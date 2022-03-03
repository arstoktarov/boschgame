<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GameCollectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $resource = $this->resource;

        return [
            'id' => $resource->id,
            'status' => $resource->status,
            'winner_id' => $resource->winner_id,
            'creator_id' => $resource->creator_id,
            'player_turn' => $resource->player_turn,
            'players' => $this->whenLoaded('players', GameUserResource::collection($resource->players)),
        ];
    }
}
