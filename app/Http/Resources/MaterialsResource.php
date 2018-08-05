<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Config;
use Storage;

class MaterialsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->materialOriginalImageUploadPath = Config::get('constant.MATERIAL_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->defaultImage = Config::get('constant.DEFAULT_IMAGE_PATH');
        $this->defaultPlusImage = Config::get('constant.DEFAULT_PLUS_IMAGE_PATH');
        $materialImgPath = ((isset($this->material_image) && !empty($this->material_image)) && Storage::exists( $this->materialOriginalImageUploadPath.$this->material_image)  && Storage::size($this->materialOriginalImageUploadPath.$this->material_image) > 0) ? Storage::url( $this->materialOriginalImageUploadPath.$this->material_image) : url($this->defaultPlusImage);
        
        return [
            'id' => $this->id,
            'material_name_en' => $this->material_name_en,
            'material_name_ch' => $this->material_name_ch,
            'material_name_ge' => $this->material_name_ge,
            'material_name_fr' => $this->material_name_fr,
            'material_name_it' => $this->material_name_it,
            'material_name_sp' => $this->material_name_sp,
            'material_name_ru' => $this->material_name_ru,
            'material_name_jp' => $this->material_name_jp,
            'material_unique_id' => $this->material_unique_id,
            'material_image' => $materialImgPath,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at
        ];
    }
}
