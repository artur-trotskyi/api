<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Process\Pool;
use Illuminate\Support\Facades\Process;

class TestProcessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестування виконання процесів';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Виконуємо команди одночасно
        [$first, $second, $third] = Process::concurrently(function (Pool $pool): void {
            $pool->path(__DIR__)->command('ls -la');
            $pool->path(app_path())->command('ls -la');
            $pool->path(storage_path())->command('ls -la');
        });

        $this->info('Output from __DIR__:');
        $this->line($first->output());

        $this->info('Output from app path:');
        $this->line($second->output());

        $this->info('Output from storage path:');
        $this->line($third->output());

        return self::SUCCESS;
    }
}
