<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\DemoResetSeeder;

class ResetDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the demo database to a clean state with sample data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Resetting demo data...');

        $seeder = new DemoResetSeeder();
        $seeder->setCommand($this);
        $seeder->run();

        $this->newLine();
        $this->info('Demo reset complete!');

        return Command::SUCCESS;
    }
}
