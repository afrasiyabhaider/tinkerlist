<?php

namespace App\Http\Resources;

use App\Models\Episode;
use Illuminate\Http\Resources\Json\JsonResource;

class PartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'position' => $this->position,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'episode' => new EpisodeResource($this->whenLoaded('episode')),
        ];
    }
}
