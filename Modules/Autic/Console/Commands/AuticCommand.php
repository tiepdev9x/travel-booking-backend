<?php

namespace Modules\Autic\Console\Commands;

use Illuminate\Support\Str;
use Modules\City\Models\City;
use Modules\Country\Models\Country;
use Symfony\Component\Panther\Client;
use Illuminate\Console\Command;
use Drnxloc\LaravelHtmlDom\HtmlDomParser;
use function Laravel\Prompts\text;

class AuticCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autic:fetchData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Autic Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $client = Client::createChromeClient(); // Chrome headless

        $crawler = file_get_contents('https://autic.vn');
        $dom = HtmlDomParser::str_get_html($crawler);

        $html = $dom->find('.suggest-airport-links .nav-item a');
        foreach ($html as $node) {
            $country = Country::firstOrNew(['name' => trim($node->innertext)]);
            $country->name = trim($node->innertext);
            $country->slug = Str::slug(trim($node->innertext));
            $country->status = 1;
            $country->save();
            // xu ly insert data city
            foreach ($dom->find($node->href . ' a') as $item) {
                $city = City::firstOrNew(['slug' => trim($item->airportcode)]);
                $city->country_id = $country->id;
                $city->name = strip_tags($item->innertext);
                $city->slug = trim($item->airportcode);
                $city->status = 1;
                $city->save();
            }
        }
        $this->info("Crawn data done");
        return Command::SUCCESS;
    }
}
