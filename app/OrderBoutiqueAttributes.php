<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Config;

class OrderBoutiqueAttributes extends Model
{
    use Notifiable;
    use SoftDeletes;
    use CascadeSoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'order_boutique_attributes';

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
            $getData = OrderBoutiqueAttributes::find($data['id']);
            $getData->update($data);
            return OrderBoutiqueAttributes::find($data['id']);
        }
        else
        {
            return OrderBoutiqueAttributes::create($data);
        }
    }

    /**
     * get All getOrders
     */
    public function getAll($filters = array(), $paginate = false)
    {
        $getData = OrderBoutiqueAttributes::whereNull('deleted_at');

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
     * getOrders
     */
    public function orders()
    {
        return $this->belongsTo('App\Orders', 'order_id');
    }

    /**
     * getCompany
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'boutique_id');
    }

}

