<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Running log test';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Log::info('TestCommand has successfully passed the test.');

        return self::SUCCESS;
    }
}
