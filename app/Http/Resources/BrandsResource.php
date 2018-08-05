<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Config;
use Storage;

class BrandsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->brandOriginalImageUploadPath = Config::get('constant.BRANDS_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->defaultImage = Config::get('constant.DEFAULT_IMAGE_PATH');
        $this->defaultPlusImage = Config::get('constant.DEFAULT_PLUS_IMAGE_PATH');
        
        $brandImgPath = ((isset($this->brand_image) && !empty($this->brand_image)) && Storage::exists($this->brandOriginalImageUploadPath.$this->brand_image)  && Storage::size($this->brandOriginalImageUploadPath.$this->brand_image) > 0) ? Storage::url($this->brandOriginalImageUploadPath.$this->brand_image) : url($this->defaultPlusImage);
        
        return [
            'id' => $this->id,
            'brand_name' => $this->brand_name,
            'brand_slug' => $this->brand_slug,
            'brand_image' => $brandImgPath,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at
        ];
    }
}
