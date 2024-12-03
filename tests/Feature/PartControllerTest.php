<?php

use Tests\TestCase;
use App\Models\Episode;
use App\Models\Part;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PartControllerTest extends TestCase
{
    use RefreshDatabase;
    // Test case for creating a part
    public function test_create_part()
    {
        $episode = Episode::create([
            'title' => 'Test Episode',
            'description' => 'Test episode description'
        ]);

        $response = $this->postJson("/api/episode/{$episode->id}/part", [
            'episode_id' => $episode->id,
            'title' => 'Test Part',
            'description' => 'Test part description',
            'position' => 1,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('parts', [
            'episode_id' => $episode->id,
            'title' => 'Test Part',
            'description' => 'Test part description',
            'position' => 1,
        ]);
    }

    // Test case for listing parts of an episode
    public function test_list_parts_of_episode()
    {
        $episode = Episode::create([
            'title' => 'Test Episode',
            'description' => 'Test episode description'
        ]);

        $part1 = Part::create([
            'episode_id' => $episode->id,
            'title' => 'Part 1',
            'description' => 'First part description',
            'position' => 1,
        ]);

        $part2 = Part::create([
            'episode_id' => $episode->id,
            'title' => 'Part 2',
            'description' => 'Second part description',
            'position' => 2,
        ]);

        $response = $this->getJson("/api/episode/{$episode->id}/parts");

        $response
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'position', 'created_at', 'updated_at', 'episode']
                ]
            ])
            ->assertJsonFragment([
                'title' => 'Part 1',
                'position' => 1
            ])
            ->assertJsonFragment([
                'title' => 'Part 2',
                'position' => 2
            ]);
    }

    // Test case for updating a part's position
    public function test_update_part_position()
    {
        $episode = Episode::create([
            'title' => 'Test Episode',
            'description' => 'Test episode description'
        ]);

        $part = Part::create([
            'episode_id' => $episode->id,
            'title' => 'Test Part',
            'description' => 'Test part description',
            'position' => 1,
        ]);

        $response = $this->postJson("/api/episode/{$episode->id}/parts/update/positions", [
            'id' => $part->id, //part id
            'position' => 0,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Position updated successfully',
            ]);

        $this->assertDatabaseHas('parts', [
            'id' => $part->id,
            'position' => 0,
        ]);
    }

    // Test case for deleting a part
    public function test_delete_part()
    {
        $episode = Episode::create([
            'title' => 'Test Episode',
            'description' => 'Test episode description'
        ]);

        $part = Part::create([
            'episode_id' => $episode->id,
            'title' => 'Test Part',
            'description' => 'Test part description',
            'position' => 1,
        ]);

        $response = $this->deleteJson("/api/episode/{$episode->id}/parts/{$part->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Part deleted successfully',
            ]);

        $this->assertDatabaseMissing('parts', [
            'id' => $part->id,
        ]);
    }
    // Test case for deleting a part
    public function test_reorder_part()
    {
        $episode = Episode::create([
            'title' => 'Test Episode',
            'description' => 'Test episode description'
        ]);

        $part = Part::create([
            'episode_id' => $episode->id,
            'title' => 'Test Part',
            'description' => 'Test part description',
            'position' => 2,
        ]);
        $part = Part::create([
            'episode_id' => $episode->id,
            'title' => 'Test Part 2',
            'description' => 'Test part description 2',
            'position' => 1,
        ]);

        $response = $this->postJson("/api/episode/{$episode->id}/parts/reorder");

        $response->assertStatus(200);
    }
}
