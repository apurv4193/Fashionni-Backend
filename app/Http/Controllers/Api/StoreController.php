<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Config;
use DB;
use App\User;
use App\Company;
use App\Store;
use App\StoreTime;
use App\CompanyUser;
use App\CompanyDocuments;
use App\CompanyTaxDocuments;
use App\CompanyCustomDocuments;
use App\UserRoles;
use App\Roles;
use App\Permissions;
use App\UserPermission;
use Validator;
use Illuminate\Validation\Rule;
use \stdClass;
use Helpers;
use Storage;
use Input;
use File;
use Image;
use JWTAuth;
use JWTAuthException;

class StoreController extends Controller
{
    public function __construct() {
        $this->objUser = new User();
        $this->objCompany = new Company();
        $this->objCompanyUser = new CompanyUser();
        $this->objCompanyDocuments = new CompanyDocuments();
        $this->objCompanyTaxDocuments = new CompanyTaxDocuments();
        $this->objCompanyCustomDocuments = new CompanyCustomDocuments();
        $this->objStore = new Store();
        $this->objStoreTime = new StoreTime();
        $this->objUserRoles = new UserRoles();
        $this->objRoles = new Roles();
        $this->objPermissions = new Permissions();
        $this->objUserPermission = new UserPermission();
        
        // $this->userThumbImageUploadPath = Config::get('constant.USER_THUMB_IMAGE_UPLOAD_PATH');
        // $this->userOriginalImageUploadPath = Config::get('constant.USER_ORIGINAL_IMAGE_UPLOAD_PATH');
        // $this->userThumbImageHeight = Config::get('constant.USER_THUMB_IMAGE_HEIGHT');
        // $this->userThumbImageWidth = Config::get('constant.USER_THUMB_IMAGE_WIDTH');
        
        $this->storeContactPersonOriginalImageUploadPath = Config::get('constant.STORE_CONTACT_PERSON_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->storeContactPersonThumbImageUploadPath = Config::get('constant.STORE_CONTACT_PERSON_THUMB_IMAGE_UPLOAD_PATH');
        
        $this->storeOriginalImageUploadPath = Config::get('constant.STORE_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->storeThumbImageUploadPath = Config::get('constant.STORE_THUMB_IMAGE_UPLOAD_PATH');
        $this->storeThumbImageHeight = Config::get('constant.STORE_THUMB_IMAGE_HEIGHT');
        $this->storeThumbImageWidth = Config::get('constant.STORE_THUMB_IMAGE_WIDTH');
        
        $this->defaultImage = Config::get('constant.DEFAULT_IMAGE_PATH');
        $this->defaultPlusImage = Config::get('constant.DEFAULT_PLUS_IMAGE_PATH');
    }


//  Delete Store.
    public function deleteStore(Request $request) 
    {
        try 
        {
            DB::beginTransaction();
            if (!isset($request->store_id))
            {
                DB::rollback();
                return response()->json([
                    'status' => 0,
                    'message' => trans('apimessages.invalid_parameter'),
                        ], 400);
            }

            $store = Store::find($request->store_id);
            if ($store && !empty($store)) 
            {
                if(!empty($store->store_slug))
                {
                    $getStorePermission = Permissions::where('slug', $store->store_slug)->first();
                    $getStorePermission->delete();
                }
                $store->delete();
                $storeTimeDelete = StoreTime::where('store_id', $request->store_id)->delete();
            } else {
                DB::rollback();
                return response()->json([
                    'status' => 0,
                    'message' => 'Store not found.',
                        ], 400);
            }
            DB::commit();
            return response()->json([
                'status' => 1,
                'message' => 'Store deleted successfully',
                    ], 200);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => '0',
                'message' => 'Error while list boost.',
                'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Get Store Listing
     */
    public function getStoreListing(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        $outputArray = [];
        try 
        {
            $validator = Validator::make($requestData, [
                'company_id' => 'required'
            ]);

            if ($validator->fails()) 
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            } 
            else
            {
                $getStoreDetails = Store::where('company_id', $requestData['company_id'])->orderBy('updated_at', 'DESC')->get();
                if ($getStoreDetails && !empty($getStoreDetails) && $getStoreDetails->count() > 0) 
                {   
                    $mainArray = [];
                    foreach ($getStoreDetails as $storeKey => $storeValue)
                    {
                        $listArray = [];
                        $listArray['id'] = $storeValue->id;
                        $listArray['company_id'] = $storeValue->company_id;
                        $listArray['store_name'] = $storeValue->store_name;
                        $listArray['store_slug'] = $storeValue->store_slug;
                        $listArray['short_name'] = $storeValue->short_name;
                        $mainArray[] = $listArray;
                    }
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.store_listing_fetched_successfully');
                    $outputArray['data'] = $mainArray;
                    $statusCode = 200;
                } else {
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.norecordsfound');
                    $outputArray['data'] = [];
                    $statusCode = 200;
                }
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
     * Get Store Detail API
     */
    public function getStoreDetails(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        $outputArray = [];
        try 
        {
            $validator = Validator::make($requestData, [
                'store_id' => 'required'
            ]);

            if ($validator->fails()) 
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            } 
            else
            {
                $getStoreDetails = Store::where('id', $requestData['store_id'])->first();
                if ($getStoreDetails && !empty($getStoreDetails)) 
                {   
                    $checkAuthorization = Helpers::checkUserAuthorization($user->id, $getStoreDetails->store_slug, 'view');
                    if($checkAuthorization == '0')
                    {
                        $outputArray['status'] = 0;
                        $outputArray['message'] = trans('apimessages.unauthorized_access');
                        $statusCode = 200;
                        return response()->json($outputArray, $statusCode);
                    }
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.store_details_fetched_successfully');
                    $outputArray['data'] = array();
                    $statusCode = 200;
                    $outputArray['data']['id'] = $getStoreDetails->id;
                    $outputArray['data']['company_id'] = $getStoreDetails->company_id;
                    $outputArray['data']['store_name'] = $getStoreDetails->store_name;
                    $outputArray['data']['store_slug'] = $getStoreDetails->store_slug;
                    $outputArray['data']['short_name'] = $getStoreDetails->short_name;
                    $outputArray['data']['address'] = $getStoreDetails->address;
                    $outputArray['data']['postal_code'] = $getStoreDetails->postal_code;
                    $outputArray['data']['city'] = $getStoreDetails->city;
                    $outputArray['data']['state'] = $getStoreDetails->state;
                    $outputArray['data']['country'] = $getStoreDetails->country;
                    $outputArray['data']['store_lat'] = $getStoreDetails->store_lat;
                    $outputArray['data']['store_lng'] = $getStoreDetails->store_lng;
                    
                    $storeImagePath = ((isset($getStoreDetails->store_image) && !empty($getStoreDetails->store_image)) && Storage::exists($this->storeOriginalImageUploadPath.$getStoreDetails->store_image) && Storage::size($this->storeOriginalImageUploadPath.$getStoreDetails->store_image) > 0) ? Storage::url($this->storeOriginalImageUploadPath.$getStoreDetails->store_image) : url($this->defaultPlusImage);
                    
                    $outputArray['data']['store_image'] = $storeImagePath;
                    $outputArray['data']['store_contact_person_name'] = (!empty($getStoreDetails->store_contact_person_name)) ? $getStoreDetails->store_contact_person_name : '';
                    $outputArray['data']['store_contact_person_email'] = (!empty($getStoreDetails->store_contact_person_email)) ? $getStoreDetails->store_contact_person_email : '';
                    $outputArray['data']['store_contact_person_telephone'] = (!empty($getStoreDetails->store_contact_person_telephone)) ? $getStoreDetails->store_contact_person_telephone : '';
                    $outputArray['data']['store_contact_person_position'] = (!empty($getStoreDetails->store_contact_person_position)) ? $getStoreDetails->store_contact_person_position : '';
                    
                   $storeContactPersonImagePath = ((isset($getStoreDetails->store_contact_person_image) && !empty($getStoreDetails->store_contact_person_image)) && Storage::exists($this->storeContactPersonOriginalImageUploadPath.$getStoreDetails->store_contact_person_image) && Storage::size($this->storeContactPersonOriginalImageUploadPath.$getStoreDetails->store_contact_person_image) > 0) ? Storage::url($this->storeContactPersonOriginalImageUploadPath.$getStoreDetails->store_contact_person_image) : url($this->defaultPlusImage);
                   
                    $outputArray['data']['store_contact_person_image'] = $storeContactPersonImagePath;
                    
                    $outputArray['data']['opening_time'] = array();
                    if(isset($getStoreDetails->storeTime) && !empty($getStoreDetails->storeTime))
                    {
                        $outputArray['data']['opening_time']['store_id'] = $getStoreDetails->storeTime->store_id;
                        $outputArray['data']['opening_time']['mon_timing'] = $getStoreDetails->storeTime->mon_timing;
                        $outputArray['data']['opening_time']['tue_timing'] = $getStoreDetails->storeTime->tue_timing;
                        $outputArray['data']['opening_time']['wed_timing'] = $getStoreDetails->storeTime->wed_timing;
                        $outputArray['data']['opening_time']['thu_timing'] = $getStoreDetails->storeTime->thu_timing;
                        $outputArray['data']['opening_time']['fri_timing'] = $getStoreDetails->storeTime->fri_timing;
                        $outputArray['data']['opening_time']['sat_timing'] = $getStoreDetails->storeTime->sat_timing;
                        $outputArray['data']['opening_time']['sun_timing'] = $getStoreDetails->storeTime->sun_timing;
                    }
                    else{
                        $outputArray['data']['opening_time'] = new \stdClass();
                    }
                } else {
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.norecordsfound');
                    $outputArray['data'] = [];
                    $statusCode = 200;
                }
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
     * Save Store Details
     */  
    public function saveStoreDetails(Request $request)
    {
        $outputArray = [];
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        
        try 
        {
            DB::beginTransaction();
            $validator = Validator::make($requestData, [
                'store_image' => 'mimes:jpeg,jpg,bmp,png,gif|max:52400',
                'store_contact_person_image' => 'mimes:jpeg,jpg,bmp,png,gif|max:52400',
                'store_id' => 'required',
                'company_id' => 'required',
                'store_name' => 'required',
                'store_slug' => 'required',
                'short_name' => 'required',
                'address' => 'required',
                'postal_code' => 'required|min:3',
                'city'  =>  'required',
                'state'  =>  'required',
                'country'  =>  'required',
            ]);
  
            if ($validator->fails()) 
            {
                DB::rollback();
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
            } 
            else
            {
                $checkAuthorization = Helpers::checkUserAuthorization($user->id, $requestData['store_slug'], 'edit');
                if($checkAuthorization == '0')
                {
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.unauthorized_access');
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode);
                }                
                $storeData['id'] = $requestData['store_id'];
                $storeData['company_id'] = $requestData['company_id'];
                $storeData['store_name'] = $requestData['store_name'];
                $storeData['short_name'] = $requestData['short_name'];
                $storeData['address'] = $requestData['address'];
                $storeData['postal_code'] = $requestData['postal_code'];
                $storeData['city'] = $requestData['city'];
                $storeData['state'] = $requestData['state'];
                $storeData['country'] = $requestData['country'];
                $storeData['store_lat'] = (isset($requestData['store_lat']) && !empty($requestData['store_lat'])) ? $requestData['store_lat'] : NULL;
                $storeData['store_lng'] = (isset($requestData['store_lng']) && !empty($requestData['store_lng'])) ? $requestData['store_lng'] : NULL;
                
                $storeOldData = $this->objStore->find($requestData['store_id']);
                if(Input::file('store_image'))
                {
                    $storeOldImage = (!empty($storeOldData) && !empty($storeOldData->store_image)) ? $storeOldData->store_image : '';
                    $file = Input::file('store_image');
                    if (!empty($file)) 
                    {
                        $storeData['store_image'] = Helpers::createUpdateImage($file,$this->storeOriginalImageUploadPath, $this->storeThumbImageUploadPath, $this->storeThumbImageHeight, $storeData, $storeOldImage );
                    }
                }
                if(Input::file('store_contact_person_image'))
                {
                    $storeOldImage = (!empty($storeOldData) && !empty($storeOldData->store_image)) ? $storeOldData->store_image : '';
                    $file = Input::file('store_contact_person_image');
                    if (!empty($file)) 
                    {
                        $storeData['store_contact_person_image'] = Helpers::createUpdateImage($file,$this->storeContactPersonOriginalImageUploadPath, $this->storeContactPersonThumbImageUploadPath, $this->storeThumbImageHeight, $storeData, $storeOldImage );
                    }
                }                
                if(isset($requestData['store_contact_person_name']) && !empty($requestData['store_contact_person_name']))
                {
                    $storeData['store_contact_person_name'] = $requestData['store_contact_person_name'];
                }
                
                if(isset($requestData['store_contact_person_email']) && !empty($requestData['store_contact_person_email']))
                {
                    $storeData['store_contact_person_email'] = $requestData['store_contact_person_email'];
                }
                
                if(isset($requestData['store_contact_person_telephone']) && !empty($requestData['store_contact_person_telephone']))
                {
                    $storeData['store_contact_person_telephone'] = $requestData['store_contact_person_telephone'];
                }
                
                if(isset($requestData['store_contact_person_position']) && !empty($requestData['store_contact_person_position']))
                {
                    $storeData['store_contact_person_position'] = $requestData['store_contact_person_position'];
                }

                $saveStoreDetails = $this->objStore->insertUpdate($storeData);
                if($saveStoreDetails)
                {
                    $requestData['opening_time'] = json_decode($requestData['opening_time'], true);
                    
                    if(isset($requestData['opening_time']) && !empty($requestData['opening_time']))
                    {
                        $storeOldTimeData = StoreTime::where('store_id', $saveStoreDetails->id)->first();
                        if(!empty($storeOldTimeData))
                        {
                            $storeTime['id'] = $storeOldTimeData->id;
                        }
                        $storeTime['store_id'] =  $saveStoreDetails->id;
                        $storeTime['mon_timing'] = (!empty($requestData['opening_time']['mon_timing'])) ? $requestData['opening_time']['mon_timing'] : NULL;
                        $storeTime['tue_timing'] = (!empty($requestData['opening_time']['tue_timing'])) ? $requestData['opening_time']['tue_timing'] : NULL;
                        $storeTime['wed_timing'] = (!empty($requestData['opening_time']['wed_timing'])) ? $requestData['opening_time']['wed_timing'] : NULL;
                        $storeTime['thu_timing'] = (!empty($requestData['opening_time']['thu_timing'])) ? $requestData['opening_time']['thu_timing'] : NULL;
                        $storeTime['fri_timing'] = (!empty($requestData['opening_time']['fri_timing'])) ? $requestData['opening_time']['fri_timing'] : NULL;
                        $storeTime['sat_timing'] = (!empty($requestData['opening_time']['sat_timing'])) ? $requestData['opening_time']['sat_timing'] : NULL;
                        $storeTime['sun_timing'] = (!empty($requestData['opening_time']['sun_timing'])) ? $requestData['opening_time']['sun_timing'] : NULL;    
                       $saveStoreTime = $this->objStoreTime->insertUpdate($storeTime); 
                    }
                    DB::commit();
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.store_details_save_successfully');
                    $statusCode = 200;
                }
                else
                {
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.default_error_msg');
                    $statusCode = 200;
                }
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
