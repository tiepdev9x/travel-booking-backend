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
                'TripType' => 'RT',
                'custom_fee' => '130.000',
                'DepartureCode-0' => $dataRequest['startPoint'],
                'DestinationCode-0' => $dataRequest['endPoint'],
                'DepartureDate-0' => $dataRequest['departureDate'],
                'ReturnDate-0' => $dataRequest['returnDate'],
                'Adults' => $dataRequest['adult'],
                'Childrens' => $dataRequest['children'],
                'Infants' => $dataRequest['infant'],
            ]);
        $dom = HtmlDomParser::str_get_html($response->body());
        $key = $dom->find('#session_key', 0);
        return $key->value;
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
                'timeStart' => $timeStart,
                'locationStart' => $locationStart,
                'timeEnd' => $timeEnd,
                'locationEnd' => $locationEnd,
                'flightSession' => $flightSession,
                'fareoptionsession' => $areOptionSession,
                'sessionKey' => $session_key,
                'price' => $priceText,
                'currency' => $currency,
                'detail' => $detail
            ];
        }
        $returns = [];
        $html_return = $responseData['html_return'];
        $domReturn = HtmlDomParser::str_get_html($html_return);
        $htmlReturn = $domReturn->find('.flight-item');
        foreach ($htmlReturn as $jsonString) {
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
                'timeStart' => $timeStart,
                'locationStart' => $locationStart,
                'timeEnd' => $timeEnd,
                'locationEnd' => $locationEnd,
                'flightSession' => $flightSession,
                'fareoptionsession' => $areOptionSession,
                'sessionKey' => $session_key,
                'price' => $priceText,
                'currency' => $currency,
                'detail' => $detail
            ];
        }
        return response()->json(['data' => [
            'departure' => $flights,
            'html_return' => $returns
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

        return response()->json($responseData);
    }

    protected function removeBOM($text)
    {
        $bom = pack('H*', 'EFBBBF');
        return preg_replace("/^$bom/", '', $text);
    }
}
