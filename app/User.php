<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Roles;
use App\Company;
use App\UserPermission;

class User extends Authenticatable
{
    use Notifiable;

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $guarded = [];
    protected $table = "users";


    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Insert and Update User
     */
    public function insertUpdate($data)
    {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0)
        {
            $user = User::find($data['id']);
            $user->update($data);
            return User::find($data['id']);
        } else {
            return User::create($data);
        }
    }

    public static function getCurrentUser() {
        return Auth::user();
    }

    // public function setPasswordAttribute($password) {
    //     $this->attributes['password'] = bcrypt($password);
    // }

    public function roles() {
        return $this->belongsToMany(Roles::class, 'user_roles', 'user_id', 'role_id');
    }

    public function company() {
        return $this->belongsToMany(Company::class, 'company_user', 'user_id', 'company_id');
    }

    public function hasRole($role) {
        return null !== $this->roles()->where('slug', $role)->first();
    }

    public function deleteUserByEmail($email) {
        return User::where('email', $email)->delete();
    }

    public function userCompany() {
        return $this->hasOne(CompanyUser::class, 'user_id');
    }

    public function userPermission() {
        return $this->hasOne(UserPermission::class, 'user_id');
    }

    public function getUserDetail($userId) {
        return User::with('roles')
                    ->with('userPermission')
                    ->where('id', $userId)
                    ->first();
    }

    public function userChat() {
        return $this->hasOne(UsersChat::class, 'user_id');
    }

    public function deleteUser($id)
    {
        CompanyUser::where('user_id', $id)->delete();
        UserRoles::where('user_id', $id)->delete();
        UserPermission::where('user_id', $id)->delete();
        User::where('id',$id)->update(['user_name' => null,'user_unique_id' => null,'email' => null,]);
        $userData = User::where('id',$id)->delete();
        return $userData;
    }

}
