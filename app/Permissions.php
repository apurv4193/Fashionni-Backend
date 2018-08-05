<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Permissions extends Model
{
    use Notifiable;

    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['slug', 'label_name', 'is_default', 'company_id', 'default_edit_for', 'default_view_for'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Insert and Update Permissions
     */
    public function insertUpdate($data)
    {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0) 
        {
            $getData = Permissions::find($data['id']);
            $getData->update($data);
            return Permissions::find($data['id']);
        } else {
            return Permissions::create($data);
        }
    }

    public function checkUserPermission($companyId)
    {
        return Permissions::where('company_id', $companyId)->where('slug', 'boutique-user')->first();
    }    

    public function getPermissionBySlug($slug) {
        return Permissions::where('slug', $slug)->first();
    }
    

    public function getAllPermissions()
    {
        return Permissions::get(['slug','label_name','id']);
    }

    public function getPermissionByCompanyId($companyId) 
    {
        $getData = Permissions::whereNull('deleted_at')
                ->where(function($query) use ($companyId){
                    $query->where('company_id', $companyId)
                        ->orWhere('company_id', NULL);
                });
        return $getData->get();
    }
}
