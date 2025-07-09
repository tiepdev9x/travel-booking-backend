<?php

namespace Modules\Country\Console\Commands;

use Illuminate\Console\Command;

class CountryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CountryCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Country Command description';

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
