<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Config;
use Storage;

class CategoriesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
//      return parent::toArray($request);
        if($this->category_images && !empty($this->category_images))
        {
            $this->categoryOriginalImageUploadPath = Config::get('constant.CATEGORY_ORIGINAL_IMAGE_UPLOAD_PATH');
            $this->defaultImage = Config::get('constant.DEFAULT_IMAGE_PATH');
            foreach ($this->category_images as $imgKey => $_imgValue) 
            {
                $_imgValue->file_name = ($_imgValue->file_name != NULL && $_imgValue->file_name != '' && $_imgValue->company_image != NULL && $_imgValue->company_image != '' && Storage::exists($this->categoryOriginalImageUploadPath.$_imgValue->file_name) && Storage::size($this->categoryOriginalImageUploadPath.$_imgValue->file_name) > 0) ? Storage::url($this->categoryOriginalImageUploadPath . $_imgValue->file_name) : url($this->defaultImage);
            }
        }
        return [
            'id' => $this->id,
            'category_name_en' => $this->category_name_en,
            'category_name_ch' => $this->category_name_ch,
            'category_name_ge' => $this->category_name_ge,
            'category_name_fr' => $this->category_name_fr,
            'category_name_it' => $this->category_name_it,
            'category_name_sp' => $this->category_name_sp,
            'category_name_ru' => $this->category_name_ru,
            'category_name_jp' => $this->category_name_jp,
            'category_unique_id' => $this->category_unique_id,
            'category_level' => $this->category_level,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
            'category_images' => $this->category_images,
        ];
    }
}
