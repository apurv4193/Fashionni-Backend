<?php

namespace App;

use Illuminate\Notifications\Notifiable;
//use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use DB;
use App\Company;
use App\Store;
use App\UserPermission;
use App\Permissions;

class Notifications extends Model
{
    use Notifiable;

//    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['notification_text', 'read_by', 'created_by', 'company_id', 'store_id', 'notification_page', 'created_at', 'updated_at'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];


    public function company()
    {
        return $this->belongsTo('App\Company', 'company_id');
    }

    public function store()
    {
        return $this->belongsTo('App\Store', 'store_id');
    }
    
    /**
     * Insert and Update Store
     */
    public function insertUpdate($data)
    {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0)
        {
            $getData = Notifications::find($data['id']);
            $getData->update($data);
            return Notifications::find($data['id']);
        } else {
            return Notifications::create($data);
        }
    }
    
    /**
     * get all Notifications
     */
    public function getAll($filters = array(), $paginate = false)
    {
        $getData = Notifications::whereNull('deleted_at')->orderBy('created_at', 'DESC');
        
        if(isset($filters) && !empty($filters)) 
        {
            if(isset($filters['read_by']) && !empty($filters['read_by']))
            {
                $read_by = $filters['read_by'];
                $getData->whereRaw("FIND_IN_SET(".$read_by.", read_by)");
            }
            elseif (isset($filters['unread_by']) && !empty($filters['unread_by'])) 
            {
                $unread_by = $filters['unread_by'];
                $getData->where(function($query) use ($unread_by) {
                        $query->whereRaw("NOT FIND_IN_SET(".$unread_by.", read_by)")
                            ->orWhere('read_by', NULL);
                    });
            }
            if(isset($filters['company_id']) && !empty($filters['company_id']))
            {
                $getData->where('company_id', $filters['company_id']);
            }
            if(isset($filters['product_id']) && !empty($filters['product_id']))
            {
                $getData->where('product_id', $filters['product_id']);
            }
            if(isset($filters['store_id']) && !empty($filters['store_id']))
            {
                $getData->where('store_id', $filters['store_id']);
            }
            if(isset($filters['notification_page']) && !empty($filters['notification_page']))
            {
                $getData->where('notification_page', $filters['notification_page']);
            }
        }
        if(isset($paginate) && $paginate == true) {
            return $getData->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        } else {
            return $getData->get();
        }
    }
   
}
