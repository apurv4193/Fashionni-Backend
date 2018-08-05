<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CompanyUser extends Model
{
    use Notifiable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'company_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'company_id', 'default'];

    /**
     * Insert and Update CompanyUser
     */
    public function insertUpdate($data)
    {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0) 
        {
            $companyUser = CompanyUser::find($data['id']);
            $companyUser->update($data);
            return CompanyUser::find($data['id']);
        } else {
            return CompanyUser::create($data);
        }
    }
    
    public function getCompany()
    {
        return $this->belongsTo('App\Company', 'company_id');
    }
    
    public function getUser()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    
    public function deleteCompanyAllUserByCompanyId($companyId) {
        return CompanyUser::where('company_id', $companyId)->delete();
    }

    public function userRoles()
    {
        return $this->belongsTo('App\UserRoles', 'user_id');
    }

    public function getCompanyUsers($companyId)
    {
        // return CompanyUser::where('company_id', $companyId)->get();
        return CompanyUser::with('getUser')->where('company_id', $companyId)->get();
    }

    public function getCompanyByUserId($userId)
    {
        return CompanyUser::where('user_id', $userId)->first();
    }

    public function getCompanyDefaultByUserId($companyId)
    {
        return CompanyUser::with('getUser')->where('default', '1')->where('company_id', $companyId)->first();
    }

}
