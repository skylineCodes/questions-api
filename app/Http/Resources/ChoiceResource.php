<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChoiceResource extends JsonResource
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
            'choice' => $this->choice,
            'is_correct_choice' => boolval($this->is_correct_choice),
            'icon_url' => $this->icon_url,
            'created_at' => $this->created_at->diffForHumans()
        ];
    }
}
