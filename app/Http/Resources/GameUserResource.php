<?php

namespace App\Http\Resources;

use App\Models\Game;
use Illuminate\Http\Resources\Json\JsonResource;

class GameUserResource extends JsonResource
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
            'first_name' => $resource->first_name,
            'last_name' => $resource->last_name,
            'login' => $resource->login,
            'image' => $resource->image,
            'scores' => $resource->scores,
            'points' => $resource->points,
        ];
    }
}
