<?php

namespace Modules\City\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\City\Models\City;

class CityDatabaseSeeder extends Seeder
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
         * Cities Seed
         * ------------------
         */

        // DB::table('cities')->truncate();
        // echo "Truncate: cities \n";

        City::factory()->count(20)->create();
        $rows = City::all();
        echo " Insert: cities \n\n";

        // Enable foreign key checks!
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
