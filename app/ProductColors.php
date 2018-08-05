<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductColors extends Model
{
    use Notifiable;

    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'product_colors';

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
            $getData = ProductColors::find($data['id']);
            $getData->update($data);
            return ProductColors::find($data['id']);
        } else {
            return ProductColors::create($data);
        }
    }

    /**
     * Get Product
     */
    public function product() {
        return $this->belongsTo(Products::class, 'product_id');
    }

    /**
     * Get Color
     */
    public function color() {
        return $this->belongsTo(Colors::class, 'color_id');
    }

    public function updateProductColors($product_id, $newProductColors)
    {
        $existingProductColors = ProductColors::where('product_id', $product_id)->get();
        $oldProductColors = [];

        if(!empty($existingProductColors)) {
            foreach($existingProductColors as $existingEducation) {
                $oldProductColors[] = $existingEducation->color_id;
            }
        }

        foreach ($oldProductColors as $key => $value) {
            if(!in_array($value, $newProductColors)) {
                ProductColors::where(['product_id' => $product_id, 'color_id' => $value])->delete();
            }
        }

        foreach ($newProductColors as $value) {
            if(!in_array($value, $oldProductColors)) {
                ProductColors::create(['product_id' => $product_id, 'color_id' => $value]);
            }
        }
        return true;
    }
}
