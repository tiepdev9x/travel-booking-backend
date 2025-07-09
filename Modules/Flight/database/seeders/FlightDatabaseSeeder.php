<?php

namespace Modules\Flight\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Flight\Models\Flight;

class FlightDatabaseSeeder extends Seeder
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
         * Flights Seed
         * ------------------
         */

        // DB::table('flights')->truncate();
        // echo "Truncate: flights \n";

        Flight::factory()->count(20)->create();
        $rows = Flight::all();
        echo " Insert: flights \n\n";

        // Enable foreign key checks!
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
