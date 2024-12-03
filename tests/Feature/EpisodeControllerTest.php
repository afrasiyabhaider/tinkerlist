<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Episode;

class EpisodeControllerTest extends TestCase
{
    use RefreshDatabase;
    // Test case for creating an episode through the API
    public function test_create_episode()
    {
        // Generating a unique title using timestamp and random number
        $uniqueTitle = 'New Episode ' . time() . rand(1, 1000);

        $response = $this->postJson('/api/episodes', [
            'title' => $uniqueTitle,
            'description' => 'This is the description of the new episode.',
        ]);

        $response->assertStatus(201);

        // Assert that the unique title is stored in the database
        $this->assertDatabaseHas('episodes', [
            'title' => $uniqueTitle,
            'description' => 'This is the description of the new episode.',
        ]);
    }

    // Test case for fetching a list of episodes
    public function test_list_episodes()
    {
        $episode = Episode::create([
            'title' => 'Episode ' . time() . rand(1, 1000),
            'description' => 'Description of Episode 1'
        ]);

        $response = $this->getJson('/api/episodes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description']
                ]
            ]);
    }

    // Test case for updating an episode
    public function test_update_episode()
    {
        $episode = Episode::create([
            'title' => 'Old Episode',
            'description' => 'Old description'
        ]);

        // Generating a unique title for update
        $updatedTitle = 'Updated Episode ' . time() . rand(1, 1000);

        $response = $this->putJson("/api/episodes/{$episode->id}", [
            'title' => $updatedTitle,
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200);

        // Assert that the episode's title was updated
        $this->assertDatabaseHas('episodes', [
            'id' => $episode->id,
            'title' => $updatedTitle,
            'description' => 'Updated description',
        ]);
    }

    // Test case for deleting an episode
    public function test_delete_episode()
    {
        $episode = Episode::create([
            'title' => 'Episode to delete',
            'description' => 'Description for the episode to delete'
        ]);

        $response = $this->deleteJson("/api/episodes/{$episode->id}");

        $response->assertStatus(204);

        // Assert that the episode has been deleted from the database
        $this->assertDatabaseMissing('episodes', [
            'id' => $episode->id,
        ]);
    }
}
