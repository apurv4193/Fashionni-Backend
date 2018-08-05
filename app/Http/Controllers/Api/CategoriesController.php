<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use DB;
use Auth;
use Config;
use App\User;
use App\UserRoles;
use App\Company;
use App\CompanyUser;
use App\Categories;
use App\CategoryImages;
use Validator;
use \stdClass;
use Helpers;
use Storage;
use Input;
use File;
use Image;
use JWTAuth;
use JWTAuthException;
use App\Http\Resources\CategoriesResource;

class CategoriesController extends Controller
{
    public function __construct()
    {
        $this->objUser = new User();
        $this->objUserRoles = new UserRoles();
        $this->objCompany = new Company();
        $this->objCompanyUser = new CompanyUser();
        $this->objCategories = new Categories();
        $this->objCategoryImages = new CategoryImages();

        $this->categoryOriginalImageUploadPath = Config::get('constant.CATEGORY_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->categoryThumbImageUploadPath = Config::get('constant.CATEGORY_THUMB_IMAGE_UPLOAD_PATH');
        $this->categoryThumbImageHeight = Config::get('constant.CATEGORY_THUMB_IMAGE_HEIGHT');
        $this->categoryThumbImageWidth = Config::get('constant.CATEGORY_THUMB_IMAGE_WIDTH');

        $this->defaultImage = Config::get('constant.DEFAULT_IMAGE_PATH');
    }

    /**
     * Save Category
     */
    public function saveCategory(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $userRoleSlug = "";
        $userRoles = $this->objUserRoles->getUserRolesByUserId(Auth::user()->id);
        if(isset($userRoles) && !empty($userRoles))
        {
            $userRoleSlug = $userRoles[0]->roles->slug;
        }
        if($userRoleSlug != Config::get('constant.SUPER_ADMIN_SLUG'))
        {
            return response()->json([
                'status' => 0,
                'message' => trans('apimessages.unauthorized_access')
            ], 400);
        }
        $requestData = $request->all();
        
        $outputArray = [];
        try
        {
            $saveCategoryData = [];
            DB::beginTransaction();
            $rules = [
                'category_images.*' => 'mimes:jpeg,jpg,bmp,png,gif|max:52400',
                'is_parent' => 'required'
            ];
            if(isset($request->action) && $request->action == Config::get('constant.API_ACTION_CREATE'))
            {
                $rules = [
                    'category_name_en' => 'required',
                    'category_name_ch' => 'required',
                    'category_name_ge' => 'required',
                    'category_name_fr' => 'required',
                    'category_name_it' => 'required',
                    'category_name_sp' => 'required',
                    'category_name_ru' => 'required',
                    'category_name_jp' => 'required',
                ];

                $category_unique_id = mt_rand();
                $getCategory= Categories::where('category_unique_id', $category_unique_id)->first();
                if($getCategory && !empty($getCategory))
                {
                    $category_unique_id = mt_rand();
                }
                $saveCategoryData['category_unique_id'] = $category_unique_id;
                
                if(isset($request->is_parent) && isset($request->category_level) && $request->category_level > 0)
                {
                    if($request->is_parent > 0 && $request->category_level > 1)
                    {
                        if($request->category_level == 2 || $request->category_level == 3 || $request->category_level == 4)
                        {
                            $getCategoryCount = Categories::where('is_parent', $request->is_parent)->where('category_level', $request->category_level)->where('category_name_en', $request->category_name_en)->count();
                        }
                        else
                        {
                            DB::rollback();
                            $outputArray['status'] = 0;
                            $outputArray['message'] = trans('apimessages.category_level_wrong');
                            $statusCode = 200;
                            return response()->json($outputArray, $statusCode);
                        }
                    }
                    elseif($request->is_parent == 0 && $request->category_level == 1)
                    {
                        $getCategoryCount = Categories::where('is_parent', $request->is_parent)
                                ->where('category_level', $request->category_level)
                                ->where(function($query) use($request) {
                                    $query->where('category_name_en', $request->category_name_en)
                                        ->orWhere('category_name_ch', $request->category_name_ch)
                                        ->orWhere('category_name_ge', $request->category_name_ge)
                                        ->orWhere('category_name_fr', $request->category_name_fr)
                                        ->orWhere('category_name_it', $request->category_name_it)
                                        ->orWhere('category_name_sp', $request->category_name_sp)
                                        ->orWhere('category_name_ru', $request->category_name_ru)
                                        ->orWhere('category_name_jp', $request->category_name_jp);
                                })
                                ->count();

                    }
                    else
                    {
                        DB::rollback();
                        $outputArray['status'] = 0;
                        $outputArray['message'] = trans('apimessages.category_level_wrong');
                        $statusCode = 200;
                        return response()->json($outputArray, $statusCode);
                    }                
                    if(isset($getCategoryCount) && $getCategoryCount > 0)
                    {
                        DB::rollback();
                        $outputArray['status'] = 0;
                        $outputArray['message'] = trans('apimessages.category_already_exist');
                        $statusCode = 200;
                        return response()->json($outputArray, $statusCode);
                    }
                }
                else
                {
                    DB::rollback();
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.category_level_wrong');
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode);
                }
            }
            elseif(isset($request->action) && $request->action == Config::get('constant.API_ACTION_UPDATE'))
            {
                $rules['id'] = 'required';
                $rules['category_name_en'] = 'required';
                $rules['category_name_ch'] = 'required';
                $rules['category_name_ge'] = 'required';
                $rules['category_name_fr'] = 'required';
                $rules['category_name_it'] = 'required';
                $rules['category_name_sp'] = 'required';
                $rules['category_name_ru'] = 'required';
                $rules['category_name_jp'] = 'required';
                
                if(isset($request->is_parent) && isset($request->category_level) && $request->category_level > 0)
                {
                    if($request->is_parent > 0 && $request->category_level > 1)
                    {
                        if($request->category_level == 2 || $request->category_level == 3 || $request->category_level == 4)
                        {
//                            $getCategoryCount = Categories::where('id', '!=', $request->id)->where('is_parent', $request->is_parent)->where('category_level', $request->category_level)->where('category_name_en', $request->category_name_en)->count();
                            
                            $getCategoryCount = Categories::where('id', '!=', $request->id)
                                    ->where('is_parent', $request->is_parent)
                                    ->where('category_level', $request->category_level)
                                    ->where(function($query) use($request) {
                                        $query->where('category_name_en', $request->category_name_en)
                                            ->orWhere('category_name_ch', $request->category_name_ch)
                                            ->orWhere('category_name_ge', $request->category_name_ge)
                                            ->orWhere('category_name_fr', $request->category_name_fr)
                                            ->orWhere('category_name_it', $request->category_name_it)
                                            ->orWhere('category_name_sp', $request->category_name_sp)
                                            ->orWhere('category_name_ru', $request->category_name_ru)
                                            ->orWhere('category_name_jp', $request->category_name_jp);
                                    })
                                    ->count();
                           
                        }
                        else
                        {
                            DB::rollback();
                            $outputArray['status'] = 0;
                            $outputArray['message'] = trans('apimessages.category_level_wrong');
                            $statusCode = 200;
                            return response()->json($outputArray, $statusCode);
                        }
                    }
                    elseif($request->is_parent == 0 && $request->category_level == 1)
                    {
                        $getCategoryCount = Categories::where('id', '!=', $request->id)
                            ->where('is_parent', $request->is_parent)
                            ->where('category_level', $request->category_level)                           
                            ->where(function($query) use($request) {
                                $query->where('category_name_en', $request->category_name_en)
                                    ->orWhere('category_name_ch', $request->category_name_ch)
                                    ->orWhere('category_name_ge', $request->category_name_ge)
                                    ->orWhere('category_name_fr', $request->category_name_fr)
                                    ->orWhere('category_name_it', $request->category_name_it)
                                    ->orWhere('category_name_sp', $request->category_name_sp)
                                    ->orWhere('category_name_ru', $request->category_name_ru)
                                    ->orWhere('category_name_jp', $request->category_name_jp);
                            })
                            ->count();
                    }
                    else
                    {
                        DB::rollback();
                        $outputArray['status'] = 0;
                        $outputArray['message'] = trans('apimessages.category_level_wrong');
                        $statusCode = 200;
                        return response()->json($outputArray, $statusCode);
                    }                
                    if(isset($getCategoryCount) && $getCategoryCount > 0)
                    {
                        DB::rollback();
                        $outputArray['status'] = 0;
                        $outputArray['message'] = trans('apimessages.category_already_exist');
                        $statusCode = 200;
                        return response()->json($outputArray, $statusCode);
                    }
                }
                else
                {
                    DB::rollback();
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.category_level_wrong');
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode);
                }
            }
            
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails())
            {
                DB::rollback();
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
            // saveCategory
            
            if(isset($request->action) && $request->action == Config::get('constant.API_ACTION_UPDATE'))
            {
                if($request->id > 0)
                {
                    $getCategoryData = $this->objCategories->find($request->id);
                    if($getCategoryData && !empty($getCategoryData))
                    {
                        $saveCategoryData['id'] = $getCategoryData->id;
                    }
                    else
                    {
                        DB::rollback();
                        $outputArray['status'] = 0;
                        $outputArray['message'] = trans('apimessages.category_not_found');
                        $statusCode = 200;
                        return response()->json($outputArray, $statusCode);
                    }
                }
                else
                {
                    DB::rollback();
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.category_not_found');
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode);
                }
            }
            $saveCategoryData['is_parent'] = $request->is_parent;
            if(isset($request->category_level))
            {
                $saveCategoryData['category_level'] = $request->category_level;
            }
            $saveCategoryData['category_name_en'] = !empty($request->category_name_en) ? $request->category_name_en : NULL;
            $saveCategoryData['category_name_ch'] = !empty($request->category_name_ch) ? $request->category_name_ch : NULL;
            $saveCategoryData['category_name_ge'] = !empty($request->category_name_ge) ? $request->category_name_ge : NULL;
            $saveCategoryData['category_name_fr'] = !empty($request->category_name_fr) ? $request->category_name_fr : NULL;
            $saveCategoryData['category_name_it'] = !empty($request->category_name_it) ? $request->category_name_it : NULL;
            $saveCategoryData['category_name_sp'] = !empty($request->category_name_sp) ? $request->category_name_sp : NULL;
            $saveCategoryData['category_name_ru'] = !empty($request->category_name_ru) ? $request->category_name_ru : NULL;
            $saveCategoryData['category_name_jp'] = !empty($request->category_name_jp) ? $request->category_name_jp : NULL;
            
