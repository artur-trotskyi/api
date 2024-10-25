<?php

namespace App\Console\Commands;

use App\Jobs\TestJob;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class RunTestJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-test-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Running a test';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        TestJob::dispatch()->delay(now()->addMinute());

        $this->info('TestJob has been successfully added to the queue.');

        return CommandAlias::SUCCESS;
    }
}
