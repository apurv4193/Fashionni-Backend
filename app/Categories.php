<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;
use DB;
use Config;

class Categories extends Model
{
    use Notifiable;
    use SoftDeletes;

   /**
   * The database table used by the model.
   *
   * @var string
   */
    protected $table = 'categories';

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
     * A category a can have many category_images
     *
     * @return Illuminate\Database\Eloquent\Relations\Relation
     */
    public function category_images()
    {
        return $this->hasMany(CategoryImages::class, 'category_id');
    }

    /**
     * A category a can have many sub categroies
     *
     * @return Illuminate\Database\Eloquent\Relations\Relation
     */
    public function child_categroies()
    {
        return $this->hasMany(Categories::class, 'is_parent');
    }

    /**
     * A category a can has parent categroy
     *
     * @return Illuminate\Database\Eloquent\Relations\Relation
     */
    public function parent_categroy()
    {
        return $this->belongsTo(Categories::class, 'is_parent');
    }

    /**
    * Insert and Update Colors
    */
    public function insertUpdate($data)
    {
      if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0)
      {
        $getData = Categories::find($data['id']);
        $getData->update($data);
        return Categories::find($data['id']);
      } else {
        return Categories::create($data);
      }
    }

    /**
     * get all categories
     */
    public function getAll($filters = array(), $paginate = false)
    {
        $getData = Categories::with(['child_categroies', 'category_images'])->orderBy('created_at', 'DESC');

        if(isset($filters) && !empty($filters))
        {
            if(isset($filters['is_parent']))
            {
                $getData->where('is_parent', $filters['is_parent']);
            }

            if(isset($filters['level']) && !empty($filters['level']) && $filters['level'] > 0)
            {
                $getData->where('category_level', $filters['level']);
            }
        }
        if(isset($paginate) && $paginate == true) {
            return $getData->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        }
        else {
            return $getData->get();
        }
    }

    /**
     * get category details by id
     */
    public function getCategoryDetails($id)
    {
        $getData = Categories::with(['child_categroies.category_images', 'category_images'])->where('id', $id)->first();
        return $getData;
    }

}
