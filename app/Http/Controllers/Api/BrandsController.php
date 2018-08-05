<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use DB;
use Auth;
use Config;
use Validator;
use \stdClass;
use Helpers;
use Storage;
use Input;
use File;
use Image;
use App\User;
use App\UserRoles;
use App\Company;
use App\Products;
use App\Notifications;
use App\Brands;
use App\Http\Resources\BrandsResource;
use JWTAuth;
use JWTAuthException;


class BrandsController extends Controller
{    
    public function __construct()
    {
        $this->objUser = new User();
        $this->objCompany = new Company();
        $this->objUserRoles = new UserRoles();
        $this->objProducts = new Products();
        $this->objCompanyUser = new Brands();
        $this->objNotifications = new Notifications();

        $this->companyOriginalImageUploadPath = Config::get('constant.COMPANY_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->companyThumbImageUploadPath = Config::get('constant.COMPANY_THUMB_IMAGE_UPLOAD_PATH');
        
        $this->brandOriginalImageUploadPath = Config::get('constant.BRANDS_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->brandThumbImageUploadPath = Config::get('constant.BRANDS_THUMB_IMAGE_UPLOAD_PATH');
        $this->brandThumbImageHeight = Config::get('constant.BRANDS_THUMB_IMAGE_HEIGHT');
        $this->brandThumbImageWidth = Config::get('constant.BRANDS_THUMB_IMAGE_WIDTH');
        
        $this->defaultImage = Config::get('constant.DEFAULT_IMAGE_PATH');
        $this->defaultPlusImage = Config::get('constant.DEFAULT_PLUS_IMAGE_PATH');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        try 
        {
            $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 0;
            if(!empty($pageNo) && $pageNo > 0)
            {
                $brandsData = Brands::paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
            }
            else
            {
                $brandsData = Brands::get();
            }            
            if($brandsData && !empty($brandsData) && $brandsData->count() > 0)
            {
                $dataArray['status'] = 1;
                $dataArray['message'] = trans('apimessages.brands_listing_fetched_successfully');
                $outputArray = BrandsResource::collection($brandsData);        
                $outputArray->additional = $dataArray;
                return $outputArray;
            }
            else
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.brands_listing_not_found');
                $statusCode = 200;
                $outputArray['data'] = array();
                return response()->json($outputArray, $statusCode);
            }
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }  
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        try
        {
            $rules = array(
                'brand_image' => 'mimes:jpeg,jpg,bmp,png,gif|max:52400',
                'brand_name' => 'required|unique:brands,brand_name,NULL,id,deleted_at,NULL'                
                );
            $validator = Validator::make($requestData, $rules);
            if ($validator->fails())
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
            $brand_slug = Helpers::createSlug($request->brand_name);
//          $color_unique_id = mt_rand();
            $getBrands = Brands::where('brand_slug', $brand_slug)->first();
            if(!empty($getBrands))
            {
                $brand_slug = $brand_slug.'_'.mt_rand();
            }
            $brandsOldImage = '';
            $brand_image = '';
            if(Input::file('brand_image'))
            {
                $file = Input::file('brand_image');
                if (!empty($file))
                {
                    $brand_image = Helpers::createUpdateImage($file, $this->brandOriginalImageUploadPath, $this->brandThumbImageUploadPath, $this->brandThumbImageHeight, $requestData, $brandsOldImage);
                }
            }
            $brands = Brands::create([
                'brand_name' => $request->brand_name,
                'brand_slug' => $brand_slug,
                'brand_image' => $brand_image
            ]);
            if($brands)
            {
                $outputArray = new BrandsResource($brands);
                $dataArray['status'] = 1;
                $dataArray['message'] = trans('apimessages.brands_added_successfully');
                $outputArray->additional = $dataArray;
                $statusCode = 200;
                return $outputArray;
            }
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] =  trans('apimessages.default_error_msg');
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
    }

    /**
     * Display the specified brands.
     *
     * @param  \App\Brands  $brands
     * @return \Illuminate\Http\Response
     */
    public function show($brands)
    {
        $user = JWTAuth::parseToken()->authenticate();
        try 
        {
            $brands = Brands::find($brands);
            if($brands && !empty($brands))
            {
                $outputArray = new BrandsResource($brands);
                $dataArray['status'] = 1;
                $dataArray['message'] = trans('apimessages.brands_details_fetched_successfully');
                $outputArray->additional = $dataArray;
                $statusCode = 200;
                return $outputArray;
            }
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] =  trans('apimessages.brands_not_found');
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
        }  catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        // return new BrandsResource($brands);
    }

