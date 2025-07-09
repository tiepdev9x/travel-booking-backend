<?php

namespace Modules\Flight\Console\Commands;

use Illuminate\Console\Command;

class FlightCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:FlightCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flight Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return Command::SUCCESS;
    }
}
