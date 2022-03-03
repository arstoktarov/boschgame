<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
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
            'finish_status' => $resource->finish_status,
            'winner_id' => $resource->winner_id,
            'creator_id' => $resource->creator_id,
            'player_turn' => $resource->player_turn,
            'left_time' => $this->removeDotAndSpaces(
                Carbon::make($resource->updated_at)
                ->addHours(48)
                ->shortAbsoluteDiffForHumans(Carbon::now())
            ),
            'should_create_round' => $resource->should_create_round,
            'players' => $this->whenLoaded('players', GameUserResource::collection($resource->players)),
            'rounds' => $this->whenLoaded('rounds', RoundWithQuestionsResource::collection($resource->rounds)),
        ];
    }

    public function removeDotAndSpaces($string) {
        return str_replace([' ', '.'], '', $string);
    }
}
