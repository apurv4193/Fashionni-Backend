<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Config;

class OrderBoutiqueDocuments extends Model
{
    use Notifiable;
    use SoftDeletes;
    use CascadeSoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'order_boutique_documents';

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
            $getData = OrderBoutiqueDocuments::find($data['id']);
            $getData->update($data);
            return OrderBoutiqueDocuments::find($data['id']);
        }
        else
        {
            return OrderBoutiqueDocuments::create($data);
        }
    }

    /**
     * get All getOrders
     */
    public function getAll($filters = array(), $paginate = false)
    {
        $getData = OrderBoutiqueDocuments::with([
            'company',
            'getUser'
        ]);

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
     * getCompany
     */
    public function company()
    {
        return $this->belongsTo('App\Company','boutique_id');
    }

}

