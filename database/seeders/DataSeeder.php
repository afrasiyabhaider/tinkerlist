<?php

namespace Database\Seeders;

use App\Models\Episode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some sample episodes with content
        $episode1 = Episode::create([
            "title" => "Episode 1",
            "description" => "This is the content for Episode 1."
        ]);

        $episode2 = Episode::create([
            "title" => "Episode 2",
            "description" => "This is the content for Episode 2."
        ]);

        // Create parts for episode 1
        foreach (range(0, 4) as $index) {
            $episode1->parts()->create([
                "title" => "Episode 1 Part {$index} Title",
                "description" => "Episode 1 Part {$index} description.",
                "position" => $index
            ]);
        }

        // Create parts for episode 2
        foreach (range(0, 4) as $index) {
            $episode2->parts()->create([
                "title" => "Episode 2 Part {$index} Title",
                "description" => "Episode 2 Part {$index} description.",
                "position" => $index
            ]);
        }
    }
}
