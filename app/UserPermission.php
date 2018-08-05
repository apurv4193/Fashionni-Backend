<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    use Notifiable;

    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_permission';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'add', 'edit', 'delete', 'view'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Insert and Update UserPermission
     */
    public function insertUpdate($data)
    {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0) 
        {
            $getData = UserPermission::find($data['id']);
            $getData->update($data);
            return UserPermission::find($data['id']);
        } else {
            return UserPermission::create($data);
        }
    }

    public function getUserPermissionForViewByPermissionIdAndUserId($id,$userId) {
        return UserPermission::whereRaw('FIND_IN_SET("'.$id.'",view)')->where('user_id',$userId)->first();
    }

    public function getUserPermissionForEditByPermissionIdAndUserId($id,$userId) {
        return UserPermission::whereRaw('FIND_IN_SET("'.$id.'",edit)')->where('user_id',$userId)->first();
    }

    public function getAllUserPermissions($userId)
    {
        return UserPermission::where('user_id', $userId)->first();   
    }
    
}
