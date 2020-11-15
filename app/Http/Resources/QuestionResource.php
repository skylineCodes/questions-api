<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
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
            'is_general' => boolval($this->is_general),
            'category' => $this->category,
            'point' => $this->point,
            'icon_url' => $this->icon_url,
            'duration' => $this->duration,
            'created_at' => $this->created_at->diffForHumans()
        ];
    }
}
