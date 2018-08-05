<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryImagesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
//        return parent::toArray($request);
        return [
            'id' => $this->id,
            'category_id' => $this->category_name_en,
            'file_name' => $this->category_name_ch,
            'created_at' => $this->category_name_ge,
            'updated_at' => $this->category_name_fr
        ];
    }
}
