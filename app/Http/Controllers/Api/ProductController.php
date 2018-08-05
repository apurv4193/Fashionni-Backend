<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\User;
use App\Company;
use App\Products;
use App\ProductMaterials;
use App\ProductColors;
use App\ProductImages;
use App\UserRoles;
use App\ProductInventory;
use Config;
use Validator;
use DB;
use Auth;
use Input;
use \stdClass;
use Storage;
use Helpers;
use JWTAuth;
use JWTAuthException;


class ProductController extends Controller
{
    public function __construct()
    {
        $this->objUser = new User();
        $this->objUserRoles = new UserRoles();
        $this->objProducts = new Products();
        $this->objCompany = new Company();
        $this->objProductImages = new ProductImages();
        $this->objProductColors = new ProductColors();
        $this->objProductMaterials = new ProductMaterials();
        $this->objProductsInventory = new ProductInventory();

        $this->productOriginalImageUploadPath = Config::get('constant.PRODUCT_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->productThumbImageUploadPath = Config::get('constant.PRODUCT_THUMB_IMAGE_UPLOAD_PATH');
        $this->productThumbImageHeight = Config::get('constant.PRODUCT_THUMB_IMAGE_HEIGHT');
        $this->productThumbImageWidth = Config::get('constant.PRODUCT_THUMB_IMAGE_WIDTH');

        $this->defaultImage = Config::get('constant.DEFAULT_IMAGE_PATH');
        $this->productRecordPerPage = Config::get('constant.PRODUCT_RECORD_PER_PAGE');

        $this->productCodeOriginalImageUploadPath = Config::get('constant.PRODUCT_CODE_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->productCodeThumbImageUploadPath = Config::get('constant.PRODUCT_CODE_THUMB_IMAGE_UPLOAD_PATH');
        $this->productCodeThumbImageHeight = Config::get('constant.PRODUCT_CODE_THUMB_IMAGE_HEIGHT');
        $this->productCodeThumbImageWidth = Config::get('constant.PRODUCT_CODE_THUMB_IMAGE_WIDTH');

        $this->productBrandLabelOriginalImageUploadPath = Config::get('constant.PRODUCT_BRAND_LABEL_WITH_ORIGINAL_INFORMATION_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->productBrandLabelThumbImageUploadPath = Config::get('constant.PRODUCT_BRAND_LABEL_WITH_ORIGINAL_INFORMATION_THUMB_IMAGE_UPLOAD_PATH');
        $this->productBrandLabelThumbImageHeight = Config::get('constant.PRODUCT_BRAND_LABEL_WITH_ORIGINAL_INFORMATION_THUMB_IMAGE_HEIGHT');
        $this->productBrandLabelThumbImageWidth = Config::get('constant.PRODUCT_BRAND_LABEL_WITH_ORIGINAL_INFORMATION_THUMB_IMAGE_WIDTH');

        $this->productWashCareOriginalImageUploadPath = Config::get('constant.PRODUCT_WASH_CARE_WITH_MATERIAL_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->productWashCareThumbImageUploadPath = Config::get('constant.PRODUCT_WASH_CARE_WITH_MATERIAL_THUMB_IMAGE_UPLOAD_PATH');
        $this->productWashCareThumbImageHeight = Config::get('constant.PRODUCT_WASH_CARE_WITH_MATERIAL_THUMB_IMAGE_HEIGHT');
        $this->productWashCareThumbImageWidth = Config::get('constant.PRODUCT_WASH_CARE_WITH_MATERIAL_THUMB_IMAGE_WIDTH');

        $this->brandOriginalImageUploadPath = Config::get('constant.BRANDS_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->brandThumbImageUploadPath = Config::get('constant.BRANDS_THUMB_IMAGE_UPLOAD_PATH');
        $this->brandThumbImageHeight = Config::get('constant.BRANDS_THUMB_IMAGE_HEIGHT');
        $this->brandThumbImageWidth = Config::get('constant.BRANDS_THUMB_IMAGE_WIDTH');

        $this->colorOriginalImageUploadPath = Config::get('constant.COLOR_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->colorThumbImageUploadPath = Config::get('constant.COLOR_THUMB_IMAGE_UPLOAD_PATH');
        $this->colorThumbImageHeight = Config::get('constant.COLOR_THUMB_IMAGE_HEIGHT');
        $this->colorThumbImageWidth = Config::get('constant.COLOR_THUMB_IMAGE_WIDTH');

        $this->materialOriginalImageUploadPath = Config::get('constant.MATERIAL_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->materialThumbImageUploadPath = Config::get('constant.MATERIAL_THUMB_IMAGE_UPLOAD_PATH');
        $this->materialThumbImageHeight = Config::get('constant.MATERIAL_THUMB_IMAGE_HEIGHT');
        $this->materialThumbImageWidth = Config::get('constant.MATERIAL_THUMB_IMAGE_WIDTH');

        $this->productInventoryRecordPerPage = Config::get('constant.PRODUCT_INVENTORY_RECORD_PER_PAGE');
        
        $this->defaultImage = Config::get('constant.DEFAULT_IMAGE_PATH');
        $this->defaultPlusImage = Config::get('constant.DEFAULT_PLUS_IMAGE_PATH');
    }

    //  Get product list.
    public function getProductsList(Request $request)
    {
        try
        {
            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id, Config::get('constant.BOUTIQUE_PRODUCT_SLUG'),'view');
            if($checkAuthorization == '0')
            {
                return response()->json([
                    'status' => 0,
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }

            $userRoles = $this->objUserRoles->getUserRolesByUserId(Auth::user()->id);            
            $userRoleSlug = "";
            if(isset($userRoles) && !empty($userRoles))
            {
                $userRoleSlug = $userRoles[0]->roles->slug;
            }
            else
            {
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.user_not_found')
                ],400);
            }
            $rules = [
                'page_no' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) 
            {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ],400);
            }
            
            $skipRecordCount = 0;
            $nextPageCount = 0;

            if(isset($request->page_no) && $request->page_no > 0) 
            {
                $skipRecordCount = ($request->page_no-1) * $this->productRecordPerPage;
            }

            $productList = [];

            $company_id = "";
            $category_1_id = "";
            $category_2_id = "";
            $category_3_id = "";
            $brand_id = "";
            $date_filter = "";
            $search_text = "";

            if(isset($request->company_id) && $request->company_id > 0)
            {
                $company_id = $request->company_id;
                $getCompanyData = Company::find($company_id);
            }

            if(isset($request->category_1_id)){
                $category_1_id = $request->category_1_id;
            }

            if(isset($request->category_2_id)){
                $category_2_id = $request->category_2_id;
            }

            if(isset($request->category_3_id)){
                $category_3_id = $request->category_3_id;
            }

            if(isset($request->brand_id)){
                $brand_id = $request->brand_id;
            }
            
            if(isset($request->date_filter) && !empty($request->date_filter))
            {
                $date_filter = Helpers::getDate($request->date_filter);
            }

            if(isset($request->search_text)){
                $search_text = $request->search_text;
            }

            $productList = $this->objProducts->getAllProductDetailsByFilter($company_id, $category_1_id, $category_2_id, $category_3_id, $brand_id, $search_text, $date_filter, $skipRecordCount, $userRoleSlug);
            
            $productListNextPage = $this->objProducts->getAllProductDetailsByFilter($company_id, $category_1_id, $category_2_id, $category_3_id, $brand_id, $search_text, $date_filter, ($skipRecordCount+$this->productRecordPerPage), $userRoleSlug);
            if(count($productListNextPage)>0){
                $nextPageCount = 1;
            }

            $productsData = [];
            if(isset($productList) && count($productList) > 0) 
            {
                foreach ($productList as $key => $value) 
                {
                    $data = [];
                    $data['id'] = (string) $value->id;
                    $data['company_id'] = (string) $value->company_id;
                    $data['brand_id'] = (string) $value->brand_id;
                    $data['brand_name'] = (isset($value->brand) && !empty($value->brand) && !empty($value->brand->brand_name)) ? $value->brand->brand_name : '';
                    $data['product_name_en'] = (string) $value->product_name_en;
                    $data['product_name_ch'] = (string) $value->product_name_ch;
                    $data['product_name_ge'] = (string) $value->product_name_ge;
                    $data['product_name_fr'] = (string) $value->product_name_fr;
                    $data['product_name_it'] = (string) $value->product_name_it;
                    $data['product_name_sp'] = (string) $value->product_name_sp;
                    $data['product_name_ru'] = (string) $value->product_name_ru;
                    $data['product_name_jp'] = (string) $value->product_name_jp;
                    
                    $data['company_product_number'] = (string) $value->company_product_number;
                    $data['product_number'] = $value->product_number;
                    
                    $data['out_of_stock'] = 0;
                    if(isset($value->sum_quantity) && $value->sum_quantity > 0)
                    {
                        $data['out_of_stock'] = 1;
                    }
                    
                    $data['edit_status_color_code'] = (string) ""; // 0 No Color : as changes made in Product

                    if($value->is_published == "0")
                    {
                        $data['edit_status_color_code'] = (string) "#d6d6d6"; // 1 Color -> Gray : not published by super admin
                    }
                    elseif($value->is_published == "1")
                    {
                        if($value->product_retail_price == "" || $value->product_discount_rate == "" || $value->product_vat_rate == "" )
                        {
                            $data['edit_status_color_code'] = (string) " #f8ba00"; // 2 Color -> Yellow : Product published but boutique admin has not added data
                        }
                        else
                        {
                            if($userRoleSlug == Config::get('constant.SUPER_ADMIN_SLUG'))
                            {
                                if($value->updated_by_boutique_admin == "1")
                                {
                                    $data['edit_status_color_code'] = (string) "#ff2600"; // 3 Color -> Red : boutique admin has made changes in product data
                                } 
                            }
                            elseif($userRoleSlug == Config::get('constant.ADMIN_SLUG'))
                            {
                                if($value->updated_by_boutique_super_admin == "1")
                                {
                                    $data['edit_status_color_code'] = (string) "#ff2600"; // 3 Color -> Red : boutique admin has made changes in product data
                                }
                            }                            
                        }
                    }

                    $productImages = [];

                    if(isset($value->productImages) && count($value->productImages)>0) {
                        foreach ($value->productImages as $k => $v) {
                            $imageData['id'] = (string) $v->id;

                            $imageData['file_name'] = (string) (isset($v->file_name) && $v->file_name != NULL && $v->file_name != '' && Storage::exists($this->productOriginalImageUploadPath.$v->file_name) && Storage::size($this->productOriginalImageUploadPath.$v->file_name) > 0) ? Storage::url($this->productOriginalImageUploadPath . $v->file_name) : url($this->defaultImage);

                            $imageData['file_position'] = (string) $v->file_position;
                            $productImages[] = $imageData;
                        }
                    }
                    $data['product_images'] = $productImages;
                    $productsData[] = $data;
                }
            }
            else {
                return response()->json([
                    'status' => '1',
                    'message' => trans('apimessages.product_not_found'),
                    'default_vat_rate' => (isset($getCompanyData) && !empty($getCompanyData) && !empty($getCompanyData->default_vat_rate) ? $getCompanyData->default_vat_rate : 0),
                    'next' => $nextPageCount,
                    'data' => []
                ],200);
            }

            return response()->json([
                'status' => '1',
                'message' => trans('apimessages.success'),
                'next' => $nextPageCount,
                'default_vat_rate' => (isset($getCompanyData) && !empty($getCompanyData) && !empty($getCompanyData->default_vat_rate) ? $getCompanyData->default_vat_rate : 0),
                'data' => $productsData
            ],200);

        }
        catch (Exception $e) {
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_getting_product_details'),
                'code' => $e->getStatusCode()
            ]);
        }
    }

    //  Get product Detail
    public function getProductsDetail(Request $request)
    {
        try
        {
            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id, Config::get('constant.BOUTIQUE_PRODUCT_SLUG'),'view');
            if($checkAuthorization == '0')
            {
                return response()->json([
                    'status' => 0,
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }

            $rules = [
                'product_id' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails())
            {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ], 400);
            }

            $userRoles = $this->objUserRoles->getUserRolesByUserId(Auth::user()->id);
            $userRoleSlug = "";
            if(isset($userRoles) && !empty($userRoles)){
                $userRoleSlug = $userRoles[0]->roles->slug;
            }
            else{
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.user_not_found')
                ],400);
            }

            $productData = $this->objProducts->getProductDetailByProductId($request->product_id);

            if(isset($productData) && !empty($productData))
            {
                if($userRoleSlug == Config::get('constant.ADMIN_SLUG')){
                    if($productData->is_published != "1"){
                        DB::rollback();
                        return response()->json([
                            'status' => '0',
                            'message' => trans('apimessages.product_is_not_published')
                        ],400);
                    }
                }
                if($productData->is_published == "1")
                {
                    if($userRoleSlug == Config::get('constant.SUPER_ADMIN_SLUG'))
                    {
                        if($productData->updated_by_boutique_admin == "1")
                        {
                            $productData->updated_by_boutique_admin = 0;
                        } 
                    }
                    elseif($userRoleSlug == Config::get('constant.ADMIN_SLUG'))
                    {
                        if($productData->updated_by_boutique_super_admin == "1")
                        {
                            $productData->updated_by_boutique_super_admin = 0;
                        }
                    }
                    $productData->save();
                }
                
                if(isset($productData->categoryLevel1) && !empty($productData->categoryLevel1))
                {
                    $productData->categoryLevel1;
                }
                else
                {
                    $productData->categoryLevel1 = new \stdClass();
                }
                if(isset($productData->categoryLevel2) && !empty($productData->categoryLevel2))
                {
                    $productData->categoryLevel2;
                }
                else
                {
                    $productData->categoryLevel2 = new \stdClass();
                }
                if(isset($productData->categoryLevel3) && !empty($productData->categoryLevel3))
                {
                    $productData->categoryLevel3;
                }
                else
                {
                    $productData->categoryLevel3 = new \stdClass();
                }
                if(isset($productData->categoryLevel4) && !empty($productData->categoryLevel4))
                {
                    $productData->categoryLevel4;
                }
                else
                {
                    $productData->categoryLevel4 = new \stdClass();
                }

                if(isset($productData->brand) && $productData->brand && !empty($productData->brand))
                {
                    $productData->brand->brand_image = (isset($productData->brand) && !empty($productData->brand) && !empty($productData->brand->brand_image) && Storage::exists($this->brandOriginalImageUploadPath.$productData->brand->brand_image) && Storage::size($this->brandOriginalImageUploadPath.$productData->brand->brand_image) > 0) ? Storage::url($this->brandOriginalImageUploadPath.$productData->brand->brand_image) : '';
                }
                else
                {
                     $productData->brand = new \stdClass();
                }

                $productData->code_image = (string) (isset($productData->code_image) && $productData->code_image != NULL && $productData->code_image != '' && Storage::exists($this->productCodeOriginalImageUploadPath.$productData->code_image) && Storage::size($this->productCodeOriginalImageUploadPath.$productData->code_image) > 0) ? Storage::url($this->productCodeOriginalImageUploadPath . $productData->code_image) : "";

                $productData->brand_label_with_original_information_image = (string) (isset($productData->brand_label_with_original_information_image) && $productData->brand_label_with_original_information_image != NULL && $productData->brand_label_with_original_information_image != '' && Storage::exists($this->productBrandLabelOriginalImageUploadPath.$productData->brand_label_with_original_information_image) && Storage::size($this->productBrandLabelOriginalImageUploadPath.$productData->brand_label_with_original_information_image) > 0) ? Storage::url($this->productBrandLabelOriginalImageUploadPath . $productData->brand_label_with_original_information_image) : "";

                $productData->wash_care_with_material_image = (string) (isset($productData->wash_care_with_material_image) && $productData->wash_care_with_material_image != NULL && $productData->wash_care_with_material_image != '' && Storage::exists($this->productWashCareOriginalImageUploadPath.$productData->wash_care_with_material_image) && Storage::size($this->productWashCareOriginalImageUploadPath.$productData->wash_care_with_material_image) > 0) ? Storage::url($this->productWashCareOriginalImageUploadPath . $productData->wash_care_with_material_image) : "";

                if(isset($productData->productImages) && !empty($productData->productImages))
                {
                    foreach ($productData->productImages as $k => $v)
                    {
                        $productData->productImages[$k]['id'] = (string) $v->id;
                        $productData->productImages[$k]['file_name'] = (string) (isset($v->file_name) && $v->file_name != NULL && $v->file_name != '' && Storage::exists($this->productOriginalImageUploadPath.$v->file_name) && Storage::size($this->productOriginalImageUploadPath.$v->file_name) > 0) ? Storage::url($this->productOriginalImageUploadPath . $v->file_name) : url($this->defaultImage);

                        $productData->productImages[$k]['file_position'] = (string) $v->file_position;
                        unset($productData->productImages[$k]['product_id']);
                        unset($productData->productImages[$k]['created_at']);
                        unset($productData->productImages[$k]['updated_at']);
                        unset($productData->productImages[$k]['deleted_at']);
                    }
                }
                if(isset($productData->productColors) && !empty($productData->productColors))
                {
                    foreach ($productData->productColors as $k => $v)
                    {
                        unset($productData->productColors[$k]['product_id']);
                        unset($productData->productColors[$k]['created_at']);
                        unset($productData->productColors[$k]['updated_at']);
                        unset($productData->productColors[$k]['deleted_at']);
                        $productData->productColors[$k]['id'] = $productData->productColors[$k]->id;
                        $productData->productColors[$k]['color_id'] = $productData->productColors[$k]->color_id;
                        if(isset($productData->productColors[$k]->color) && !empty($productData->productColors[$k]->color))
                        {
                            $productData->productColors[$k]['color_name_en'] = $productData->productColors[$k]->color->color_name_en;
                            $productData->productColors[$k]['color_name_ch'] = $productData->productColors[$k]->color->color_name_ch;
                            $productData->productColors[$k]['color_name_ge'] = $productData->productColors[$k]->color->color_name_ge;
                            $productData->productColors[$k]['color_name_fr'] = $productData->productColors[$k]->color->color_name_fr;
                            $productData->productColors[$k]['color_name_it'] = $productData->productColors[$k]->color->color_name_it;
                            $productData->productColors[$k]['color_name_sp'] = $productData->productColors[$k]->color->color_name_sp;
                            $productData->productColors[$k]['color_name_ru'] = $productData->productColors[$k]->color->color_name_ru;
                            $productData->productColors[$k]['color_name_jp'] = $productData->productColors[$k]->color->color_name_jp;
                            $productData->productColors[$k]['color_unique_id'] = $productData->productColors[$k]->color->color_unique_id;
                            $colorImage = ((isset($productData->productColors[$k]->color) && !empty($productData->productColors[$k]->color->color_image)) && Storage::exists($this->colorOriginalImageUploadPath.$productData->productColors[$k]->color->color_image)  && Storage::size($this->colorOriginalImageUploadPath.$productData->productColors[$k]->color->color_image) > 0) ? Storage::url($this->colorOriginalImageUploadPath.$productData->productColors[$k]->color->color_image) : url($this->defaultImage);

                            $productData->productColors[$k]['color_image'] = $colorImage;
                            unset($productData->productColors[$k]->color);
                        }
                    }

                }
                if(isset($productData->productMaterials) && !empty($productData->productMaterials))
                {
                    foreach ($productData->productMaterials as $k => $v)
                    {
                        $productData->productMaterials[$k]['id'] = $productData->productMaterials[$k]->id;
                        $productData->productMaterials[$k]['material_id'] = $productData->productMaterials[$k]->material_id;
                        unset($productData->productMaterials[$k]['product_id']);
                        unset($productData->productMaterials[$k]['created_at']);
                        unset($productData->productMaterials[$k]['updated_at']);
                        unset($productData->productMaterials[$k]['deleted_at']);
                        if(isset($productData->productMaterials[$k]->material) && !empty($productData->productMaterials[$k]->material))
                        {
                            $productData->productMaterials[$k]['material_name_en'] = $productData->productMaterials[$k]->material->material_name_en;
                            $productData->productMaterials[$k]['material_name_ch'] = $productData->productMaterials[$k]->material->material_name_ch;
                            $productData->productMaterials[$k]['material_name_ge'] = $productData->productMaterials[$k]->material->material_name_ge;
                            $productData->productMaterials[$k]['material_name_fr'] = $productData->productMaterials[$k]->material->material_name_fr;
                            $productData->productMaterials[$k]['material_name_it'] = $productData->productMaterials[$k]->material->material_name_it;
                            $productData->productMaterials[$k]['material_name_sp'] = $productData->productMaterials[$k]->material->material_name_sp;
                            $productData->productMaterials[$k]['material_name_ru'] = $productData->productMaterials[$k]->material->material_name_ru;
                            $productData->productMaterials[$k]['material_name_jp'] = $productData->productMaterials[$k]->material->material_name_jp;
                            $productData->productMaterials[$k]['material_unique_id'] = $productData->productMaterials[$k]->material->material_unique_id;
                            $materialImage = ((isset($productData->productMaterials[$k]->material) && !empty($productData->productMaterials[$k]->material->material_image)) && Storage::exists($this->materialOriginalImageUploadPath.$productData->productMaterials[$k]->material->material_image)  && Storage::size($this->materialOriginalImageUploadPath.$productData->productMaterials[$k]->material->material_image) > 0) ? Storage::url($this->materialOriginalImageUploadPath.$productData->productMaterials[$k]->material->material_image) : url($this->defaultImage);

                            $productData->productMaterials[$k]['material_image'] = $materialImage;
                            unset($productData->productMaterials[$k]->material);
                        }
                    }
                }

                return response()->json([
                    'status' => '1',
                    'message' => trans('apimessages.success'),
                    'data' => $productData
                ],200);
            }
            else
            {
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.product_not_found')
                ],400);
            }
        }
        catch (Exception $e) {
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_getting_product_details'),
                'code' => $e->getStatusCode()
            ]);
        }
    }

    // save Products Details
    public function saveProductsDetails(Request $request)
    {
        try
        {
            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id, Config::get('constant.BOUTIQUE_PRODUCT_SLUG'),'edit');
            if($checkAuthorization == '0')
            {
                return response()->json([
                    'status' => 0,
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }

            $userRoles = $this->objUserRoles->getUserRolesByUserId(Auth::user()->id);
            $userRoleSlug = "";
            if(isset($userRoles) && count($userRoles)>0){
                $userRoleSlug = $userRoles[0]->roles->slug;
            }            
            else{
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.user_not_found')
                ],400);
            }
            if($userRoleSlug == Config::get('constant.SUPER_ADMIN_SLUG'))
            {
                if(isset($request->is_published) && $request->is_published > 0)
                {
                    $rules = [
                        'category_level1_id' => 'required',
                        'category_level2_id' => 'required',
                        'category_level3_id' => 'required',
                        'product_name_en' => 'required',
                        'product_name_ch' => 'required',
                        'product_name_ge' => 'required',
                        'product_name_fr' => 'required',
                        'product_name_it' => 'required',
                        'product_name_sp' => 'required',
                        'product_name_ru' => 'required',
                        'product_name_jp' => 'required'
                    ];                    
                }
                else{
                    $rules = [
                        'product_name_en' => 'required_without_all:product_name_ch,product_name_ge,product_name_fr,product_name_it,product_name_sp,product_name_ru,product_name_jp',
                        'product_name_ch' => 'required_without_all:product_name_en,product_name_ge,product_name_fr,product_name_it,product_name_sp,product_name_ru,product_name_jp',
                        'product_name_ge' => 'required_without_all:product_name_en,product_name_ch,product_name_fr,product_name_it,product_name_sp,product_name_ru,product_name_jp',
                        'product_name_fr' => 'required_without_all:product_name_en,product_name_ch,product_name_ge,product_name_it,product_name_sp,product_name_ru,product_name_jp',
                        'product_name_it' => 'required_without_all:product_name_en,product_name_ch,product_name_ge,product_name_fr,product_name_sp,product_name_ru,product_name_jp',
                        'product_name_sp' => 'required_without_all:product_name_en,product_name_ch,product_name_ge,product_name_fr,product_name_it,product_name_ru,product_name_jp',
                        'product_name_ru' => 'required_without_all:product_name_en,product_name_ch,product_name_ge,product_name_fr,product_name_it,product_name_sp,product_name_jp',
                        'product_name_jp' =>'required_without_all:product_name_en,product_name_ch,product_name_ge,product_name_fr,product_name_it,product_name_sp,product_name_ru',
                        'brand_label_with_original_information_image' => 'mimes:jpeg,jpg,bmp,png,gif|max:52400',
                        'wash_care_with_material_image' => 'mimes:jpeg,jpg,bmp,png,gif|max:52400',
                        'code_image' => 'mimes:jpeg,jpg,bmp,png,gif|max:52400', 
                        'category_level1_id' => 'nullable',
                        'category_level2_id' => 'nullable',
                        'category_level3_id' => 'nullable',
                        'action' => 'required',
                        'id' => 'required',
                    ];
                }
                
            }
            if($userRoleSlug == Config::get('constant.ADMIN_SLUG')){
                $rules = [
                    'product_retail_price' => 'required',
                    'product_discount_rate' => 'required',
                    'product_discount_amount' => 'required',
                    'product_outlet_price' => 'required',
                    'product_vat_rate' => 'required',
                    'action' => 'required',
                    'id' => 'required',
                ];
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ],400);
            }
            
            if($userRoleSlug == Config::get('constant.SUPER_ADMIN_SLUG'))
            {
                if(isset($request->is_published) && $request->is_published == "1")
                {
                    $productData = $this->objProducts->getProductDetailByProductId($request->id);
                    $product_image_json = json_decode($request->product_image, true);
                    if(!empty($product_image_json) && count($product_image_json) != 6)
                    {
                        $position = [];
                        foreach ($product_image_json as $key => $value) {
                            $position[] = $value['position'];
                        }
                        if(isset($productData) && count($productData)>0)
                        {
                            if(isset($productData->productImages))
                            {
                                foreach ($productData->productImages as $key => $value) {
                                    $position[] = $value->file_position;
                                }
                            }

                            $counterArray = array_unique($position);
                            if(count($counterArray) < 6)
                            {   
                                DB::rollback();
                                return response()->json([
                                    'status' => '0',
                                    'message' => trans('apimessages.product_must_have_six_images_for_publish')
                                ],400);
                            }
                        }
                    }

                    if (!Input::file('brand_label_with_original_information_image'))
                    {
                        if(isset($productData) && count($productData)>0)
                        {
                            if($productData->brand_label_with_original_information_image == "")
                            {
                                DB::rollback();
                                return response()->json([
                                    'status' => '0',
                                    'message' => trans('apimessages.product_must_have_brand_label_with_original_information_image_for_publish')
                                ],400);
                            }
                        }
                        else
                        {
                            DB::rollback();
                            return response()->json([
                                'status' => '0',
                                'message' => trans('apimessages.product_not_found')
                            ],400);
                        }
                    }

                    if (!Input::file('wash_care_with_material_image'))
                    {
                        if(isset($productData) && count($productData)>0)
                        {
                            if($productData->wash_care_with_material_image == "")
                            {
                                DB::rollback();
                                return response()->json([
                                    'status' => '0',
                                    'message' => trans('apimessages.product_must_have_wash_care_with_material_image_for_publish')
                                ],400);
                            }
                        }
                        else
                        {
                            DB::rollback();
                            return response()->json([
                                'status' => '0',
                                'message' => trans('apimessages.product_not_found')
                            ],400);
                        }
                    }
                    
                    if (!Input::file('code_image'))
                    {
                        if(isset($productData) && count($productData) > 0)
                        {
                            if($productData->code_image == "")
                            {
                                DB::rollback();
                                return response()->json([
                                    'status' => '0',
                                    'message' => trans('apimessages.product_must_have_code_image_for_publish')
                                ],400);
                            }
                        }
                        else
                        {
                            DB::rollback();
                            return response()->json([
                                'status' => '0',
                                'message' => trans('apimessages.product_not_found')
                            ],400);
                        }
                    }

                    // if(isset($productData->productColors) && count($productData->productColors)<1){
                    //     DB::rollback();
                    //     return response()->json([
                    //         'status' => '0',
                    //         'message' => trans('apimessages.product_must_have_color_for_publish')
                    //     ],400);
                    // }

                    // if(isset($productData->productMaterials) && count($productData->productMaterials)<1){
                    //     DB::rollback();
                    //     return response()->json([
                    //         'status' => '0',
                    //         'message' => trans('apimessages.product_must_have_material_for_publish')
                    //     ],400);
                    // }

                    $rules = [
                        'product_name_en' => 'required_without_all:product_name_ch,product_name_ge,product_name_fr,product_name_it,product_name_sp,product_name_ru,product_name_jp',
                        'product_name_ch' => 'required_without_all:product_name_en,product_name_ge,product_name_fr,product_name_it,product_name_sp,product_name_ru,product_name_jp',
                        'product_name_ge' => 'required_without_all:product_name_en,product_name_ch,product_name_fr,product_name_it,product_name_sp,product_name_ru,product_name_jp',
                        'product_name_fr' => 'required_without_all:product_name_en,product_name_ch,product_name_ge,product_name_it,product_name_sp,product_name_ru,product_name_jp',
                        'product_name_it' => 'required_without_all:product_name_en,product_name_ch,product_name_ge,product_name_fr,product_name_sp,product_name_ru,product_name_jp',
                        'product_name_sp' => 'required_without_all:product_name_en,product_name_ch,product_name_ge,product_name_fr,product_name_it,product_name_ru,product_name_jp',
                        'product_name_ru' => 'required_without_all:product_name_en,product_name_ch,product_name_ge,product_name_fr,product_name_it,product_name_sp,product_name_jp',
                        'product_name_jp' =>'required_without_all:product_name_en,product_name_ch,product_name_ge,product_name_fr,product_name_it,product_name_sp,product_name_ru',
                        'category_level1_id' => 'required',
                        'category_level2_id' => 'required',
                        'category_level3_id' => 'required',
                        'brand_id' => 'required',
                        'code_number' => 'required',
                        'short_description' => 'required',
                        'material_detail' => 'required',
                        'action' => 'required',
                        'product_color' => 'required',
                        'product_material' => 'required',
                        'is_published' => 'required',
                    ];

                    $validator = Validator::make($request->all(), $rules);

                    if ($validator->fails())
                    {
                        DB::rollback();
                        return response()->json([
                            'status' => '0',
                            'message' => $validator->messages()->all()[0]
                        ],400);
                    }
                }
            }

            DB::beginTransaction();

            //product insert
            $productData = [];
            $productOldData = [];
            $productOldBrandLabelImage = "";
            $productOldWashCareImage = "";
            $productOldCodeImage = "";
            $published = (isset($request->is_published) && $request->is_published > 0) ? $request->is_published : 0;

            if( (isset($request->id) && $request->id > 0) || $request->action == Config::get('constant.API_ACTION_UPDATE') || $userRoleSlug == Config::get('constant.ADMIN_SLUG'))
            {
                if($request->id != 0){
                    $productOldData = $this->objProducts->find($request->id);
                    if(isset($productOldData) && count($productOldData)>0){
                        $productData['id'] = $productOldData->id;
                        $published = ($productOldData->is_published == 1) ? $productOldData->is_published : $published;
                        $productOldBrandLabelImage = $productOldData->brand_label_with_original_information_image;
                        $productOldWashCareImage = $productOldData->wash_care_with_material_image;
                        $productOldCodeImage = $productOldData->code_image;
                        if($userRoleSlug == Config::get('constant.SUPER_ADMIN_SLUG') && $request->is_published == 1)
                        {
                            $productData['updated_by_boutique_super_admin'] = 1; 
                        }
                    }
                    else{
                        DB::rollback();
                        return response()->json([
                            'status' => '0',
                            'message' => trans('apimessages.product_not_found')
                        ],400);
                    }
                }
                else{
                    DB::rollback();
                    return response()->json([
                        'status' => '0',
                        'message' => trans('apimessages.record_id_not_specified')
                    ],400);
                }
            }                        
            $productData['created_by'] = Auth::user()->id;

            if($userRoleSlug == Config::get('constant.SUPER_ADMIN_SLUG')){
                $productData['company_id'] = $request->company_id;
                $productData['brand_id'] = $request->brand_id;
                $productData['is_published'] = $published;

                $productData['product_name_en'] = (isset($request->product_name_en)) ? $request->product_name_en : '';
                $productData['product_name_ch'] = (isset($request->product_name_ch)) ? $request->product_name_ch : '';
                $productData['product_name_ge'] = (isset($request->product_name_ge)) ? $request->product_name_ge : '';
                $productData['product_name_fr'] = (isset($request->product_name_fr)) ? $request->product_name_fr : '';
                $productData['product_name_it'] = (isset($request->product_name_it)) ? $request->product_name_it : '';
                $productData['product_name_sp'] = (isset($request->product_name_sp)) ? $request->product_name_sp : '';
                $productData['product_name_ru'] = (isset($request->product_name_ru)) ? $request->product_name_ru : '';
                $productData['product_name_jp'] = (isset($request->product_name_jp)) ? $request->product_name_jp : '';
                $productData['category_level1_id'] = (isset($request->category_level1_id)) ? $request->category_level1_id : '';
                $productData['category_level2_id'] = (isset($request->category_level2_id)) ? $request->category_level2_id : '';
                $productData['category_level3_id'] = (isset($request->category_level3_id)) ? $request->category_level3_id : '';
                $productData['category_level4_id'] = (isset($request->category_level4_id)) ? $request->category_level4_id : '';
                $productData['code_number'] = (isset($request->code_number)) ? $request->code_number : '';
                $productData['short_description'] = (isset($request->short_description)) ? $request->short_description : '';
                $productData['material_detail'] = (isset($request->material_detail)) ? $request->material_detail : '';
                

                if($request->action == Config::get('constant.API_ACTION_CREATE'))
                {
                    $product_unique_id = Helpers::generateRandomNoString(Config::get('constant.RANDOM_NO_STRING'));

                    $checkProductUniqueId = $this->objProducts->where('product_unique_id', $product_unique_id)->first();
                    if(!empty($checkProductUniqueId))
                    {
                        $product_unique_id = Helpers::generateRandomNoString(Config::get('constant.RANDOM_NO_STRING'));
                    }  
                    $productData['product_unique_id'] = $product_unique_id;
                    
                    $getCompanyProducts = $this->objProducts->where('company_id', $request->company_id)->get();
                    $getCompanyDetails = Company::find($request->company_id);
                    $company_unique_id = '';
                    if($getCompanyDetails && !empty($getCompanyDetails) && !empty($getCompanyDetails->company_unique_id))
                    {
                        $company_unique_id = $getCompanyDetails->company_unique_id;
                    }
                    
                    $fixLength = Config::get('constant.FIX_LENGTH');                    
                    if($getCompanyProducts->count() > 0)
                    {
                        $getCompanyProductsArray = $getCompanyProducts->toArray();
                        $lastProduct = end($getCompanyProductsArray);
                        $getProductNumber = (isset($lastProduct['product_number']) && !empty($lastProduct['product_number'])) ? $lastProduct['product_number'] : 0;
                        
                        $getTrimNumber = trim($getProductNumber) + 1;
                        $productNumLength = strlen($getTrimNumber);                       
                        
                        $value = $fixLength - $productNumLength;
                        $genProductNum = str_pad($getTrimNumber, $value, '0', STR_PAD_LEFT);
                    }
                    else
                    {
                        $getTrimNumber = 1;
                        $value = $fixLength - $getTrimNumber;
                        $genProductNum = str_pad($getTrimNumber, $value, '0', STR_PAD_LEFT);
                    }
                    $productData['product_number'] = (isset($getTrimNumber) && $getTrimNumber > 0) ? $getTrimNumber : NULL;
                    $productData['company_product_number'] = (isset($genProductNum)) ? $company_unique_id.'-'.$genProductNum : NULL;
                }

                if (Input::file('brand_label_with_original_information_image')) {
                    $file = Input::file('brand_label_with_original_information_image');
                    if (!empty($file)) {
                        $productData['brand_label_with_original_information_image'] = Helpers::createUpdateImage($file,$this->productBrandLabelOriginalImageUploadPath, $this->productBrandLabelThumbImageUploadPath, $this->productBrandLabelThumbImageHeight, $productData, $productOldBrandLabelImage );
                    }
                }

                if (Input::file('wash_care_with_material_image')) 
                {
                    $file = Input::file('wash_care_with_material_image');
                    if (!empty($file)) 
                    {
                        $productData['wash_care_with_material_image'] = Helpers::createUpdateImage($file,$this->productWashCareOriginalImageUploadPath, $this->productWashCareThumbImageUploadPath, $this->productWashCareThumbImageHeight, $productData, $productOldWashCareImage );
                    }
                }

                if (Input::file('code_image')) 
                {
                    $file = Input::file('code_image');
                    if (!empty($file)) 
                    {
                        $productData['code_image'] = Helpers::createUpdateImage($file,$this->productCodeOriginalImageUploadPath, $this->productCodeThumbImageUploadPath, $this->productCodeThumbImageHeight, $productData, $productOldCodeImage );
                    }
                }
            }
            if($userRoleSlug == Config::get('constant.ADMIN_SLUG') || $userRoleSlug == Config::get('constant.SUPER_ADMIN_SLUG')){

                if($userRoleSlug == Config::get('constant.ADMIN_SLUG'))
                {
                    if($productOldData->is_published != "1"){
                        DB::rollback();
                        return response()->json([
                            'status' => '0',
                            'message' => trans('apimessages.product_is_not_published')
                        ],400);
                    }
                    $productData['updated_by_boutique_admin'] = 1;
                }

                $productData['product_retail_price'] = (isset($request->product_retail_price)) ? $request->product_retail_price : '';
                $productData['product_discount_rate'] = (isset($request->product_discount_rate)) ? $request->product_discount_rate : '';
                $productData['product_discount_amount'] = (isset($request->product_discount_amount)) ? $request->product_discount_amount : '';
                $productData['product_vat_rate'] = (isset($request->product_vat_rate)) ? $request->product_vat_rate : '';
                $productData['product_vat'] = (isset($request->product_vat)) ? $request->product_vat : '';
                $productData['product_outlet_price'] = (isset($request->product_outlet_price)) ? $request->product_outlet_price : '';
                $productData['product_outlet_price_exclusive_vat'] = (isset($request->product_outlet_price_exclusive_vat)) ? $request->product_outlet_price_exclusive_vat : '';
                $productData['fashionni_fees'] = (isset($request->fashionni_fees)) ? $request->fashionni_fees : '';
                $productData['product_code_barcode'] = (isset($request->product_code_barcode)) ? $request->product_code_barcode : '';
                $productData['product_code_boutique'] = (isset($request->product_code_boutique)) ? $request->product_code_boutique : '';
                $productData['product_code_rfid'] = (isset($request->product_code_rfid)) ? $request->product_code_rfid : '';
                $productData['product_notice'] = (isset($request->product_notice)) ? $request->product_notice : '';               
            }


            $product = $this->objProducts->insertUpdate($productData);

            if(isset($product) && count($product) > 0)
            {
                if(isset($request->product_color) && $request->product_color != ""){
                    $productColorsData = explode(",",$request->product_color);
                    $productColor = $this->objProductColors->updateProductColors($product->id,$productColorsData);
                }

                if(isset($request->product_material) && $request->product_material != ""){
                    $productMaterialData = explode(",",$request->product_material);
                    $productMaterial = $this->objProductMaterials->updateProductMaterials($product->id,$productMaterialData);
                }

                if(isset($request->product_image) && $request->product_image != ""){
                    foreach (json_decode($request->product_image, true) as $key => $value)
                    {
                        $productImageData = [];
                        $oldProductImageName = "";

                        $oldProductImageData = $this->objProductImages->getProductImageByProductIdAndProductPosition($product->id,$value['position']);
                        if(isset($oldProductImageData) && count($oldProductImageData)>0){
                            $productImageData['id'] = $oldProductImageData->id;
                            $oldProductImageName = $oldProductImageData->file_name;
                        }

                        $productImageData['product_id'] = $product->id;
                        $productImageData['file_position'] = $value['position'];

                        if(isset($value['image_name']) && $value['image_name'] != ""){
                            if(Input::file($value['image_name'])){
                                $file = Input::file($value['image_name']);
                                if (!empty($file)) {
                                    $productImageData['file_name'] = Helpers::createUpdateImage($file,$this->productOriginalImageUploadPath, $this->productThumbImageUploadPath, $this->productThumbImageHeight, $oldProductImageData, $oldProductImageName );
                                }
                                $productImages = $this->objProductImages->insertUpdate($productImageData);
                            }
                        }

                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => '1',
                'message' => trans('apimessages.product_details_save_successfully'),
                'data' => $product
            ],200);

        }
        catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_getting_product_details'),
                'code' => $e->getStatusCode()
            ]);
        }
    }

    //  Delete Products Details
    public function deleteProduct(Request $request)
    {
        try 
        {
            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id, Config::get('constant.BOUTIQUE_PRODUCT_SLUG'),'edit');
            if($checkAuthorization == '0')
            {
                return response()->json([
                    'status' => 0,
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }

            $userRoles = $this->objUserRoles->getUserRolesByUserId(Auth::user()->id);
            $userRoleSlug = "";
            if(isset($userRoles) && count($userRoles)>0){
                $userRoleSlug = $userRoles[0]->roles->slug;
            }
            else{
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.user_not_found')
                ],400);
            }

            $rules = [
                'product_id' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ],400);
            }

            if($userRoleSlug == Config::get('constant.SUPER_ADMIN_SLUG')){

            }
            if($userRoleSlug == Config::get('constant.ADMIN_SLUG')){

            }

            DB::beginTransaction();

            $productData = $this->objProducts->find($request->product_id);
            if(isset($productData) && count($productData)>0){
                $productData->delete();
                DB::commit();
                return response()->json([
                    'status' => '1',
                    'message' => trans('apimessages.product_deleted_successfully')
                ],200);
            }
            else{
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.product_not_found')
                ],400);
            }

        }
        catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_getting_product_details'),
                'code' => $e->getStatusCode()
            ]);
        }
    }

    //  Get Product Inventory Page wise
    public function getProductInventory(Request $request)
    {
        try
        {
            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id, Config::get('constant.BOUTIQUE_PRODUCT_SLUG'),'view');
            if($checkAuthorization == '0')
            {
                return response()->json([
                    'status' => 0,
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }

            $rules = [
                'product_id' => 'required',
                'page_no' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ],400);
            }
            $skipRecordCount = 0;
            $nextPageCount = 0;
            $productInventoryList = [];
            $product_id = "";

            if(isset($request->product_id)){
                $product_id = $request->product_id;
            }
            $filters = [];
            $filters['product_id'] = $product_id;
            
            $getAllInventory = $this->objProductsInventory->getAll($filters);
                       
            if(isset($request->page_no) && $request->page_no>0){
                //page wise get product inventory
                $skipRecordCount = ($request->page_no-1) * $this->productInventoryRecordPerPage;

                $productInventoryList = $this->objProductsInventory->getAllProductInventory($product_id,$skipRecordCount);
            }
            else{
                //get all product inventory data
                $productInventoryList = $this->objProductsInventory->getAllProductInventoryData($product_id);
            }
            //For get unique ids
            $getProductData = $this->objProducts->getOnlyProductDetails($product_id);
            if(isset($getProductData))
            {
                $product_unique_id = (isset($getProductData['product_unique_id'])) ? $getProductData['product_unique_id'] : '';

                $company_unique_id = (isset($getProductData['company']['company_unique_id'])) ? $getProductData['company']['company_unique_id'] : '';
                $company_product_number = (isset($getProductData['company_product_number'])) ? $getProductData['company_product_number'] : '';
                $product_number = (isset($getProductData['product_number'])) ? $getProductData['product_number'] : '';
            }
            else
            {
                $product_unique_id = "";
                $company_unique_id = "";
                $company_product_number = "";
                $product_number = "";
            }
            
            $productInventoryListNextPage = $this->objProductsInventory->getAllProductInventory($product_id,($skipRecordCount+$this->productInventoryRecordPerPage));

            if(count($productInventoryListNextPage)>0){
                $nextPageCount = 1;
            }

            $productsInventoryData = [];
            if(isset($productInventoryList) && count($productInventoryList)>0)
            {
                if(isset($getAllInventory) && !empty($getAllInventory))
                {        
                    $arrayData = $getAllInventory->toArray();
                    $array = end($arrayData);  
                    if(isset($array['fashionni_id']) && $array['fashionni_id'] != '')
                    {
                        $exp = explode('-', $array['fashionni_id']);
                        $last_number = (!empty($exp) && isset($exp[1])) ? $exp[1] : '';
                    }
                } 
                foreach ($productInventoryList as $key => $value)
                {
                    $data = [];
                    $data['id'] = $value['id'];

                    $data['fashionni_id'] = (isset($value['fashionni_id'])) ? $value['fashionni_id'] : '';

                    $data['product_standard'] = (isset($value['product_standard'])) ? $value['product_standard'] : '';

                    $data['product_size'] = (isset($value['product_size'])) ? $value['product_size'] : '';

                    $data['product_quantity'] = (isset($value['product_quantity'])) ? $value['product_quantity'] : '';

                    $data['product_warehouse'] = (isset($value['product_warehouse'])) ? $value['product_warehouse'] : '';

                    $data['product_quantity'] = (isset($value['product_quantity'])) ? $value['product_quantity'] : '';

                    // $product_unique_id = (isset($value['product']['product_unique_id'])) ? $value['product']['product_unique_id'] : '';

                    // $company_unique_id = (isset($value['product']['company']['company_unique_id'])) ? $value['product']['company']['company_unique_id'] : '';

                    $data['sold_by'] = (isset($value['sold_by'])) ? $value['sold_by'] : '';
                    $productsInventoryData[] = $data;
                }
            }
            else
            {
                return response()->json([
                    'status' => '1',
                    'message' => trans('apimessages.products_inventory_not_found'),
                    'next' => $nextPageCount,
                    'product_unique_id' => $product_unique_id,
                    'company_unique_id' => $company_unique_id,
                    'company_product_number' => $company_product_number,
                    'product_number' => $product_number,
                    'last_generated_number' => (isset($last_number)) ? $last_number : 0,
                    'data' => []
                ],200);
            }
            return response()->json([
                'status' => '1',
                'message' => trans('apimessages.products_inventory_list'),
                'next' => $nextPageCount,
                'product_unique_id' => $product_unique_id,
                'company_unique_id' => $company_unique_id,
                'company_product_number' => $company_product_number,
                'product_number' => $product_number,
                'last_generated_number' => (isset($last_number)) ? $last_number : 0,
                'data' => $productsInventoryData
            ],200);
        }catch (Exception $e) {
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_getting_product_inventory_details'),
                'code' => $e->getStatusCode()
            ]);
        }
    }

    //  Save Product Inventory
    public function saveProductInventory(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        $outputArray = [];
        try
        {
            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id, Config::get('constant.BOUTIQUE_PRODUCT_SLUG'),'edit');
            if($checkAuthorization == '0')
            {
                return response()->json([
                    'status' => 0,
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }
            DB::beginTransaction();
            $rules = [
                'product_inventory' => 'required',
                'product_id' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails())
            {
                DB::rollback();
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }

            //save Inventory
            if(isset($request->product_inventory) && $request->product_inventory != "")
            {
                $productInventoryJson = json_decode($request->product_inventory, true);
                if(!empty($productInventoryJson))
                {
                    foreach ($productInventoryJson as $key => $value)
                    {
                        $saveInventoryData = [];
                        $data = $value;
                        //update inventory
                        if(isset($data['id']) && $data['id'] != 0 && $data['id'] > 0)
                        {
                            $inventoryOldData = $this->objProductsInventory->find($data['id']);
                                if(isset($inventoryOldData) && count($inventoryOldData)>0)
                                {
                                    $saveInventoryData['id'] = $inventoryOldData->id;
                                }
                                else
                                {
                                    DB::rollback();
                                    return response()->json([
                                        'status' => '0',
                                        'message' => trans('apimessages.products_inventory_not_found')
                                    ],400);
                                }
                        }

                        $saveInventoryData['product_id'] = $request->product_id;
                        $saveInventoryData['fashionni_id'] = !empty($data['fashionni_id']) ? $data['fashionni_id'] : NULL;
                        $saveInventoryData['product_standard'] = !empty($data['product_standard']) ? $data['product_standard'] : NULL;
                        $saveInventoryData['product_size'] = !empty($data['product_size']) ? $data['product_size'] : NULL;
                        $saveInventoryData['product_warehouse'] = !empty($data['product_warehouse']) ? $data['product_warehouse'] : NULL;

                        $product_inventory_unique_id = mt_rand();
                        $getInventory= $this->objProductsInventory->where('product_inventory_unique_id', $product_inventory_unique_id)->first();

                        if($getInventory && !empty($getInventory))
                        {
                            $product_inventory_unique_id = mt_rand();
                        }
                        $saveInventoryData['product_inventory_unique_id'] = $product_inventory_unique_id;
                        $saveInventoryData['sold_by'] = (isset($data['sold_by']) && $data['sold_by'] != "") ? $data['sold_by'] : '0';
                        if($saveInventoryData['sold_by'] == 0)
                        {
                            $saveInventoryData['product_quantity'] = "1";
                        }else{
                            $saveInventoryData['product_quantity'] = "0";
                        }

                        $saveInventory = $this->objProductsInventory->insertUpdate($saveInventoryData);

                        if($saveInventory)
                        {
                            DB::commit();
                            $outputArray['status'] = 1;
                            $outputArray['message'] =  trans('apimessages.products_inventory_saved_successfully');
                            $statusCode = 200;
                        }
                        else
                        {
                            DB::rollback();
                            $outputArray['status'] = 0;
                            $outputArray['message'] = trans('apimessages.default_error_msg');
                            $statusCode = 200;
                        }
                    }
                }
            }
            else
            {
                DB::rollback();
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.default_error_msg');
                $statusCode = 200;
            }
        }catch (Exception $e) {
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_getting_product_inventory_details'),
                'code' => $e->getStatusCode()
            ]);
        }
        return response()->json($outputArray, $statusCode);
    }

    //  Delete Product Inventory
    public function deleteProductInventory(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        $outputArray = [];
        try
        {
            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id, Config::get('constant.BOUTIQUE_PRODUCT_SLUG'),'edit');
            if($checkAuthorization == '0')
            {
                return response()->json([
                    'status' => 0,
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }
            DB::beginTransaction();
            $rules = [
                'id' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails())
            {
                DB::rollback();
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }

            $productInventory = ProductInventory::find($request->id);
            if(isset($productInventory) && !empty($productInventory))
            {
                $deleteInventory = $productInventory->delete();

                if($deleteInventory)
                {
                    DB::commit();
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.products_inventory_deleted_successfully');
                    $statusCode = 200;
                }
                else
                {
                    DB::rollback();
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.default_error_msg');
                    $statusCode = 200;
                }
            }
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] =  trans('apimessages.products_inventory_not_found');
                $statusCode = 200;
            }
        }catch (Exception $e) {
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_delete_product_inventory_details'),
                'code' => $e->getStatusCode()
            ]);
        }
        return response()->json($outputArray, $statusCode);
    }
    
    //  Delete Product Inventory
    public function deleteProductCodeImage(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        $outputArray = [];
        try
        {
            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id, Config::get('constant.BOUTIQUE_PRODUCT_SLUG'),'edit');
            if($checkAuthorization == '0')
            {
                return response()->json([
                    'status' => 0,
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }
            $rules = [
                'id' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails())
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }

            $productDetails = Products::find($request->id);
            
            if(isset($productDetails) && !empty($productDetails) && !empty($productDetails->code_image))
            {
                $oldImgName = $productDetails->code_image;
                Helpers::deleteFileToStorage($oldImgName, $this->productCodeOriginalImageUploadPath);
                Helpers::deleteFileToStorage($oldImgName, $this->productCodeThumbImageUploadPath);
                $productDetails->code_image = NULL;
                $response = $productDetails->save();
                if($response)
                {
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.products_inventory_deleted_successfully');
                    $statusCode = 200;
                }
                else
                {
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.default_error_msg');
                    $statusCode = 200;
                }
            }
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] =  trans('apimessages.products_inventory_not_found');
                $statusCode = 200;
            }
        }catch (Exception $e) {
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_delete_product_inventory_details'),
                'code' => $e->getStatusCode()
            ]);
        }
        return response()->json($outputArray, $statusCode);
    }
    
    public function saveAudio(Request $request) 
    {
        $requestData = $request->all();
        $outputArray = [];
        echo '<pre/>';
        print_r($requestData);
        die('dsf');
    }
}
