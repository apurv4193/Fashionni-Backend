<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Config;

class ProductInventory extends Model
{
  use Notifiable;

  use SoftDeletes;

   /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'product_inventory';

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
      $getData = ProductInventory::find($data['id']);
      $getData->update($data);
      return ProductInventory::find($data['id']);
    } else {
      return ProductInventory::create($data);
    }
  }

  /**
   * Get Product
   */
  public function product() {
    return $this->belongsTo(Products::class, 'product_id');
  }
  
    /**
     * get All Inventory
     */
  
    public function getAll($filters = array(), $paginate = false)
    {
        $getData = ProductInventory::whereNull('deleted_at');
        
        if(isset($filters) && !empty($filters))
        {
            if(isset($filters['product_id'])) 
            {
                $getData->where('product_id', $filters['product_id']);
            }
        }
        if(isset($paginate) && $paginate == true) 
        {
            return $getData->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        } 
        else {
            return $getData->get();
        }
    }

  /**
     * get All Product Inventory
     */
    public function getAllProductInventory($productId, $skipRecordCount)
    {
        $return = ProductInventory::take(Config::get('constant.PRODUCT_INVENTORY_RECORD_PER_PAGE'))->skip($skipRecordCount);

        if(isset($productId) && $productId != ""){
            $return->with('product.company')->where('product_id', $productId);
        }

        return $return->get();
    }

    /**
     * get All Product Inventory
     */
    public function getAllProductInventoryData($productId)
    {
        $return = ProductInventory::with('product.company')->where('product_id', $productId);

        return $return->get();
    }
}
