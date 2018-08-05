<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Config;
use Storage;

class ColorsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->colorOriginalImageUploadPath = Config::get('constant.COLOR_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->defaultImage = Config::get('constant.DEFAULT_IMAGE_PATH');
        $this->defaultPlusImage = Config::get('constant.DEFAULT_PLUS_IMAGE_PATH');
        
        $colorImgPath = ((isset($this->color_image) && !empty($this->color_image)) && Storage::exists($this->colorOriginalImageUploadPath.$this->color_image)  && Storage::size($this->colorOriginalImageUploadPath.$this->color_image) > 0) ? Storage::url($this->colorOriginalImageUploadPath.$this->color_image) : url($this->defaultPlusImage);
        return [
            'id' => $this->id,
            'color_name_en' => $this->color_name_en,
            'color_name_ch' => $this->color_name_ch,
            'color_name_ge' => $this->color_name_ge,
            'color_name_fr' => $this->color_name_fr,
            'color_name_it' => $this->color_name_it,
            'color_name_sp' => $this->color_name_sp,
            'color_name_ru' => $this->color_name_ru,
            'color_name_jp' => $this->color_name_jp,
            'color_unique_id' => $this->color_unique_id,
            'color_image' => $colorImgPath,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at
        ];
    }
}
