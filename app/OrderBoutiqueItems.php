<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Config;

class OrderBoutiqueItems extends Model
{
    use Notifiable;
    use SoftDeletes;
    use CascadeSoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'order_boutique_items';

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

    protected $cascadeDeletes = [];

    /**
     * Insert and Update Orders
     */
    public function insertUpdate($data)
    {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0)
        {
            $getData = OrderBoutiqueItems::find($data['id']);
            $getData->update($data);
            return OrderBoutiqueItems::find($data['id']);
        }
        else
        {
            return OrderBoutiqueItems::create($data);
        }
    }

    /**
     * get All getOrders
     */
    public function getAll($filters = array(), $paginate = false)
    {
        $getData = OrderBoutiqueItems::whereNull('deleted_at');

        if(isset($filters) && !empty($filters))
        {

        }
        if(isset($paginate) && $paginate == true)
        {
            return $getData->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        }
        else
        {
            return $getData->get();
        }
    }

    /**
     * get company details
     * relationship id is boutique_id
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'boutique_id');
    }


    /**
     * get Boutique wise Product
     * relationship id is product_id
     */
    public function orderBoutiqueProduct()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

}

