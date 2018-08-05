<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductImages extends Model
{
    use Notifiable;

    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'product_images';

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
            $getData = ProductImages::find($data['id']);
            $getData->update($data);
            return ProductImages::find($data['id']);
        } else {
            return ProductImages::create($data);
        }
    }

    /**
     * Get Product
     */
    public function product() {
        return $this->belongsTo(Products::class, 'product_id');
    }

    /**
     * Get Company
     */
    public function company() {
        return $this->belongsTo(Company::class, 'products', 'product_id', 'company_id');
    }

    /**
     * Get Product Image by Product Id ans position
     */
    public function getProductImageByProductIdAndProductPosition($product_id,$position)
    {
        return ProductImages::where(['product_id' => $product_id, 'file_position' => $position])->first();
    }

}
