<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use App\Roles;
use App\User;
use App\CompanyUser;

class UserRoles extends Model
{
    use Notifiable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'role_id'];

    /**
     * Insert and Update UserRoles
     */
    public function insertUpdate($data)
    {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0)
        {
            $getData = UserRoles::find($data['id']);
            $getData->update($data);
            return UserRoles::find($data['id']);
        } else {
            return UserRoles::create($data);
        }
    }

    /**
     * get all users whose role_id is 1 and id is not 1
     */
    public function getSuperAdminUsers($filters = array(), $paginate = false)
    {
        $getData = UserRoles::with([
            'user'
        ])->whereHas('user', function($query) use ($filters) {
            $query->whereNull('deleted_at');
        });

        if(isset($filters) && !empty($filters))
        {
            if(isset($filters['user_id']) && $filters['user_id'] == 1)
            {
                $getData->where('user_id', '!=', $filters['user_id']);
            }
            if(isset($filters['role_id']) && $filters['role_id'] == 1)
            {
                $getData->where('role_id', $filters['role_id']);
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

    public function getUserRolesByUserId($userId) {
        return UserRoles::where('user_id', $userId)->get();
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function companyUser() {
        return $this->belongsTo(CompanyUser::class, 'user_id', 'user_id');
    }

    public function roles() {
        return $this->belongsTo(Roles::class, 'role_id');
    }

    public function getUserRole($userId)
    {
        return UserRoles::where('user_id',$userId)->pluck('role_id')->first();
    }
}
