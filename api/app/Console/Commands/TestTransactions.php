<?php

namespace App\Console\Commands;

use App\Services\PostService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as CommandAlias;

class TestTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test transactions by creating posts';

    protected PostService $postService;

    public function __construct(PostService $postService)
    {
        parent::__construct();
        $this->postService = $postService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        DB::beginTransaction();

        try {
            // Create the first post
            $newPost1 = $this->postService->create([
                'user_id' => 1,
                'title' => 'First Post 1',
                'content' => 'Content of the first post',
                'tags' => ['php', 'javascript'],
            ]);


            // Create the second post (modify data to simulate different scenarios)
            $newPost2 = $this->postService->create([
                'user_id' => 1111111,
                'title' => 'Second Post 1111111',
                'content' => 'Content of the second post',
                'tags' => ['javascript', 'ruby'],
            ]);

            DB::commit();
            $this->info('Posts created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            $this->error('Error creating posts: ' . $e->getMessage());
        }

        return CommandAlias::SUCCESS;
    }
}
