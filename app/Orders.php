<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Config;
use App\Company;

class Orders extends Model
{
    use Notifiable;
    use SoftDeletes;
    use CascadeSoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'orders';

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
            $getData = Orders::find($data['id']);
            $getData->update($data);
            return Orders::find($data['id']);
        }
        else
        {
            return Orders::create($data);
        }
    }

     /**
     * get All Company
     */
    public function getCompanyAllDetail($filters = array(), $paginate = false)
    {
        $getData = Company::whereNull('deleted_at');

        if(isset($filters) && !empty($filters))
        {
            if(isset($filters['country']) && !empty($filters['country']))
            {
                $getData->where('country', $filters['country']);
            }

            if(isset($filters['boutique_id']) && !empty($filters['boutique_id']))
            {
                $getData->where('company_unique_id', $filters['boutique_id']);
            }

            if(isset($filters['boutique_alphabet']) && !empty($filters['boutique_alphabet']))
            {
                $getData->where('company_name', 'like', $filters['boutique_alphabet'].'%');
            }

            if(isset($filters['search_key']) && !empty($filters['search_key']))
            {
                $getData->where('company_name', 'like', '%'.$filters['search_key'].'%');
            }

            if(isset($filters['start_date']) && !empty($filters['start_date']) && isset($filters['end_date']) && !empty($filters['end_date']))
            {
                $getData->where(function($getData) use ($filters)
                {
                    $getData->whereHas('orderAttributes', function($query) use ($filters)
                    {
                        $query->whereDate('orders.created_at','>=', $filters['start_date'])
                            ->whereDate('orders.created_at','<=', $filters['end_date']);
                    });
                });
            }
        }
        if(isset($paginate) && $paginate == true)
        {
            return $getData->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        }
        else
        {
            return $getData->get(['id', 'company_unique_id', 'company_name', 'company_image', 'country']);
        }
        // return Company::get(['id', 'company_unique_id', 'company_name', 'company_image']);
    }

    /**
     * get All getOrders
     */
    public function getAll($filters = array(), $paginate = false)
    {
        $getData = Orders::whereNull('deleted_at');

        if(isset($filters) && !empty($filters))
        {
            if(isset($filters['search_key']) && $filters['search_key'] != '')
            {
                $getData->where('order_number', 'like', '%'.$filters['search_key'].'%');
            }
            if(isset($filters['boutique_id']) && $filters['boutique_id'] != '')
            {
                $getData->whereIn('id', function($query) use ($filters) {
                    $query->select('order_id')
                    ->from('order_boutique_attributes')
                    ->where('boutique_id', $filters['boutique_id']);
                });
            }
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
     * get All Order Boutique Items
     * relationship id is order_id
     */
    public function orderDetails($filters = array())
    {
        $getData = Orders::whereNull('deleted_at')->with(
            [
                'orderBoutiqueItems.orderBoutiqueProduct.brand',
                'orderBoutiqueItems.orderBoutiqueProduct.productImages',
                'orderBoutiqueAttributes.company',
                'orderBoutiqueDocuments.company'
            ]);

        if(isset($filters) && !empty($filters))
        {
            if(isset($filters['boutique_id']) && $filters['boutique_id'] != '' && $filters['boutique_id'] > 0)
            {
                $getData->with([
                    'orderBoutiqueAttributes' => function($query) use($filters){
                        $query->where('boutique_id', $filters['boutique_id']);
                    }
                ]);
                $getData->with([
                    'orderBoutiqueItems' => function($query) use($filters){
                        $query->where('boutique_id', $filters['boutique_id']);
                    }
                ]);
                $getData->with([
                    'orderBoutiqueDocuments' => function($query) use($filters){
                        $query->where('boutique_id', $filters['boutique_id']);
                    }
                ]);
            }
        }
        return $getData->find($filters['order_id']);
    }

    /**
     * get All Order Boutique Items
     * relationship id is order_id
     */
    public function orderBoutiqueItems()
    {
        $return = $this->hasMany('App\OrderBoutiqueItems', 'order_id');
        return $return;
    }

    /**
     * get All Order Boutique Attributes
     * relationship id is order_id
     */
    public function orderBoutiqueAttributes()
    {
        $return = $this->hasMany('App\OrderBoutiqueAttributes', 'order_id');
        return $return;
    }

    /**
     * get All Order Boutique Documents
     * relationship id is order_id
     */
    public function orderBoutiqueDocuments()
    {
        $return = $this->hasMany('App\OrderBoutiqueDocuments', 'order_id');
        return $return;
    }

}

