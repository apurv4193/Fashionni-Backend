<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;
use DB;
use Config;
use App\Company;
use App\CompanyUser;
use App\User;

class UsersChat extends Model
{
    use Notifiable;
    use SoftDeletes;

   /**
   * The database table used by the model.
   *
   * @var string
   */
    protected $table = 'users_chat';

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


    /**
     * A Boutique user a can has company
     *
     * @return Illuminate\Database\Eloquent\Relations\Relation
     */
    public function company()
    {
        return $this->belongsTo('App\Company', 'company_id');
    }

    /**
     * A Boutique user a details
     *
     * @return Illuminate\Database\Eloquent\Relations\Relation
     */
    public function getUser()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * A Super admin user a details
     *
     * @return Illuminate\Database\Eloquent\Relations\Relation
     */
    public function getSuperAdminUser()
    {
        return $this->belongsTo('App\User', 'created_by');
    }

    /**
     * get all Company and User Data
     */   
    public function getAllCompany($filters = array(), $paginate = false)
    {
        $getData = Company::with([
            'companyChatUsers.userChat'
        ])->orderBy('updated_at', 'DESC');
        
        if(isset($filters) && !empty($filters))
        {
            
        }
        if(isset($paginate) && $paginate == true) {
            return $getData->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        }
        else {
            return $getData->get();
        }
    }
    
    /**
     * get all UsersChat
     */
    public function getAll($filters = array(), $paginate = false)
    {
        $getData = UsersChat::with([
            'company', 
            'getUser'
        ]);
        
        if(isset($filters) && !empty($filters))
        {
            if(isset($filters['created_by']) && $filters['created_by'] > 0)
            {
                $getData->where(function($query) use($filters) {
                    $query->where('is_default', 1)->orWhere('created_by', $filters['created_by']);
                });
            }
            if(isset($filters['search_key']) && !empty($filters['search_key']))
            {                
                $getData->where(function($getData) use ($filters) {
                    $getData->where(function($getData) use ($filters) {
                        $getData->whereHas('company', function($query) use ($filters){
                            $query->where('company_name', 'like', '%'.$filters['search_key'].'%');
                        });   
                    });
                    $getData->orWhere(function($getData) use ($filters) {
                        $getData->orWhereHas('getUser', function($query) use ($filters){
                            $query->where('name', 'like', '%'.$filters['search_key'].'%');
                        });  
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
            return $getData->get();
        }
    }
    
    /**
     * Search User chat
     */    
    public function getSearchChatUsers($filters = array(), $paginate = false)
    {
        $getData = UsersChat::with([
            'company', 
            'getUser'
        ]);
        if(isset($filters) && !empty($filters))
        {
            if(isset($filters['created_by']) && $filters['created_by'] > 0)
            {
                $getData->where(function($query) use($filters) {
                    $query->where('is_default', 1)->orWhere('created_by', $filters['created_by']);
                });
            }
            if(isset($filters['search_key']) && !empty($filters['search_key']))
            {   
                $getData->where(function($getData) use ($filters) {
                    $getData->where(function($getData) use ($filters) {
                        $getData->whereHas('company', function($query) use ($filters){
                            $query->where('company_name', 'like', '%'.$filters['search_key'].'%');
                        });   
                    });
                    $getData->orWhere(function($getData) use ($filters) {
                        $getData->orWhereHas('getUser', function($query) use ($filters){
                            $query->where('name', 'like', '%'.$filters['search_key'].'%');
                        });  
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
            return $getData->get();
        }
    }
    
    
    /**
    * Insert and Update Colors
    */
    public function insertUpdate($data)
    {
      if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0)
      {
        $getData = UsersChat::find($data['id']);
        $getData->update($data);
        return UsersChat::find($data['id']);
      } else {
        return UsersChat::create($data);
      }
    }
    
    /**
     * get category details by id
     */
    public function getCategoryDetails($id)
    {
        $getData = UsersChat::with(['child_categroies.category_images', 'category_images'])->where('id', $id)->first();
        return $getData;
    }

}
