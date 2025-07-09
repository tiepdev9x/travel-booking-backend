<?php

namespace Modules\Country\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Country\Models\Country;

class CountryDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks!
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        /*
         * Countries Seed
         * ------------------
         */

        // DB::table('countries')->truncate();
        // echo "Truncate: countries \n";

        Country::factory()->count(20)->create();
        $rows = Country::all();
        echo " Insert: countries \n\n";

        // Enable foreign key checks!
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
