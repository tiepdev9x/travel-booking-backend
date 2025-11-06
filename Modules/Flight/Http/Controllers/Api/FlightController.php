<?php

namespace Modules\Flight\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Drnxloc\LaravelHtmlDom\HtmlDomParser;
use Illuminate\Support\Facades\Cache;

class FlightController extends Controller
{
    protected function login()
    {
        if (!empty(Cache::get('cookie_value_login'))) {
            return Cache::get('cookie_value_login');
        }
        $response = Http::withHeaders([
            'accept' => '*/*',
            'accept-language' => 'en-US,en;q=0.9,vi;q=0.8',
            'cache-control' => 'no-cache',
            'content-type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'origin' => 'https://autic.vn',
            'pragma' => 'no-cache',
            'priority' => 'u=1, i',
            'referer' => 'https://autic.vn/agent/login',
            'sec-ch-ua' => '"Google Chrome";v="137", "Chromium";v="137", "Not/A)Brand";v="24"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Linux"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36',
            'x-requested-with' => 'XMLHttpRequest',
        ])
            ->asForm()
            ->post('https://autic.vn/cassiopeia/ajax', [
                'cmd' => 'custom_login',
                'data' => '[null,"0389471710","250594"]',
            ]);
        $cookieJar = $response->cookies();
        $cookieJarText = '';
        foreach ($cookieJar as $cookie) {
            if ($cookie->getName()) {
                $cookieJarText = "{$cookie->getName()}={$cookie->getValue()};";
            }
        }
        $cookieValue = rtrim($cookieJarText, ';');
        Cache::put('cookie_value_login', $cookieValue, now()->addWeek());
        return Cache::get('cookie_value_login');
    }

    protected function getSessionKey($cookie, $dataRequest)
    {
        $response = Http::withHeaders([
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'accept-language' => 'en-US,en;q=0.9,vi;q=0.8',
            'cache-control' => 'no-cache',
            'pragma' => 'no-cache',
            'priority' => 'u=0, i',
            'sec-ch-ua' => '"Google Chrome";v="137", "Chromium";v="137", "Not/A)Brand";v="24"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Linux"',
            'sec-fetch-dest' => 'document',
            'sec-fetch-mode' => 'navigate',
            'sec-fetch-site' => 'same-origin',
            'sec-fetch-user' => '?1',
            'upgrade-insecure-requests' => '1',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36',
            'Cookie' => $cookie,
        ])
            ->get('https://autic.vn/flight-search', [
                'TripType' => $dataRequest['TripType'] ??'RT',
                'custom_fee' => '130.000',
                'DepartureCode-0' => $dataRequest['startPoint'] ?? '',
                'DestinationCode-0' => $dataRequest['endPoint'] ?? '',
                'DepartureDate-0' => $dataRequest['departureDate'] ?? '',
                'ReturnDate-0' => $dataRequest['returnDate'] ?? '',
                'Adults' => $dataRequest['adult'] ?? '0',
                'Childrens' => $dataRequest['children'] ?? '0',
                'Infants' => $dataRequest['infant'] ?? '0',
            ]);
        $dom = HtmlDomParser::str_get_html($response->body());
        $key = $dom->find('#session_key', 0);
        return $key->value;
    }

    private function convertPrice($price)
    {
        // Remove everything except digits and comma
        $clean = preg_replace('/[^\d,]/', '', $price);

// Replace comma with dot for decimal
        $clean = str_replace(',', '.', $clean);

// Remove thousand separators (dots before the comma)
        $clean = preg_replace('/\.(?=\d{3,})/', '', $clean);

        $number = (float)$clean;

        return $number; // Output: 1221000.50
    }

