<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use DB;
use App\Permissions;

class Store extends Model
{
    use Notifiable;

    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'store';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'store_name', 'store_slug', 'store_image', 'short_name', 'address', 'postal_code', 'city', 'state', 'country', 'store_contact_person_name', 'store_contact_person_email', 'store_contact_person_image', 'store_contact_person_telephone', 'store_contact_person_position', 'store_lat', 'store_lng', 'random_number'];

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
    
    public function storeTime()
    {
        return $this->hasOne('App\StoreTime', 'store_id');
    }

    /**
     * Insert and Update Store
     */
    public function insertUpdate($data)
    {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0) 
        {
            $store = Store::find($data['id']);
            $store->update($data);
            return Store::find($data['id']);
        } else {
            return Store::create($data);
        }
    }

    public function getStoreBySlug($slug)
    {
        return Store::where('store_slug',$slug)->first();
    }

    public function getStoreByCompanyId($companyId)
    {
        return Store::where('company_id',$companyId)->get();
    }

    public function deleteStore($id)
    {
        $storeData = Store::find($id);
        if($storeData){
            Permissions::where('slug',$storeData->store_slug)->delete();
            Store::where('id',$id)->update(['store_slug' => null]);
            $storeData->delete();
        }
        return $storeData;
    }
    
}
