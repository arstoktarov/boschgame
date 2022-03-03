<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionWithAnswersResource extends JsonResource
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
            'title' => $resource->title,
            'image' => $resource->image,
            'answers' => AnswerResource::collection($this->answers->shuffle())
        ];
    }
}