    public function getFlight(Request $request)
    {
        $dataRequest = [
            'startPoint' => $request->get('startPoint'),
            'endPoint' => $request->get('endPoint'),
            'departureDate' => $request->get('departureDate'),
            'returnDate' => $request->get('returnDate'),
            'adult' => $request->get('adult'),
            'children' => $request->get('children'),
            'infant' => $request->get('infant'),
            'itineraryType' => $request->get('itineraryType'),
            'TripType' => $request->get('TripType'),
        ];

        $cookie = $this->login();
        $session_key = $this->getSessionKey($cookie, $dataRequest);
        $response = Http::withHeaders([
            'accept' => '*/*',
            'accept-language' => 'en-US,en;q=0.9,vi;q=0.8',
            'cache-control' => 'no-cache',
            'content-type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'origin' => 'https://autic.vn',
            'pragma' => 'no-cache',
            'priority' => 'u=1, i',
            'sec-ch-ua' => '"Google Chrome";v="137", "Chromium";v="137", "Not/A)Brand";v="24"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Linux"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36',
            'x-requested-with' => 'XMLHttpRequest',
            'Cookie' => $cookie,
        ])
            ->asForm()
            ->post('https://autic.vn/cassiopeia/ajax', [
                'cmd' => 'get_domestic_flights_meta',
                'session_key' => $session_key,
                'data' => json_encode([
                    "StartPoint" => $dataRequest['startPoint'],
                    "EndPoint" => $dataRequest['endPoint'],
                    "DepartureDate" => $dataRequest['departureDate'],
                    "ReturnDate" => $dataRequest['returnDate'],
                    "ItineraryType" => 2,
                    "Adult" => $dataRequest['adult'],
                    "Children" => $dataRequest['children'],
                    "Infant" => $dataRequest['infant'],
                    "customFee" => "130000",
                ]),
            ]);
        $decodedContent = html_entity_decode($this->removeBOM($response->body()));
        // Extract data segments using regex
        $responseData = json_decode($decodedContent, true);
        $html_departure = $responseData['html_departure'];
        $dom = HtmlDomParser::str_get_html($html_departure);
        $flights = [];
        $html = $dom->find('.flight-item');
        foreach ($html as $jsonString) {
            $startTime = $jsonString->attr['data-start-time'] ?? '';
            $priceFilter = $jsonString->attr['data-full-price'] ?? '';
            $airlineCode = $jsonString->attr['data-airline'] ?? '';
            $fightNumber = trim($jsonString->find('.flight-number', 0)->innertext);
            $startPoint = $jsonString->find('div.startpoint', 0);
            $endpoint = $jsonString->find('div.endpoint', 0);
            $timeStart = trim($startPoint->find('div', 0)->innertext);
            $locationStart = trim($startPoint->find('div', 1)->innertext);
            $timeEnd = trim($endpoint->find('div', 0)->innertext);
            $locationEnd = trim($endpoint->find('div', 1)->innertext);
            $flightSession = $jsonString->attr['data-flightsession'] ?? '';
            $areOptionSession = $jsonString->attr['data-fareoptionsession'] ?? '';
            $price = $jsonString->find('div.price', 0)->find('span.tax-fee', 0)->find('span.active', 0);
            $currency = trim($price->find('strong', 0)->innertext);
            $priceText = trim(strip_tags($price->innertext));
            $detail = $jsonString->find('div.detail', 0)->innertext;
            $flights[] = [
                'flightNumber' => $fightNumber,
                'filter_start_time' => $startTime,
                'timeStart' => $timeStart,
                'locationStart' => $locationStart,
                'timeEnd' => $timeEnd,
                'locationEnd' => $locationEnd,
                'flightSession' => $flightSession,
                'fareoptionsession' => $areOptionSession,
                'sessionKey' => $session_key,
                'price' => $priceText,
                'currency' => $currency,
                'price_filter' => $priceFilter,
                'airlineCode' => $airlineCode,
                'detail' => $detail
            ];
        }
        $returns = [];
        $html_return = $responseData['html_return'];
        $domReturn = HtmlDomParser::str_get_html($html_return);
        $htmlReturn = $domReturn->find('.flight-item');
        foreach ($htmlReturn as $jsonString) {
            $startTime = $jsonString->attr['data-start-time'] ?? '';
            $priceFilter = $jsonString->attr['data-full-price'] ?? '';
            $airlineCode = $jsonString->attr['data-airline'] ?? '';
            $fightNumber = trim($jsonString->find('.flight-number', 0)->innertext);
            $startPoint = $jsonString->find('div.startpoint', 0);
            $endpoint = $jsonString->find('div.endpoint', 0);
            $timeStart = trim($startPoint->find('div', 0)->innertext);
            $locationStart = trim($startPoint->find('div', 1)->innertext);
            $timeEnd = trim($endpoint->find('div', 0)->innertext);
            $locationEnd = trim($endpoint->find('div', 1)->innertext);
            $flightSession = $jsonString->attr['data-flightsession'] ?? '';
            $areOptionSession = $jsonString->attr['data-fareoptionsession'] ?? '';
            $price = $jsonString->find('div.price', 0)->find('span.tax-fee', 0)->find('span.active', 0);
            $currency = trim($price->find('strong', 0)->innertext);
            $priceText = trim(strip_tags($price->innertext));
            $detail = $jsonString->find('div.detail', 0)->innertext;
            $returns[] = [
                'flightNumber' => $fightNumber,
                'filter_start_time' => $startTime,
                'timeStart' => $timeStart,
                'locationStart' => $locationStart,
                'timeEnd' => $timeEnd,
                'locationEnd' => $locationEnd,
                'flightSession' => $flightSession,
                'fareoptionsession' => $areOptionSession,
                'sessionKey' => $session_key,
                'price' => $priceText,
                'currency' => $currency,
                'price_filter' => $priceFilter,
                'airlineCode' => $airlineCode,
                'detail' => $detail
            ];
        }

        $blockFilter = $responseData['block_filter'] ?? '';
        $domFilter = HtmlDomParser::str_get_html($blockFilter);

        //Lọc chuyến bay
        $departureFilter = $domFilter->find('.departure-block', 0)->find('.block-item');
        $dataAirlineFilter = [
            'departure' => [],
            'return' => [],
        ];
        foreach ($departureFilter as $departure) {
            $dataAirline = $departure->attr['data-airline'] ?? '';
            $dataAirlineName = $departure->find('label', 0)->innertext;
            $dataAirlineFilter['departure'][] = ['code' => $dataAirline, 'name' => $dataAirlineName];
        }
        $returnFilter = $domFilter->find('.return-block', 0)->find('.block-item');
        foreach ($returnFilter as $return) {
            $dataAirline = $return->attr['data-airline'] ?? '';
            $dataAirlineName = $return->find('label', 0)->innertext;
            $dataAirlineFilter['return'][] = ['code' => $dataAirline, 'name' => $dataAirlineName];;
        }
        //Khoảng giá
        $priceRagerFilter = $domFilter->find('#price-slider-range-amount', 0)->attr['value'] ?? '';
        $priceData = explode('-', $priceRagerFilter);
        $priceMin = $this->convertPrice(trim($priceData[0] ?? '0'));
        $priceMax = $this->convertPrice(trim($priceData[1] ?? '999999999'));

        //Giờ cất cánh
        $dataTimeFilter = [
            'departure' => [],
            'return' => [],
        ];
        $departureTimeFilter = $domFilter->find('.departure-time-filter', 0)->find('.block-item');
        foreach ($departureTimeFilter as $departureTime) {
            $time = $departureTime->find('input[name="departure-time"]', 0)->attr['value'] ?? '';
            $labelTime = trim($departureTime->find('label', 0)->innertext ?? '');
            $dataTimeFilter['departure'][] = ['code' => $time, 'name' => $labelTime];
        }
        $returnTimeFilter = $domFilter->find('.return-time-filter', 0)->find('.block-item');
        //Giờ hạ cánh
        foreach ($returnTimeFilter as $returnTime) {
            $time = $returnTime->find('input[name="return-time"]', 0)->attr['value'] ?? '';
            $labelTime = trim($returnTime->find('label', 0)->innertext ?? '');
            $dataTimeFilter['return'][] = ['code' => $time, 'name' => $labelTime];
        }

        return response()->json(['data' => [
            'title_departure' => $responseData['block_result_DO_departure_head'] ?? '',
            'title_return' => $responseData['block_result_DO_return_head'] ?? '',
            'departure' => $flights,
            'html_return' => $returns,
            'block_filter' => [
                'airline_filter' => $dataAirlineFilter,
                'time_filter' => $dataTimeFilter,
                'price_filter' => ['min' => $priceMin, 'max' => $priceMax],
            ],
        ]]);
    }

