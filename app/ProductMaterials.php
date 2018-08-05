<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductMaterials extends Model
{
    use Notifiable;

    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'product_materials';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];


    /**
     * Insert and Update Colors
     */
    public function insertUpdate($data)
    {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0)
        {
            $getData = ProductMaterials::find($data['id']);
            $getData->update($data);
            return ProductMaterials::find($data['id']);
        } else {
           return ProductMaterials::create($data);
        }
    }

    /**
     * Get Product
     */
    public function product() {
      return $this->belongsTo(Products::class, 'product_id');
    }

    /**
     * Get Material
     */
    public function material() {
      return $this->belongsTo(Materials::class, 'material_id');
    }

    public function updateProductMaterials($product_id, $newProductMaterials)
    {
        $existingProductMaterials = ProductMaterials::where('product_id', $product_id)->get();
        $oldProductMaterials = [];

        if(!empty($existingProductMaterials)) {
            foreach($existingProductMaterials as $existingEducation) {
                $oldProductMaterials[] = $existingEducation->material_id;
            }
        }

        foreach ($oldProductMaterials as $key => $value) {
            if(!in_array($value, $newProductMaterials)) {
                ProductMaterials::where(['product_id' => $product_id, 'material_id' => $value])->delete();
            }
        }

        foreach ($newProductMaterials as $value) {
            if(!in_array($value, $oldProductMaterials)) {
                ProductMaterials::create(['product_id' => $product_id, 'material_id' => $value]);
            }
        }
        return true;
    }
}
