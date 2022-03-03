<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserAnswerResource extends JsonResource
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
            'user_id' => $resource->user_id,
            'is_correct' => $this->whenLoaded('answer')->is_correct ?? null,
        ];
    }
}