    public function getFightDetail(Request $request)
    {
        $itinerary = $request->get('itinerary', 'DepartureFlights');
        $flightSession = $request->get('flightSession');
        $fareOptionSession = $request->get('fareOptionSession');
        $session_key = $request->get('session_key');
        $cookie = $this->login();
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Accept-Language' => 'en-US,en;q=0.9,vi;q=0.8',
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'Cookie' => $cookie,
            'Origin' => 'https://autic.vn',
            'Pragma' => 'no-cache',
            'Priority' => 'u=1, i',
            'Sec-Ch-Ua' => '"Google Chrome";v="137", "Chromium";v="137", "Not/A)Brand";v="24"',
            'Sec-Ch-Ua-Mobile' => '?0',
            'Sec-Ch-Ua-Platform' => '"Linux"',
            'Sec-Fetch-Dest' => 'empty',
            'Sec-Fetch-Mode' => 'cors',
            'Sec-Fetch-Site' => 'same-origin',
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36',
            'X-Requested-With' => 'XMLHttpRequest',
        ])->asForm()->post('https://autic.vn/cassiopeia/ajax', [
            'cmd' => 'getFlightDetail',
            'FlightSession' => $flightSession,
            'FareOptionSession' => $fareOptionSession,
            'Itinerary' => $itinerary,
            'session_key' => $session_key,
        ]);

        $decodedContent = html_entity_decode($this->removeBOM($response->body()));
        // Extract data segments using regex
        $responseData = json_decode($decodedContent, true);
        $responseData['html'] = str_replace('autic.vn', 'localhost', $responseData['html']);
        return response()->json($responseData);
    }

    public function getPrice(Request $request)
    {
        $flightSession = $request->get('flightSession');
        $fareOptionSession = $request->get('fareOptionSession');
        $session_key = $request->get('session_key');
        $isReturn = (bool)$request->get('isReturn', false);
        $cookie = $this->login();
        $response = Http::withHeaders([
            'accept' => '*/*',
            'accept-language' => 'en-US,en;q=0.9,vi;q=0.8',
            'cache-control' => 'no-cache',
            'content-type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'origin' => 'https://autic.vn',
            'pragma' => 'no-cache',
            'priority' => 'u=1, i',
            'sec-ch-ua' => '"Google Chrome";v="137", "Chromium";v="137", "Not/A)Brand";v="24"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Linux"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36',
            'x-requested-with' => 'XMLHttpRequest',
            'Cookie' => $cookie,
        ])
            ->asForm()
            ->post('https://autic.vn/cassiopeia/ajax', [
                'cmd' => 'getFLightClass',
                'FlightSession' => $flightSession,
                'FareOptionSession' => $fareOptionSession,
                'session_key' => $session_key,
                'Itinerary' => $isReturn ? 'ReturnFlights' : 'DepartureFlights',
                'customFee' => '130000'
            ]);
        $decodedContent = html_entity_decode($this->removeBOM($response->body()));
        // Extract data segments using regex
        $responseData = json_decode($decodedContent, true);
        $dom = HtmlDomParser::str_get_html($responseData['html']);
        $prices = [];
        foreach ($dom->childNodes() as $node) {
            $class = $node->attr['data-class'] ?? null;
            $price = $node->attr['data-value'] ?? null;
            $fareoptionsession = $node->attr['data-fareoptionsession'] ?? null;
            $prices[] = [
                'class' => $class,
                'price' => $price,
                'fareoptionsession' => $fareoptionsession,
            ];
        }
        return $prices;
    }

    public function bookingChooseFlight(Request $request)
    {
        $cookie = $this->login();
        $isReturn = (bool)$request->get('isReturn', false);
        $flightNumber = $request->get('flightNumber');
        $airline = $request->get('airline');
        $session_key = $request->get('session_key');
        $flightSession = $request->get('flightSession');
        $fareOptionSession = $request->get('fareOptionSession');
        $delete = $request->get('delete', false);
        $response = Http::withHeaders([
            'accept' => '*/*',
            'accept-language' => 'en-US,en;q=0.9,vi;q=0.8',
            'cache-control' => 'no-cache',
            'content-type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'origin' => 'https://autic.vn',
            'pragma' => 'no-cache',
            'priority' => 'u=1, i',
            'sec-ch-ua' => '"Google Chrome";v="137", "Chromium";v="137", "Not/A)Brand";v="24"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Linux"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36',
            'x-requested-with' => 'XMLHttpRequest',
            'Cookie' => $cookie,
        ])
            ->asForm()
            ->post('https://autic.vn/cassiopeia/ajax', [
                'cmd' => $delete ? 'booking_un_choose_flight' : 'booking_choose_flight',
                '_flight_type' => 2,
                'FlightNumber' => $flightNumber,
                'Airline' => $airline,
                'Itinerary' => $isReturn ? 'ReturnFlights' : 'DepartureFlights',
                '_session_key' => $session_key,
                'FlightSession' => $flightSession,
                'FareOptionSession' => $fareOptionSession,
                'customFee' => '130000'
            ]);
        $decodedContent = html_entity_decode($this->removeBOM($response->body()));
        // Extract data segments using regex
        $responseData = json_decode($decodedContent, true);
        $bookingForm = $responseData['booking_form'] ?? '';
        if ($bookingForm) {
            $dom = HtmlDomParser::str_get_html($bookingForm);
            // nguoi lon form
            $blockAdults = $dom->find('.block-adults');
            $bookingForm = [
                'adults' => [],
                'children' => [],
                'infants' => [],
                'total_adults' => '0',
                'total_children' => '0',
                'total_infants' => '0',
            ];
            foreach ($blockAdults as $key => $adult) {
                $optionDepartures = $adult->find('select[data-itinerary=DepartureFlights] option');
                foreach ($optionDepartures as $option) {
                    $bookingForm['adults'][$key]['DepartureFlights'][] = ['name' => $option->attr['value'] ?? '', 'value' => $option->innertext, 'price' => $option->attr['data-price'] ?? '0'];
                }

                $optionReturnFlights = $adult->find('select[data-itinerary=ReturnFlights] option');
                foreach ($optionReturnFlights as $option) {
                    $bookingForm['adults'][$key]['ReturnFlights'][] = ['name' => $option->attr['value'] ?? '', 'value' => $option->innertext, 'price' => $option->attr['data-price'] ?? '0'];
                }
                $bookingForm['total_adults'] += 1;
            }

            // block-children
            $blockChildren = $dom->find('.block-children');
            foreach ($blockChildren as $key => $adult) {
                $optionDepartures = $adult->find('select[data-itinerary=DepartureFlights] option');
                foreach ($optionDepartures as $option) {
                    $bookingForm['children'][$key]['DepartureFlights'][] = ['name' => $option->attr['value'] ?? '', 'value' => $option->innertext, 'price' => $option->attr['data-price'] ?? '0'];
                }

                $optionReturnFlights = $adult->find('select[data-itinerary=ReturnFlights] option');
                foreach ($optionReturnFlights as $option) {
                    $bookingForm['children'][$key]['ReturnFlights'][] = ['name' => $option->attr['value'] ?? '', 'value' => $option->innertext, 'price' => $option->attr['data-price'] ?? '0'];
                }
                $bookingForm['total_children'] += 1;
            }

            //block-infants
            $blockInfants = $dom->find('.block-infants');
            foreach ($blockInfants as $key => $adult) {
                $optionDepartures = $adult->find('select[data-itinerary=DepartureFlights] option');
                $bookingForm['infants'][$key]['DepartureFlights'] = [];
                foreach ($optionDepartures as $option) {
                    $bookingForm['infants'][$key]['DepartureFlights'][] = ['name' => $option->attr['value'] ?? '', 'value' => $option->innertext, 'price' => $option->attr['data-price'] ?? '0'];
                }
                $bookingForm['infants'][$key]['ReturnFlights'] = [];
                $optionReturnFlights = $adult->find('select[data-itinerary=ReturnFlights] option');
                foreach ($optionReturnFlights as $option) {
                    $bookingForm['infants'][$key]['ReturnFlights'][] = ['name' => $option->attr['value'] ?? '', 'value' => $option->innertext, 'price' => $option->attr['data-price'] ?? '0'];
                }
                $bookingForm['total_infants'] += 1;
            }
            $responseData['booking_form'] = $bookingForm;
        }

        return response()->json($responseData);
    }

    public function addBaggage(Request $request)
    {
        $cookie = $this->login();
        $baggages = $request->get('baggages', []);
        $response = Http::withHeaders([
            'accept' => '*/*',
            'accept-language' => 'en-US,en;q=0.9,vi;q=0.8',
            'cache-control' => 'no-cache',
            'content-type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'origin' => 'https://autic.vn',
            'pragma' => 'no-cache',
            'priority' => 'u=1, i',
            'sec-ch-ua' => '"Google Chrome";v="137", "Chromium";v="137", "Not/A)Brand";v="24"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Linux"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36',
            'x-requested-with' => 'XMLHttpRequest',
            'Cookie' => $cookie,
        ])
            ->asForm()
            ->post('https://autic.vn/cassiopeia/ajax', [
                'cmd' => 'add_baggage',
                'baggages' => json_encode($baggages),
                'customFee' => '130000'
            ]);
        $decodedContent = html_entity_decode($this->removeBOM($response->body()));
        // Extract data segments using regex
        $responseData = json_decode($decodedContent, true);

        return response()->json($responseData);
    }

    private function mappedRequest($request)
    {
        $output = [];

        foreach ($request as $key => $value) {
            // Remove [0], [1], etc. from key
            $cleanKey = preg_replace('/\[\d+\]/', '', $key);

            // Initialize array if needed
            if (!isset($output[$cleanKey])) {
                $output[$cleanKey] = [];
            }

            // Push value to corresponding array
            $output[$cleanKey][] = $value;
        }

// Display result
        return $output;
    }

    public function bookingSubmit(Request $request)
    {
        $cookie = $this->login();
        $allRequest = $this->mappedRequest($request->all());
        $adtData = [];
        $chdData = [];
        $infData = [];
        foreach ($allRequest['adults_customer_name'] ?? [] as $key => $value) {
            $departureFlights = json_decode($allRequest['adults_departure_flights'][$key] ?? '{}', true);
            $returnFlights = json_decode($allRequest['adults_return_flights'][$key] ?? '{}', true);
           $baggages = [];
            if($departureFlights['value'] ?? 0 > 0){
                $baggages['DepartureFlights'] = ['id' => $departureFlights['value']];
            }
            if($returnFlights['value'] ?? 0 > 0){
                $baggages['ReturnFlights'] = ['id' => $returnFlights['value']];
            }
            $adtDataTmp = [
                'full_name' => $value,
                'gender' => $allRequest['adults_gender'][$key] ?? 0,
                'baggages' => $baggages
            ];
            $adtData[] = $adtDataTmp;
        }
        foreach ($allRequest['children_customer_name'] ?? [] as $key => $value) {
            $departureFlights = json_decode($allRequest['children_departure_flights'][$key] ?? '{}', true);
            $returnFlights = json_decode($allRequest['children_return_flights'][$key] ?? '{}', true);
            $baggages = [];
            if($departureFlights['value'] ?? 0 > 0){
                $baggages['DepartureFlights'] = ['id' => $departureFlights['value']];
            }
            if($returnFlights['value'] ?? 0 > 0){
                $baggages['ReturnFlights'] = ['id' => $returnFlights['value']];
            }
            $chdDataTmp = [
                'full_name' => $value,
                'gender' => $allRequest['children_gender'][$key] ?? 0,
                'baggages' => $baggages,
                'birth_day' => $allRequest['children_date_of_birth'][$key] ?? '',
            ];
            $chdData[] = $chdDataTmp;
        }

        foreach ($allRequest['infants_customer_name'] ?? [] as $key => $value) {
            $infDataTmp = [
                'full_name' => $value,
                'gender' => $allRequest['infants_gender'][$key] ?? 0,
                'birth_day' => $allRequest['infants_date_of_birth'][$key] ?? '',
            ];
            $infData[] = $infDataTmp;
        }
        $response = Http::withHeaders([
            'accept' => '*/*',
            'accept-language' => 'en-US,en;q=0.9,vi;q=0.8',
            'cache-control' => 'no-cache',
            'content-type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'origin' => 'https://autic.vn',
            'pragma' => 'no-cache',
            'priority' => 'u=1, i',
            'sec-ch-ua' => '"Google Chrome";v="137", "Chromium";v="137", "Not/A)Brand";v="24"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Linux"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-origin',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36',
            'x-requested-with' => 'XMLHttpRequest',
            'Cookie' => $cookie,
        ])
            ->asForm()
            ->post('https://autic.vn/cassiopeia/ajax', [
                'cmd' => 'booking_submit_meta',
                '_Adt' => json_encode($adtData),
                '_Chd' => json_encode($chdData),
                '_Inf' => json_encode($infData),
                '_contact' => '{"full_name":"hà thị phượng","tel":"0389471710","email":"phuongbooking88@gmail.com"}',
                'payment' => 1,
                '_selectAgent' => 0
            ]);
        $decodedContent = html_entity_decode($this->removeBOM($response->body()));
        // Extract data segments using regex
        $responseData = json_decode($decodedContent, true);

        return response()->json($responseData);
    }

    protected function removeBOM($text)
    {
        $bom = pack('H*', 'EFBBBF');
        return preg_replace("/^$bom/", '', $text);
    }
}
