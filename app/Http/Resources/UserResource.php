<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'rating' => $resource->rating,
            'scores' => $resource->scores,
            'in_blacklist' => $resource->in_blacklist,
            'in_friends' => $resource->in_friends,
        ];
    }
}
