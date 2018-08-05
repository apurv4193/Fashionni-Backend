<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Config;
use App\Categories;

class Products extends Model
{
    use Notifiable;
    use SoftDeletes;
    use CascadeSoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'products';

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

    protected $cascadeDeletes = ['productImages','productColors','productMaterials','productInventory'];

    /**
     * Insert and Update Colors
     */
    public function insertUpdate($data)
    {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0) {
            $getData = Products::find($data['id']);
            $getData->update($data);
            return Products::find($data['id']);
        } else {
            return Products::create($data);
        }
    }

    /**
     * get All Product Details
     */
    public function getAllProductDetails()
    {
        $return = Products::with('productImages')->get();
        return $return;
    }

    /**
     * get All Product Details for perticular Company
     */
    public function getAllProductDetailsByFilter($companyId, $category1Id, $category2Id, $category3Id, $brandId, $searchText, $date_filter, $skipRecordCount, $userRoleSlug)
    {
        $return = Products::with('productImages')->take(Config::get('constant.PRODUCT_RECORD_PER_PAGE'))->skip($skipRecordCount);

        $return->selectRaw('*, (SELECT sum(product_inventory.product_quantity) FROM product_inventory WHERE product_inventory.product_id = products.id) As sum_quantity');

        if(isset($companyId) && $companyId != ""){
            $return->where('company_id', $companyId);
        }

        if(!empty($userRoleSlug) && $userRoleSlug != Config::get('constant.SUPER_ADMIN_SLUG'))
        {
            $return->where('is_published', '1');
        }

        if(isset($category1Id) && $category1Id != ""){
            $return->where('category_level1_id', $category1Id);
        }

        if(isset($category2Id) && $category2Id != ""){
            $return->where('category_level2_id', $category2Id);
        }

        if(isset($category3Id) && $category3Id != ""){
            $return->where('category_level3_id', $category3Id);
        }

        if(isset($brandId) && $brandId != ""){
            $return->where('brand_id', $brandId);
        }

        if(isset($date_filter) && !empty($date_filter) && $date_filter != ""){
            $return->whereDate('created_at', '>=', $date_filter)->orderBy('created_at', 'DESC');
        }

        if(isset($searchText) && $searchText != "")
        {
            $return->Where(function($query) use ($searchText) {
                $query->where('product_name_en', "Like", "%$searchText%");
                $query->orWhere('product_name_ch', "Like", "%$searchText%");
                $query->orWhere('product_name_ge', "Like", "%$searchText%");
                $query->orWhere('product_name_fr', "Like", "%$searchText%");
                $query->orWhere('product_name_it', "Like", "%$searchText%");
                $query->orWhere('product_name_sp', "Like", "%$searchText%");
                $query->orWhere('product_name_ru', "Like", "%$searchText%");
                $query->orWhere('product_name_jp', "Like", "%$searchText%");
            });
        }
        return $return->get();
    }


    /**
     * get Product Detail by product id
     */
    public function getProductDetailByProductId($product_id)
    {
        $return = Products::with('productImages')
                    ->with('productColors.color')
                    ->with('productMaterials.material')
                    ->where('id', $product_id)
                    ->first();
        return $return;
    }

    /**
     * get All Product Images
     */
    public function productImages()
    {
        $return = Products::hasMany('App\ProductImages', 'product_id');
        return $return;
    }

    /**
     * get Category level 1 relation ship
     */
    public function categoryLevel1()
    {
        return $this->belongsTo(Categories::class, 'category_level1_id');
    }

    /**
     * get Category level 2 relation ship
     */
    public function categoryLevel2()
    {
        return $this->belongsTo(Categories::class, 'category_level2_id');
    }

    /**
     * get Category level 3 relation ship
     */
    public function categoryLevel3()
    {
        return $this->belongsTo(Categories::class, 'category_level3_id');
    }

    /**
     * get Category level 4 relation ship
     */
    public function categoryLevel4()
    {
        return $this->belongsTo(Categories::class, 'category_level4_id');
    }

    /**
     * get Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * get All Product Color
     */
    public function productColors()
    {
        $return = Products::hasMany('App\ProductColors', 'product_id');
        return $return;
    }// return $this->belongsToMany('App\Colors', 'product_colors', 'product_id', 'color_id');

    /**
     * get Color
     */
    public function getColor()
    {
        return $this->hasManyThrough('App\Colors', 'App\ProductColors', 'product_id', 'id', 'id', 'color_id');
    }

    /**
     * get All Product Materials
     */
    public function productMaterials()
    {
        $return = Products::hasMany('App\ProductMaterials', 'product_id');
        return $return;
    }

    /**
     * get Materials
     */
    public function getMaterial()
    {
        return $this->belongsToMany('App\Materials', 'product_materials', 'product_id', 'material_id');
    }

    /**
     * get All Product Inventory
     */
    public function productInventory()
    {
        $return = Products::hasMany('App\ProductInventory', 'product_id');
        return $return;
    }

    /**
     * get Brands
     */
    public function brand()
    {
        return Products::belongsTo('App\Brands');
    }


     /**
     * get Category
     */
    public function getCategoryById($categoryId)
    {
        if(isset($categoryId) && $categoryId > 0)
        {
            $getData = Categories::find($categoryId);
        }
        else
        {
            $getData = [];
        }
        return $getData;
    }

    public function getOnlyProductDetails($productId)
    {
        return Products::with('company')->where('id',$productId)->first();
    }
}

