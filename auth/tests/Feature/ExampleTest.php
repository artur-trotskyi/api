<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testTheApplicationReturnsASuccessfulResponse(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * @return void
     */
    public function testAvatarsCanBeUploaded(): void
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $path = $file->store('avatars', 'avatars');

        Storage::disk('avatars')->assertExists($path);
    }
}