            $saveCategory = $this->objCategories->insertUpdate($saveCategoryData);
            if($saveCategory)
            {
                $category_id = $saveCategory->id;
                if (Input::file('category_images'))
                {
                    $fileNameArray = Input::file('category_images');
                    if (isset($fileNameArray) && count($fileNameArray) > 0 && !empty($fileNameArray))
                    {
                        $getOldCategoryImages = CategoryImages::where('category_id', $category_id)->get();
                        
                        if($getOldCategoryImages && !empty($getOldCategoryImages) && $getOldCategoryImages->count() > 0)
                        {
                            foreach($getOldCategoryImages as $oldFileNameKey => $oldFileNameValue)
                            {
                                $oldImageFileName = $oldFileNameValue->file_name;
                                $deleteOldFileImage = $oldFileNameValue->delete();
                                if($deleteOldFileImage)
                                {
                                    Helpers::deleteFileToStorage($oldImageFileName, $this->categoryOriginalImageUploadPath);
                                    Helpers::deleteFileToStorage($oldImageFileName, $this->categoryThumbImageUploadPath);
                                }
                            }
                        }                        
                        foreach($fileNameArray as $fileNameKey => $fileNameValue)
                        {
                            $categoryOldImage = '';
                            $categoryImageData = [];
                            $categoryImageData['category_id'] = $category_id;
                            $categoryImageData['file_name'] = Helpers::createUpdateImage($fileNameValue, $this->categoryOriginalImageUploadPath, $this->categoryThumbImageUploadPath, $this->categoryThumbImageHeight, $categoryImageData, $categoryOldImage);
                            if($categoryImageData['file_name'] && !empty($categoryImageData['file_name']))
                            {
                                $saveCategoryImage = CategoryImages::firstOrCreate($categoryImageData);
                            }
                        }
                    }
                }
                DB::commit();
                $outputArray['status'] = 1;
                $outputArray['message'] =  trans('apimessages.category_saved_successfully');
                $outputArray['data'] = $saveCategory;
                $statusCode = 200;
            }
            else
            {
                DB::rollback();
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.default_error_msg');
                $statusCode = 200;
            }
        } catch (Exception $e) {
            DB::rollback();
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }


    /**
     * Add Category Image By Id
     */
    public function addCategoryImage(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $userRoleSlug = "";
        $userRoles = $this->objUserRoles->getUserRolesByUserId(Auth::user()->id);
        if(isset($userRoles) && !empty($userRoles))
        {
            $userRoleSlug = $userRoles[0]->roles->slug;
        }
        if($userRoleSlug != Config::get('constant.SUPER_ADMIN_SLUG'))
        {
            return response()->json([
                'status' => 0,
                'message' => trans('apimessages.unauthorized_access')
            ], 400);
        }
        $requestData = $request->all();
        $outputArray = [];
        try
        {
            DB::beginTransaction();
            if(isset($request->id) && $request->id > 0)
            {
                $rules = [
                    'id' => 'required'
                ];
            }
            $rules = [
                'file_name' => 'required|mimes:jpeg,jpg,bmp,png,gif|max:52400',
                'category_id' => 'required'
            ];
            $validator = Validator::make($requestData, $rules);
            if ($validator->fails())
            {
                DB::rollback();
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
            $getCategoriesData = Categories::find($request->category_id);
            if(empty($getCategoriesData) || $getCategoriesData == '' || $getCategoriesData == NULL)
            {
                DB::rollback();
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.category_not_found');
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
            if(Input::file('file_name'))
            {
                $file = Input::file('file_name');
                $categoryOldImage = '';
                if (!empty($file))
                {
                    $categoryImageData = [];
                    $categoryImageData['category_id'] = $request->category_id;
                    if(isset($request->id) && $request->id > 0)
                    {
                        $getOldCategoryImage = CategoryImages::find($request->id);
                        if($getOldCategoryImage && !empty($getOldCategoryImage))
                        {
                            $oldImageFileName = $getOldCategoryImage->file_name;
                            $deleteOldFileImage = $getOldCategoryImage->delete();
                            if($deleteOldFileImage)
                            {
                                Helpers::deleteFileToStorage($oldImageFileName, $this->categoryOriginalImageUploadPath);
                                Helpers::deleteFileToStorage($oldImageFileName, $this->categoryThumbImageUploadPath);
                            }
                        }
                    }                    
                    $categoryImageData['file_name'] = Helpers::createUpdateImage($file, $this->categoryOriginalImageUploadPath, $this->categoryThumbImageUploadPath, $this->categoryThumbImageHeight, $categoryImageData, $categoryOldImage);
                    $response = CategoryImages::firstOrCreate($categoryImageData);
                    if(isset($response) && $response)
                    {
                        DB::commit();
                        $outputArray['status'] = 1;
                        $outputArray['message'] = trans('apimessages.category_image_added_successfully');
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
                    $outputArray['message'] = trans('apimessages.category_image_not_found');
                    $statusCode = 200;
                }
            }
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.category_image_not_found');
                $statusCode = 200;
            }
        } catch (Exception $e) {
            DB::rollback();
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }

    /**
     * Delete Category Image By Id
     */
    public function deleteCategoryImage(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $userRoleSlug = "";
        $userRoles = $this->objUserRoles->getUserRolesByUserId(Auth::user()->id);
        if(isset($userRoles) && !empty($userRoles))
        {
            $userRoleSlug = $userRoles[0]->roles->slug;
        }
        if($userRoleSlug != Config::get('constant.SUPER_ADMIN_SLUG'))
        {
            return response()->json([
                'status' => 0,
                'message' => trans('apimessages.unauthorized_access')
            ], 400);
        }
        $requestData = $request->all();
        $outputArray = [];
        try
        {
            DB::beginTransaction();
            $rules = [
                'id' => 'required',
            ];
            $validator = Validator::make($requestData, $rules);
            if ($validator->fails())
            {
                DB::rollback();
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }

            $categoryImage = CategoryImages::find($request->id);
            if($categoryImage && !empty($categoryImage) && !empty($categoryImage->file_name))
            {
                $response = $categoryImage->delete();
                if($response)
                {
                    $oldImgName = $categoryImage->file_name;
                    Helpers::deleteFileToStorage($oldImgName, $this->categoryOriginalImageUploadPath);
                    Helpers::deleteFileToStorage($oldImgName, $this->categoryThumbImageUploadPath);
                    DB::commit();
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.category_image_deleted_successfully');
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
                $outputArray['message'] =  trans('apimessages.category_image_not_found');
                $statusCode = 200;
            }
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }

    /**
     * Delete Category By Id
     */
    public function deleteCategory(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $userRoleSlug = "";
        $userRoles = $this->objUserRoles->getUserRolesByUserId(Auth::user()->id);
        if(isset($userRoles) && !empty($userRoles))
        {
            $userRoleSlug = $userRoles[0]->roles->slug;
        }
        if($userRoleSlug != Config::get('constant.SUPER_ADMIN_SLUG'))
        {
            return response()->json([
                'status' => 0,
                'message' => trans('apimessages.unauthorized_access')
            ], 400);
        }
        $requestData = $request->all();
        $outputArray = [];
        try
        {
            DB::beginTransaction();
            $rules = [
                'id' => 'required',
            ];
            $validator = Validator::make($requestData, $rules);
            if ($validator->fails())
            {
                DB::rollback();
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
            $category = Categories::find($request->id);
            if($category && !empty($category))
            {
                if(isset($category->category_images) && !empty($category->category_images) && $category->category_images->count() > 0)
                {
                    foreach ($category->category_images as $imgKey => $_imgValue)
                    {
                        $categoryImage = CategoryImages::find($_imgValue->id);
                        $deleteCatImage = $categoryImage->delete();
                        $oldImgName = $_imgValue->file_name;
//                        Helpers::deleteFileToStorage($oldImgName, $this->categoryOriginalImageUploadPath);
//                        Helpers::deleteFileToStorage($oldImgName, $this->categoryThumbImageUploadPath);
                    }
                }
                $response = $category->delete();
                $nLevelCategoryRemove = $this->nLevelCategoryDeleted($category->id);
                if($response)
                {
                    DB::commit();
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.category_deleted_successfully');
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
                $outputArray['message'] =  trans('apimessages.category_not_found');
                $statusCode = 200;
            }
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }
        
    /**
     * Delete nLevel Category
     */
    public function nLevelCategoryDeleted($catId) 
    {
        $response = true;
        $subCategories = Categories::where('is_parent', $catId)->get();
        if($subCategories && !empty($subCategories) && $subCategories->count() > 0) 
        {   
            foreach ($subCategories as $catKey => $catvalue)
            {
                if(isset($catvalue->category_images) && !empty($catvalue->category_images) && $catvalue->category_images->count() > 0)
                {
                    foreach ($catvalue->category_images as $imgKey => $_imgValue)
                    {
                        $categoryImage = CategoryImages::find($_imgValue->id);
                        $deleteCatImage = $categoryImage->delete();
                        $oldImgName = $_imgValue->file_name;
//                        Helpers::deleteFileToStorage($oldImgName, $this->categoryOriginalImageUploadPath);
//                        Helpers::deleteFileToStorage($oldImgName, $this->categoryThumbImageUploadPath);
                    }
                }
                $response = $catvalue->delete();
                $this->nLevelCategoryDeleted($catvalue->id);  
            }            
        }
        return $response;
    }

    /**
     * Get Category Details By Id
     */
    public function getCategoryDetails(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id, Config::get('constant.BOUTIQUE_PRODUCT_SLUG'), 'view');
        if($checkAuthorization == '0')
        {
            return response()->json([
                'status' => 0,
                'message' => trans('apimessages.unauthorized_access')
            ], 400);
        }
        $requestData = $request->all();
        $outputArray = [];
        try
        {
            $rules = [
                'id' => 'required',
            ];
            $validator = Validator::make($requestData, $rules);
            if ($validator->fails())
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
            $getCategoryDetails = $this->objCategories->getCategoryDetails($request->id);

            if($getCategoryDetails && !empty($getCategoryDetails))
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.category_details_fetched_successfully');
                $outputArray['data'] = array();
                $statusCode = 200;

                if($getCategoryDetails->child_categroies && !empty($getCategoryDetails->child_categroies) && $getCategoryDetails->child_categroies->count() > 0)
                {
                    $getCategoryDetails->child_categroies_count = $getCategoryDetails->child_categroies->count();

                    foreach ($getCategoryDetails->child_categroies as $childKey => $_childValue)
                    {
                        if($_childValue->category_images && !empty($_childValue->category_images) && $_childValue->category_images->count() > 0)
                        {
                            foreach ($_childValue->category_images as $childImgKey => $_childImgValue)
                            {
                               $_childImgValue->file_name = (!empty($_childImgValue->file_name) && $_childImgValue->file_name != '' && Storage::exists($this->categoryOriginalImageUploadPath.$_childImgValue->file_name) && Storage::size($this->categoryOriginalImageUploadPath.$_childImgValue->file_name) > 0) ? Storage::url($this->categoryOriginalImageUploadPath.$_childImgValue->file_name) : url($this->defaultImage);
                            }
                        }
                    }
                }
                if($getCategoryDetails->category_images && !empty($getCategoryDetails->category_images) && $getCategoryDetails->category_images->count() > 0)
                {
                    foreach ($getCategoryDetails->category_images as $imgKey => $_imgValue)
                    {
                        $_imgValue->file_name = (!empty($_imgValue->file_name) && $_imgValue->file_name != '' && Storage::exists($this->categoryOriginalImageUploadPath.$_imgValue->file_name) && Storage::size($this->categoryOriginalImageUploadPath.$_imgValue->file_name) > 0) ? Storage::url($this->categoryOriginalImageUploadPath.$_imgValue->file_name) : url($this->defaultImage);
                    }
                }
                $outputArray['data'] = $getCategoryDetails;
            }
            else
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.category_not_found');
                $statusCode = 200;
                $outputArray['data'] = new \stdClass();
            }
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }

    /**
     * Get Categories Lists By Level vice
     */
    public function getMainCategories(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id, Config::get('constant.BOUTIQUE_PRODUCT_SLUG'), 'view');
        if($checkAuthorization == '0')
        {
            return response()->json([
                'status' => 0,
                'message' => trans('apimessages.unauthorized_access')
            ], 400);
        }
        $requestData = $request->all();
        $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 0;
        $levelNo = (isset($request->level) && !empty($request->level)) ? $request->level : 0;
        $outputArray = [];
        try
        {
            $filters = [];
//          $filters['level'] = $levelNo;
            $filters['is_parent'] = '0';
            $paginate = (!empty($pageNo) && $pageNo > 0) ? true : false;
            $getCategories = $this->objCategories->getAll($filters, $paginate);
            if($getCategories && !empty($getCategories) && $getCategories->count() > 0)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.categories_listing_fetched_successfully');
                $outputArray['data'] = array();
                $statusCode = 200;
                foreach ($getCategories as $catKey => $catValue)
                {
                    if($catValue->category_images && !empty($catValue->category_images) && $catValue->category_images->count() > 0)
                    {
                        foreach ($catValue->category_images as $imgKey => $_imgValue)
                        {
                            $_imgValue->file_name = (!empty($_imgValue->file_name) && $_imgValue->file_name != '' && Storage::exists($this->categoryOriginalImageUploadPath.$_imgValue->file_name) && Storage::size($this->categoryOriginalImageUploadPath.$_imgValue->file_name) > 0) ? Storage::url($this->categoryOriginalImageUploadPath.$_imgValue->file_name) : url($this->defaultImage);
                        }
                    }
                }
                $outputArray['data'] = $getCategories;
            }
            else
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.categories_not_found');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }

    /**
     * Get Categories Lists By Level vice
     */
    public function categories(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id, Config::get('constant.BOUTIQUE_PRODUCT_SLUG'), 'view');
        if($checkAuthorization == '0')
        {
            return response()->json([
                'status' => 0,
                'message' => trans('apimessages.unauthorized_access')
            ], 400);
        }
        $requestData = $request->all();
        $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 0;
        $levelNo = (isset($request->level) && !empty($request->level)) ? $request->level : 0;
        $outputArray = [];
        try
        {
            $filters = [];
            $filters['level'] = $levelNo;
            $paginate = (!empty($pageNo) && $pageNo > 0) ? true : false;
            $getCategories = $this->objCategories->getAll($filters, $paginate);
            if($getCategories && !empty($getCategories) && $getCategories->count() > 0)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.categories_listing_fetched_successfully');
                $outputArray['data'] = array();
                $outputArray['data']['category_level_0'] = array();
                $outputArray['data']['category_level_1'] = array();
                $outputArray['data']['category_level_2'] = array();
                $outputArray['data']['category_level_3'] = array();
                $outputArray['data']['category_level_4'] = array();
                $statusCode = 200;

                foreach ($getCategories as $catKey => $catValue)
                {
                    if($catValue->category_images && !empty($catValue->category_images) && $catValue->category_images->count() > 0)
                    {
                        foreach ($catValue->category_images as $imgKey => $_imgValue)
                        {
                            $file_name = (!empty($_imgValue->file_name) && $_imgValue->file_name != '' && Storage::exists($this->categoryOriginalImageUploadPath.$_imgValue->file_name) && Storage::size($this->categoryOriginalImageUploadPath.$_imgValue->file_name) > 0) ? Storage::url($this->categoryOriginalImageUploadPath.$_imgValue->file_name) : url($this->defaultImage);
                            $catValue->category_images[$imgKey]->file_name = $file_name;
                        }
                    }
                    if(!empty($catValue->category_level) && $catValue->category_level == 1)
                    {
                        $outputArray['data']['category_level_1'][] = $catValue;
                    }
                    elseif(!empty($catValue->category_level) && $catValue->category_level == 2)
                    {
                        $outputArray['data']['category_level_2'][] = $catValue;
                    }
                    elseif(!empty($catValue->category_level) && $catValue->category_level == 3)
                    {
                        $outputArray['data']['category_level_3'][] = $catValue;
                    }
                    elseif(!empty($catValue->category_level) && $catValue->category_level == 4)
                    {
                        $outputArray['data']['category_level_4'][] = $catValue;
                    }
                    else
                    {
                        $outputArray['message'] = trans('apimessages.all_categories_found');
                        $outputArray['data']['category_level_0'][] = $catValue;
                    }
                }
                
//                $outputArray = CategoriesResource::collection($getCategories);
//                $outputArray->additional = $dataArray;
//                return $outputArray;
            }
            else
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.categories_not_found');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }
}
