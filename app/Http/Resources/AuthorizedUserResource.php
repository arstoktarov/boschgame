<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthorizedUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $model = $this->resource;
        return [
            'id' => $model->id,
            'first_name' => $model->first_name,
            'last_name' => $model->last_name,
            'login' => $model->login,
            'phone' => $model->phone,
            'image' => $model->image,
            'rating' => $model->rating,
            'scores' => $model->scores,
            'workplace' => $model->workplace,
            'organization' => $model->organization,
            'country_id' => $model->country_id,
            'city_id' => $model->city_id,
            'rating_coefficient' => $model->rating_coefficient,
        ];
    }
}
