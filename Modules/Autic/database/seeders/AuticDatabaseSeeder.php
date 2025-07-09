<?php

namespace Modules\Autic\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Autic\Models\Autic;

class AuticDatabaseSeeder extends Seeder
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
         * Autics Seed
         * ------------------
         */

        // DB::table('autics')->truncate();
        // echo "Truncate: autics \n";

        Autic::factory()->count(20)->create();
        $rows = Autic::all();
        echo " Insert: autics \n\n";

        // Enable foreign key checks!
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
