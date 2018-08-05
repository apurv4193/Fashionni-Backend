<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Config;

class OrderPackageBoxes extends Model
{
    use Notifiable;
    use SoftDeletes;
    use CascadeSoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'order_package_boxes';

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
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0) {
            $getData = OrderPackageBoxes::find($data['id']);
            $getData->update($data);
            return OrderPackageBoxes::find($data['id']);
        } else {
            return OrderPackageBoxes::create($data);
        }
    }

    /**
     * get All getOrders
     */
    public function getAll($filters = array(), $paginate = false)
    {
        $getData = OrderPackageBoxes::whereNull('deleted_at');

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

}

