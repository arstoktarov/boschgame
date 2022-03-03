<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoundWithQuestionsResource extends JsonResource
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
            'category' => $this->whenLoaded('category'),
            'player_turn' => $this->player_turn,
            'creator_id' => $this->creator_id,
            'questions' => QuestionWithAnswersResource::collection($this->whenLoaded('questions')),
            'user_answers' => UserAnswerResource::collection($this->whenLoaded('userAnswers')),
        ];
    }
}
