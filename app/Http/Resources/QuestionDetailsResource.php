<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionDetailsResource extends JsonResource
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
            'questions' => $this->questions,
            'is_general' => $this->is_general = '1' ? true : false,
            'category' => $this->category,
            'point' => $this->point,
            'icon_url' => $this->icon_url,
            'duration' => $this->duration,
            'created_at' => $this->created_at->diffForHumans(),
            'choices' => ChoiceResource::collection($this->choices)
        ];
    }
}