    /**
     * Update the specified brands in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Brands  $brands
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Brands $brands)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = Input::all();
        try
        {
            $rules = array(
                'id' => 'required',
                'brand_image' => 'mimes:jpeg,jpg,bmp,png,gif|max:52400',
                'brand_name' => 'required|unique:brands,brand_name,'.$requestData['id'],
            );
            $validator = Validator::make($requestData, $rules);            
            if ($validator->fails())
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }

            $brandData = Brands::find($requestData['id']);
            if($brandData && !empty($brandData))
            {
                $brand_image = !empty($brandData->brand_image) ? $brandData->brand_image : NULL;

                if(Input::file('brand_image'))
                {
                    $file = Input::file('brand_image');
                    if (!empty($file)) 
                    {
                        $brand_image = Helpers::createUpdateImage($file, $this->brandOriginalImageUploadPath, $this->brandThumbImageUploadPath, $this->brandThumbImageHeight, $requestData, $brandData->brand_image);
                    }
                }

                $requestData['brand_image'] = $brand_image;
                $updateBrand = $brandData->update($requestData);
                if($updateBrand)
                {
                    $brandUpdatedData = Brands::find($requestData['id']);
                    $outputArray = new BrandsResource($brandUpdatedData);
                    $dataArray['status'] = 1;
                    $dataArray['message'] = trans('apimessages.brands_updated_successfully');
                    $outputArray->additional = $dataArray;
                    $statusCode = 200;
                    return $outputArray;
                }
                else
                {
                    $outputArray['status'] = 0;
                    $outputArray['message'] =  trans('apimessages.default_error_msg');
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode); 
                }
            }
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] =  trans('apimessages.brands_not_found');
                $statusCode = 200;
                return response()->json($outputArray, $statusCode); 
            }
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
    }

    /**
     * Remove the specified brands from storage.
     *
     * @param  \App\Brands  $brands
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try 
        {
            $brands = Brands::find($id);
            if($brands && !empty($brands))
            {
                $oldImgName = $brands->brand_image;
                Helpers::deleteFileToStorage($oldImgName, $this->brandOriginalImageUploadPath);
                Helpers::deleteFileToStorage($oldImgName, $this->brandThumbImageUploadPath);
                $brands->delete();
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.brands_deleted_successfully');
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] =  trans('apimessages.brands_not_found');
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
    }
    
    
    /**
     * Remove the specified brands from storage.
     *
     * @param  \App\Brands  $brands
     * @return \Illuminate\Http\Response
     */
    public function deleteMultipleBrands(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        try 
        {
            $userRoles = $this->objUserRoles->getUserRole($request->user()->id);
            if($userRoles != 1)
            {
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }
            DB::beginTransaction();
            $rules = [
                'ids' => 'required'
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
            if(isset($request->ids) && !empty($request->ids))
            {
                $idArray = explode(',', $request->ids);                
                foreach($idArray as $key => $id) 
                {
                    $brands = Brands::find($id);
                    if($brands && !empty($brands))
                    {
                        $oldImgName = $brands->brand_image;
                        Helpers::deleteFileToStorage($oldImgName, $this->brandOriginalImageUploadPath);
                        Helpers::deleteFileToStorage($oldImgName, $this->brandThumbImageUploadPath);
                        $response = $brands->delete();                        
                    }
                }
                if(isset($response) && $response)
                {
                    DB::commit();
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.brands_deleted_successfully');
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode); 
                }
                else
                {
                    $outputArray['status'] = 0;
                    $outputArray['message'] =  trans('apimessages.brands_not_found');
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode);
                }
            }
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] =  trans('apimessages.brands_not_found');
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
    }
    
    /**
     * Get Boutique Brands
     *
     * @param  \App\Brands  $brands
     * @return \Illuminate\Http\Response
     */
    public function getBoutiqueBrands($company_id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        try 
        {
            $responseData = [];
            $brandsDetails = $this->objCompany->with(['brands' => function($query) {
                            $query->groupBy('id');
                        }])->find($company_id);            
            if(isset($brandsDetails) &&  !empty($brandsDetails))
            {
                if(isset($brandsDetails->brands) && !empty($brandsDetails->brands) && count($brandsDetails->brands)>0 )
                {
                    foreach ($brandsDetails->brands as $brands)
                    {
                        $data = [];
                        $data['id'] = $brands['id'];
                        $data['brand_name'] = $brands['brand_name'];
                        $data['brand_image'] = (!empty($brands['brand_image']) && Storage::exists($this->brandThumbImageUploadPath.$brands['brand_image']) && Storage::size($this->brandThumbImageUploadPath.$brands['brand_image']) > 0) ? Storage::url($this->brandThumbImageUploadPath.$brands['brand_image']) : url($this->defaultPlusImage);
                        $responseData[] = $data;
                    }
                    return response()->json([
                        'status' => '1',
                        'message' => trans('apimessages.boutique_brands_list'),
                        'data' => $responseData
                    ], 200);
                }
                else
                {
                    return response()->json([
                        'status' => '1',
                        'message' => trans('apimessages.brands_not_found'),
                        'data' => $responseData
                        ], 200);
                }
            }
            else
            {
                return response()->json([
                    'status' => 1,
                    'message' => trans('apimessages.brands_not_found'),
                    'data' => []
                ], 200);
            }        
        }  catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
    }

    /**
     * Delete brand Image
     */
    public function deleteBrandImage(Request $request)
    {
        try 
        {
            $rules = [
                'id' => 'required'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) 
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);                
            }
            $id = $request->id;
            $brands = Brands::find($id);
            if($brands && !empty($brands) && !empty($brands->brand_image))
            {
                $oldImgName = $brands->brand_image;
                Helpers::deleteFileToStorage($oldImgName, $this->brandOriginalImageUploadPath);
                Helpers::deleteFileToStorage($oldImgName, $this->brandThumbImageUploadPath);
                $brands->brand_image = NULL;
                $response = $brands->save();
                if($response)
                {
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.brands_image_deleted_successfully');
                    $statusCode = 200;
                }
                else
                {
                    $outputArray['status'] = 0;
                    $outputArray['message'] =  trans('apimessages.default_error_msg');
                    $statusCode = 200;                    
                }                
            }
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] =  trans('apimessages.brands_image_not_found');
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
}
