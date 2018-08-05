<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use DB;
use Auth;
use Config;
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
use App\Notifications;
use App\UserPermission;
use Validator;
use \stdClass;
use Helpers;
use Storage;
use Input;
use File;
use Image;
use JWTAuth;
use JWTAuthException;


class BoutiqueCompanyController extends Controller
{
    public function __construct()
    {
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
        $this->objNotifications = new Notifications();

        $this->companyCustomsDocumentsUploadPath = Config::get('constant.COMPANY_CUSTOMS_DOCUMENTS_UPLOAD_PATH');
        $this->companyDocumentsUploadPath = Config::get('constant.COMPANY_DOCUMENTS_UPLOAD_PATH');
        $this->companyTaxDocumentsUploadPath = Config::get('constant.COMPANY_TAX_DOCUMENTS_UPLOAD_PATH');

        $this->companyOriginalImageUploadPath = Config::get('constant.COMPANY_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->companyThumbImageUploadPath = Config::get('constant.COMPANY_THUMB_IMAGE_UPLOAD_PATH');
        $this->companyThumbImageHeight = Config::get('constant.COMPANY_THUMB_IMAGE_HEIGHT');
        $this->companyThumbImageWidth = Config::get('constant.COMPANY_THUMB_IMAGE_WIDTH');

        $this->storeContactPersonOriginalImageUploadPath = Config::get('constant.STORE_CONTACT_PERSON_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->storeContactPersonThumbImageUploadPath = Config::get('constant.STORE_CONTACT_PERSON_THUMB_IMAGE_UPLOAD_PATH');

        $this->storeOriginalImageUploadPath = Config::get('constant.STORE_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->storeThumbImageUploadPath = Config::get('constant.STORE_THUMB_IMAGE_UPLOAD_PATH');
        $this->storeThumbImageHeight = Config::get('constant.STORE_THUMB_IMAGE_HEIGHT');
        $this->storeThumbImageWidth = Config::get('constant.STORE_THUMB_IMAGE_WIDTH');

        $this->userOriginalImageUploadPath = Config::get('constant.USER_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->userThumbImageUploadPath = Config::get('constant.USER_THUMB_IMAGE_UPLOAD_PATH');
        $this->userThumbImageHeight = Config::get('constant.USER_THUMB_IMAGE_HEIGHT');
        $this->userThumbImageWidth = Config::get('constant.USER_THUMB_IMAGE_WIDTH');

        $this->companyContactPersonOriginalImageUploadPath = Config::get('constant.COMPANY_CONTACT_PERSON_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->companyContactPersonThumbImageUploadPath = Config::get('constant.COMPANY_CONTACT_PERSON_THUMB_IMAGE_UPLOAD_PATH');

        $this->defaultImage = Config::get('constant.DEFAULT_IMAGE_PATH');
        $this->defaultPlusImage = Config::get('constant.DEFAULT_PLUS_IMAGE_PATH');
    }

    //get Company details by id for Admin
    public function getCompanyDetail (Request $request)
    {
        try
        {
            DB::beginTransaction();
            $rules = [
                'company_id' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ], 200);
            }

            $userRoles = $this->objUserRoles->getUserRole($request->user()->id);

            if($userRoles != 2)
            {
                return response()->json([
                        'status' => '0',
                        'message' => trans('apimessages.unauthorized_access')
                    ], 400);
            }
            $companyDetail = $this->objCompany->getCompanyDetail($request->company_id);

            $companyDetail->company_image = (isset($companyDetail->company_image) && $companyDetail->company_image != NULL && $companyDetail->company_image != '' && Storage::exists($this->companyThumbImageUploadPath.$companyDetail->company_image) && Storage::size($this->companyThumbImageUploadPath.$companyDetail->company_image) > 0) ? Storage::url($this->companyThumbImageUploadPath . $companyDetail->company_image) : '';

            foreach ($companyDetail->store as $_store) {
                $_store->store_image = ($_store->store_image != NULL && $_store->store_image != '' && $_store->company_image != NULL && $_store->company_image != '' && Storage::exists($this->storeThumbImageUploadPath.$_store->store_image) && Storage::size($this->storeThumbImageUploadPath.$_store->store_image) > 0) ? Storage::url($this->storeThumbImageUploadPath . $_store->store_image) : url($this->defaultPlusImage);
            }

            if(!empty($companyDetail))
            {
                return response()->json([
                'status' => '1',
                'message' => trans('apimessages.company_details'),
                'data' =>  $companyDetail
                ], 200);
            }
            else
            {
                return response()->json([
                    'status' => '1',
                    'message' => trans('apimessages.company_details_not_found'),
                    'code' => 200
                ]);
            }
            DB::commit();
        }catch (Exception $e)
        {
            DB::rollback();
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_company'),
                'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Get Company User List
     */
    public function getCompanyUserList(Request $request)
    {
        try
        {
            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id,Config::get('constant.BOUTIQUE_USER'),'view');
            if($checkAuthorization == '0'){
                return response()->json([
                    'status' => 0,
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }

            DB::beginTransaction();
            $rules = [
                'company_id' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ], 200);
            }

            $getCompanyUserList = $this->objCompanyUser->getCompanyUsers($request->company_id);

            $responseData = [];
            if(isset($getCompanyUserList) && $getCompanyUserList != '')
            {
                foreach ($getCompanyUserList as $key => $value)
                {   
                    $data = [];
                    $data['id'] = $value->getUser['id'];
                    $data['name'] = $value->getUser['name'];
                    $data['user_unique_id'] = $value->getUser['user_unique_id'];

                    $data['user_image'] = ($value->getUser['photo'] != NULL && $value->getUser['photo'] != '' && Storage::exists($this->userThumbImageUploadPath.$value->getUser['photo']) && Storage::size($this->userThumbImageUploadPath.$value->getUser['photo']) > 0) ? Storage::url($this->userThumbImageUploadPath . $value->getUser['photo']) : url($this->defaultImage);
                    $data['defaults'] = $value['default'];
                    $responseData[] = $data;
                }
            }

            return response()->json([
                'status' => '1',
                'message' => trans('apimessages.company_user_list'),
                'data' => $responseData
            ], 200);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_company_user_list'),
                'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Get Company User Details By ID
     */
    public function getCompanyUserDetailById(Request $request)
    {
        try {
            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id,Config::get('constant.BOUTIQUE_USER'),'view');
            if($checkAuthorization == '0'){
               return response()->json([
                           'status' => 0,
                           'message' => trans('apimessages.unauthorized_access')
               ], 400);
            }
            DB::beginTransaction();
            $rules = [
                'user_id' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ], 200);
            }

            $companyUserDetail = $this->objUser->getUserDetail($request->user_id);

            $permissionDetail = $this->objPermissions->getPermissionByCompanyId($request->company_id);

            if($companyUserDetail && !empty($companyUserDetail) && $companyUserDetail->count() > 0 && $permissionDetail && !empty($permissionDetail) && $permissionDetail->count() > 0)
            {
                $data = [];
                $data['user_id'] = $companyUserDetail->id;
                $data['user_name'] = $companyUserDetail->user_name;

                $data['user_image'] = (!empty($companyUserDetail->photo) && Storage::exists($this->userThumbImageUploadPath.$companyUserDetail->photo) && Storage::size($this->userThumbImageUploadPath.$companyUserDetail->photo) > 0) ? Storage::url($this->userThumbImageUploadPath.$companyUserDetail->photo) : url($this->defaultImage);

                $data['user_unique_id'] = $companyUserDetail->user_unique_id;
                $data['name'] = $companyUserDetail->name;
                $data['role'] = $companyUserDetail->roles[0]->name;
                $data['role_id'] = $companyUserDetail->roles[0]->id;
                $data['custom_user_role'] = (!empty($companyUserDetail->custom_role_name)) ? $companyUserDetail->custom_role_name : '';
                $data['position'] = $companyUserDetail->position;
                $data['email'] = $companyUserDetail->email;
                $data['permission'] = [];
                if(isset($companyUserDetail->userPermission) && $companyUserDetail->userPermission != ''){

                    $edit = explode(',', $companyUserDetail->userPermission->edit);
                    $view = explode(',', $companyUserDetail->userPermission->view);

                    foreach ($permissionDetail as $key => $permissionList) {

                        if(in_array($permissionList->id, $edit) && in_array($permissionList->id, $view)){
                            $permission = [];
                            $permission['id'] = $permissionList->id;
                            $permission['pageName'] = $permissionList->slug;
                            $permission['labelName'] = $permissionList->label_name;
                            $permission['edit'] = 1;
                            $permission['view'] = 1;

                            array_push($data['permission'], $permission);
                        }
                        elseif(in_array($permissionList->id, $edit) && !in_array($permissionList->id, $view)){
                            $permission = [];
                            $permission['id'] = $permissionList->id;
                            $permission['pageName'] = $permissionList->slug;
                            $permission['labelName'] = $permissionList->label_name;
                            $permission['edit'] = 1;
                            $permission['view'] = 0;

                            array_push($data['permission'], $permission);
                        }
                        elseif(!in_array($permissionList->id, $edit) && in_array($permissionList->id, $view)){
                            $permission = [];
                            $permission['id'] = $permissionList->id;
                            $permission['pageName'] = $permissionList->slug;
                            $permission['labelName'] = $permissionList->label_name;
                            $permission['edit'] = 0;
                            $permission['view'] = 1;

                            array_push($data['permission'], $permission);
                        }
                        else{
                            $permission = [];
                            $permission['id'] = $permissionList->id;
                            $permission['pageName'] = $permissionList->slug;
                            $permission['labelName'] = $permissionList->label_name;
                            $permission['edit'] = 0;
                            $permission['view'] = 0;

                            array_push($data['permission'], $permission);
                        }
                    }
                }
                return response()->json([
                    'status' => '1',
                    'message' => trans('apimessages.company_user_details'),
                    'data' => $data
                ], 200);
            }
            else
            {
                return response()->json([
                    'status' => 1,
                    'message' => trans('apimessages.norecordsfound'),
                    'data' => $data
                ], 200);
            }
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_company_user_details'),
                'code' => $e->getStatusCode()
            ]);
        }
    }


    /**
     * Get User Details By ID Without Authorization check
     */
    public function getUserDetailById(Request $request)
    {
        try {
            DB::beginTransaction();
            $rules = [
                'user_id' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ], 400);
            }

            $companyUserDetail = $this->objUser->getUserDetail($request->user_id);

            $permissionDetail = $this->objPermissions->getPermissionByCompanyId($request->company_id);

            if($companyUserDetail && !empty($companyUserDetail) && $companyUserDetail->count() > 0 && $permissionDetail && !empty($permissionDetail) && $permissionDetail->count() > 0)
            {
                $data = [];
                $data['user_id'] = $companyUserDetail->id;
                $data['user_name'] = $companyUserDetail->user_name;

                $data['user_image'] = (!empty($companyUserDetail->photo) && Storage::exists($this->userThumbImageUploadPath.$companyUserDetail->photo) && Storage::size($this->userThumbImageUploadPath.$companyUserDetail->photo) > 0) ? Storage::url($this->userThumbImageUploadPath.$companyUserDetail->photo) : url($this->defaultImage);

                $data['user_unique_id'] = $companyUserDetail->user_unique_id;
                $data['name'] = $companyUserDetail->name;
                $data['role'] = $companyUserDetail->roles[0]->name;
                $data['role_id'] = $companyUserDetail->roles[0]->id;
                $data['custom_user_role'] = (!empty($companyUserDetail->custom_role_name)) ? $companyUserDetail->custom_role_name : '';
                $data['position'] = $companyUserDetail->position;
                $data['email'] = $companyUserDetail->email;
                $data['permission'] = [];
                if(isset($companyUserDetail->userPermission) && $companyUserDetail->userPermission != ''){

                    $edit = explode(',', $companyUserDetail->userPermission->edit);
                    $view = explode(',', $companyUserDetail->userPermission->view);

                    foreach ($permissionDetail as $key => $permissionList) {

                        if(in_array($permissionList->id, $edit) && in_array($permissionList->id, $view)){
                            $permission = [];
                            $permission['id'] = $permissionList->id;
                            $permission['pageName'] = $permissionList->slug;
                            $permission['labelName'] = $permissionList->label_name;
                            $permission['edit'] = 1;
                            $permission['view'] = 1;

                            array_push($data['permission'], $permission);
                        }
                        elseif(in_array($permissionList->id, $edit) && !in_array($permissionList->id, $view)){
                            $permission = [];
                            $permission['id'] = $permissionList->id;
                            $permission['pageName'] = $permissionList->slug;
                            $permission['labelName'] = $permissionList->label_name;
                            $permission['edit'] = 1;
                            $permission['view'] = 0;

                            array_push($data['permission'], $permission);
                        }
                        elseif(!in_array($permissionList->id, $edit) && in_array($permissionList->id, $view)){
                            $permission = [];
                            $permission['id'] = $permissionList->id;
                            $permission['pageName'] = $permissionList->slug;
                            $permission['labelName'] = $permissionList->label_name;
                            $permission['edit'] = 0;
                            $permission['view'] = 1;

                            array_push($data['permission'], $permission);
                        }
                        else{
                            $permission = [];
                            $permission['id'] = $permissionList->id;
                            $permission['pageName'] = $permissionList->slug;
                            $permission['labelName'] = $permissionList->label_name;
                            $permission['edit'] = 0;
                            $permission['view'] = 0;

                            array_push($data['permission'], $permission);
                        }
                    }
                }
                return response()->json([
                    'status' => '1',
                    'message' => trans('apimessages.company_user_details'),
                    'data' => $data
                ], 200);
            }
            else
            {
                return response()->json([
                    'status' => 1,
                    'message' => trans('apimessages.norecordsfound'),
                    'data' => $data
                ],200);
            }
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_company_user_details'),
                'code' => $e->getStatusCode()
            ]);
        }
    }
    
    /**
     * Get Company Profile Details
     */
    public function getCompanyProfileDetail(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        $outputArray = [];
        try
        {
            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id,Config::get('constant.BOUTIQUE_COMPANY'),'view');
            if($checkAuthorization == '0'){
                return response()->json([
                    'status' => 0,
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }

            $validator = Validator::make($requestData, [
                'company_id' => 'required'
            ]);

            if ($validator->fails()) 
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
            }
            else
            {
                $getCompanyDetails = $this->objCompany->getCompanyProfileDetails($request->company_id);

                if (isset($getCompanyDetails) && $getCompanyDetails != '')
                {
                        $mainArray = [];
                        //company details
                        $mainArray['company_name'] = $getCompanyDetails->company_name;
                        $mainArray['address'] = (!empty($getCompanyDetails->address)) ? $getCompanyDetails->address : '';
                        $mainArray['postal_code'] = (!empty($getCompanyDetails->postal_code)) ? $getCompanyDetails->postal_code : '';
                        $mainArray['city'] = (!empty($getCompanyDetails->city)) ? $getCompanyDetails->city : '';
                        $mainArray['state'] = (!empty($getCompanyDetails->state)) ? $getCompanyDetails->state : '';
                        $mainArray['country'] = (!empty($getCompanyDetails->country)) ? $getCompanyDetails->country : '';

                        $mainArray['company_image'] = (!empty($getCompanyDetails->company_image) && Storage::exists($this->companyThumbImageUploadPath.$getCompanyDetails->company_image) && Storage::size($this->companyThumbImageUploadPath.$getCompanyDetails->company_image) > 0) ? Storage::url($this->companyThumbImageUploadPath.$getCompanyDetails->company_image) : url($this->defaultPlusImage);

                        //company social details
                        $mainArray['website'] = (!empty($getCompanyDetails->website)) ? $getCompanyDetails->website : '';
                        $mainArray['facebook'] = (!empty($getCompanyDetails->facebook)) ? $getCompanyDetails->facebook : '';
                        $mainArray['company_email'] = (!empty($getCompanyDetails->company_email)) ? $getCompanyDetails->company_email : '';
                        $mainArray['twitter'] = (!empty($getCompanyDetails->twitter)) ? $getCompanyDetails->twitter : '';
                        $mainArray['whatsapp'] = (!empty($getCompanyDetails->whatsapp)) ? $getCompanyDetails->whatsapp : '';
                        $mainArray['instagram'] = (!empty($getCompanyDetails->instagram)) ? $getCompanyDetails->instagram : '';
                        $mainArray['wechat'] = (!empty($getCompanyDetails->wechat)) ? $getCompanyDetails->wechat : '';
                        $mainArray['pinterest'] = (!empty($getCompanyDetails->pinterest)) ? $getCompanyDetails->pinterest : '';

                        //contact person details
                        $mainArray['contact_person_position'] = (!empty($getCompanyDetails->contact_person_position)) ? $getCompanyDetails->contact_person_position : '';
                        $mainArray['contact_person_gender'] = (!empty($getCompanyDetails->contact_person_gender)) ? $getCompanyDetails->contact_person_gender : '';
                        $mainArray['contact_person_first_name'] = (!empty($getCompanyDetails->contact_person_first_name)) ? $getCompanyDetails->contact_person_first_name : '';
                        $mainArray['contact_person_last_name'] = (!empty($getCompanyDetails->contact_person_last_name)) ? $getCompanyDetails->contact_person_last_name : '';
                        $mainArray['contact_person_telefon'] = (!empty($getCompanyDetails->contact_person_telefon)) ? $getCompanyDetails->contact_person_telefon : '';
                        $mainArray['contact_person_fax'] = (!empty($getCompanyDetails->contact_person_fax)) ? $getCompanyDetails->contact_person_fax : '';
                        $mainArray['contact_person_mo_no'] = (!empty($getCompanyDetails->contact_person_mo_no)) ? $getCompanyDetails->contact_person_mo_no : '';
                        $mainArray['contact_person_email'] = (!empty($getCompanyDetails->contact_person_email)) ? $getCompanyDetails->contact_person_email : '';

                        $mainArray['contact_person_image'] = (!empty($getCompanyDetails->contact_person_image) && Storage::exists($this->companyContactPersonThumbImageUploadPath.$getCompanyDetails->contact_person_image) && Storage::size($this->companyContactPersonThumbImageUploadPath.$getCompanyDetails->contact_person_image) > 0) ? Storage::url($this->companyContactPersonThumbImageUploadPath . $getCompanyDetails->contact_person_image) : url($this->defaultPlusImage);

                        $outputArray['status'] = 1;
                        $outputArray['message'] = trans('apimessages.company_profile_details');
                        $outputArray['data'] = $mainArray;
                        $statusCode = 200;

                } else {
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.record_not_found');
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
     * Save Company Profile Details
     */
    public function saveCompanyProfileDetail(Request $request)
    {
        $outputArray = [];
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        try
        {
            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id,Config::get('constant.BOUTIQUE_COMPANY'),'edit');
            if($checkAuthorization == '0'){
                return response()->json([
                            'status' => 0,
                            'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }

            DB::beginTransaction();
            $validator = Validator::make($requestData, [
                'company_id' => 'required',
                'company_name' => 'required',
                'address'  => 'required',
                'postal_code'  => 'required|min:3',
                'city'  => 'required',
                'state' => 'required',
                'country'  => 'required',
                'company_email'  => 'required',
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
                   $insertData = [];

                    $insertData['id'] = (isset($requestData['company_id'])) ? $requestData['company_id'] : '';

                    $insertData['company_name'] = (isset($requestData['company_name'])) ? $requestData['company_name'] : '';

                    $insertData['address'] = (isset($requestData['address'])) ? $requestData['address'] : '';

                    $insertData['postal_code'] = (isset($requestData['postal_code'])) ? $requestData['postal_code'] : '';

                    $insertData['city'] = (isset($requestData['city'])) ? $requestData['city'] : '';

                    $insertData['state'] = (isset($requestData['state'])) ? $requestData['state'] : '';

                    $insertData['country'] = (isset($requestData['country'])) ? $requestData['country'] : '';

                    $insertData['company_email'] = (isset($requestData['company_email'])) ? $requestData['company_email'] : '';

                    $insertData['website'] = (isset($requestData['website'])) ? $requestData['website'] : '';

                    $insertData['facebook'] = (isset($requestData['facebook'])) ? $requestData['facebook'] : '';

                    $insertData['twitter'] = (isset($requestData['twitter'])) ? $requestData['twitter'] : '';

                    $insertData['whatsapp'] = (isset($requestData['whatsapp'])) ? $requestData['whatsapp'] : '';

                    $insertData['instagram'] = (isset($requestData['instagram'])) ? $requestData['instagram'] : '';

                    $insertData['wechat'] = (isset($requestData['wechat'])) ? $requestData['wechat'] : '';

                    $insertData['pinterest'] = (isset($requestData['pinterest'])) ? $requestData['pinterest'] : '';

                    $insertData['contact_person_position'] = (isset($requestData['contact_person_position'])) ? $requestData['contact_person_position'] : '';

                    $insertData['contact_person_gender'] = (isset($requestData['contact_person_gender'])) ? $requestData['contact_person_gender'] : '';

                    $insertData['contact_person_first_name'] = (isset($requestData['contact_person_first_name'])) ? $requestData['contact_person_first_name'] : '';

                    $insertData['contact_person_last_name'] = (isset($requestData['contact_person_last_name'])) ? $requestData['contact_person_last_name'] : '';

                    $insertData['contact_person_telefon'] = (isset($requestData['contact_person_telefon'])) ? $requestData['contact_person_telefon'] : '';

                    $insertData['contact_person_fax'] = (isset($requestData['contact_person_fax'])) ? $requestData['contact_person_fax'] : '';

                    $insertData['contact_person_mo_no'] = (isset($requestData['contact_person_mo_no'])) ? $requestData['contact_person_mo_no'] : '';

                    $insertData['contact_person_email'] = (isset($requestData['contact_person_email'])) ? $requestData['contact_person_email'] : '';
                        
                    if (Input::file('company_image')) {
                        $file = Input::file('company_image');
                        if (!empty($file)) {
                            $insertData['company_image'] = Helpers::createUpdateImage($file,$this->companyOriginalImageUploadPath, $this->companyThumbImageUploadPath, $this->companyThumbImageHeight, null, null );
                        }
                    }
                    
                    if (Input::file('contact_person_image')) {
                        $file = Input::file('contact_person_image');
                        if (!empty($file)) {
                            $insertData['contact_person_image'] = Helpers::createUpdateImage($file,$this->companyContactPersonOriginalImageUploadPath, $this->companyContactPersonThumbImageUploadPath, $this->companyThumbImageHeight, null, null );
                        }
                    }

                    $saveCompanyDetails = $this->objCompany->insertUpdate($insertData);
                    if($saveCompanyDetails){
                        DB::commit();
                        $outputArray['status'] = 1;
                        $outputArray['message'] = trans('apimessages.company_profile_details_update');
                        $statusCode = 200;
                    }else{
                        DB::rollback();
                        $outputArray['status'] = 0;
                        $outputArray['message'] =  trans('apimessages.default_error_msg');
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
     * Get Company Register Details
     */
    public function getCompanyRegisterDetails(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        $outputArray = [];
        try
        {
            $validator = Validator::make($requestData, [
                'company_id' => 'required'
            ]);

            if ($validator->fails()) {
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
            }
            else
            {
                $checkAuthorization = Helpers::checkUserAuthorization($user->id, Config::get('constant.BOUTIQUE_REG'), 'view');
                if($checkAuthorization == '0')
                {
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.unauthorized_access');
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode);
                }
                else
                {
                    $getCompanyDetails = Company::find($request->company_id);
                    if ($getCompanyDetails && !empty($getCompanyDetails))
                    {
                        $mainArray = [];
                        $mainArray['company_name'] = $getCompanyDetails->register_company_name;
                        $mainArray['register_number'] = (!empty($getCompanyDetails->register_number)) ? $getCompanyDetails->register_number : '';
                        $mainArray['register_date'] = (!empty($getCompanyDetails->register_date)) ? $getCompanyDetails->register_date : '';
                        $mainArray['court_name'] = (!empty($getCompanyDetails->court_name)) ? $getCompanyDetails->court_name : '';
                        $mainArray['legal_person'] = (!empty($getCompanyDetails->legal_person)) ? $getCompanyDetails->legal_person : '';
                        $mainArray['general_manager'] = (!empty($getCompanyDetails->general_manager)) ? $getCompanyDetails->general_manager : '';
                        $mainArray['company_documents'] = [];

                        if(isset($getCompanyDetails->companyDocuments) && $getCompanyDetails->companyDocuments->count() > 0)
                        {
                            foreach ($getCompanyDetails->companyDocuments as $docsKey => $docsValue)
                            {
                                $listArray = [];
                                $companyDocsPath = ((isset($docsValue->company_doc_file_name) && !empty($docsValue->company_doc_file_name)) && Storage::exists($this->companyDocumentsUploadPath.$docsValue->company_doc_file_name)  && Storage::size($this->companyDocumentsUploadPath.$docsValue->company_doc_file_name) > 0) ? Storage::url($this->companyDocumentsUploadPath.$docsValue->company_doc_file_name) : '';
//                              $companyDocsPath = '';

                                $listArray['id'] = $docsValue->id;
                                $listArray['company_id'] = $docsValue->company_id;
                                $listArray['company_doc_name'] = (isset($docsValue->company_doc_name) && !empty($docsValue->company_doc_name)) ? $docsValue->company_doc_name : '' ;
                                $listArray['company_doc_file_name'] = (isset($docsValue->company_doc_file_name) && !empty($docsValue->company_doc_file_name)) ? $docsValue->company_doc_file_name : '' ;
                                $listArray['company_doc_url'] = $companyDocsPath;

                                $mainArray['company_documents'][$docsKey] = $listArray;
                            }
                        }
                        $outputArray['status'] = 1;
                        $outputArray['message'] = trans('apimessages.company_register_details_fetched_successfully');
                        $outputArray['data'] = $mainArray;
                        $statusCode = 200;
                    } else {
                        $outputArray['status'] = 1;
                        $outputArray['message'] = trans('apimessages.norecordsfound');
                        $outputArray['data'] = [];
                        $statusCode = 200;
                    }
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
     * Save Company Register Details
     */
    public function saveCompanyRegisterDetails(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        $outputArray = [];
        try
        {
            DB::beginTransaction();
            $validator = Validator::make($requestData, [
                'company_documents.*' => 'mimes:pdf,doc,docx,jpeg,jpg,png|max:1052400',
                'company_id' => 'required',
                'register_number' => 'required',
                'register_date'  =>  'required',
                'company_name'  =>  'required',
                'legal_person'  =>  'required'
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
                $getCompanyUserDetails = Company::find($request->company_id);
                if ($getCompanyUserDetails && !empty($getCompanyUserDetails))
                {
                    $insertData = [];
                    $insertData['id'] = $requestData['company_id'];
                    $insertData['register_number'] = $requestData['register_number'];
                    $insertData['register_date'] = $requestData['register_date'];
                    $insertData['register_company_name'] = $requestData['company_name'];
                    $insertData['court_name'] = $requestData['court_name'];
                    $insertData['legal_person'] = $requestData['legal_person'];
                    $insertData['general_manager'] = $requestData['general_manager'];

                    $checkAuthorization = Helpers::checkUserAuthorization($user->id, Config::get('constant.BOUTIQUE_REG'), 'edit');
                    if($checkAuthorization == '0')
                    {
                        $outputArray['status'] = 0;
                        $outputArray['message'] = trans('apimessages.unauthorized_access');
                        $statusCode = 200;
                        return response()->json($outputArray, $statusCode);
                    }
                    else
                    {
                        $saveCompanyDetails = $this->objCompany->insertUpdate($insertData);
                        if($saveCompanyDetails)
                        {
                            if (Input::file('company_documents'))
                            {
                                $fileDocsArray = Input::file('company_documents');
                                if (isset($fileDocsArray) && count($fileDocsArray) > 0 && !empty($fileDocsArray))
                                {
                                    foreach($fileDocsArray as $fileDocKey => $fileDocValue)
                                    {
                                        $prefix = 'company_docs_';
                                        $docsData = Helpers::createDocuments($fileDocValue, $this->companyDocumentsUploadPath, $prefix, $requestData);
                                        if(isset($docsData) && $docsData['doc_name'] && $docsData['doc_original_name'])
                                        {
                                            CompanyDocuments::firstOrCreate(['company_id' => $requestData['company_id'], 'company_doc_name' => $docsData['doc_original_name'], 'company_doc_file_name' => $docsData['doc_name']]);
                                        }
                                    }
                                }
                            }
                            DB::commit();
                            $outputArray['status'] = 1;
                            $outputArray['message'] =  trans('apimessages.company_register_details_saved_successfully');
                            $statusCode = 200;
                        }
                        else
                        {
                            DB::rollback();
                            $outputArray['status'] = 0;
                            $outputArray['message'] =  trans('apimessages.default_error_msg');
                            $statusCode = 200;
                        }
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
     * Get Company Tax Details
     */
    public function getCompanyTaxDetails(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        $outputArray = [];
        try
        {
            $validator = Validator::make($requestData, [
                'company_id' => 'required'
            ]);

            if ($validator->fails()) {
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
            }
            else
            {
                $checkAuthorization = Helpers::checkUserAuthorization($user->id, Config::get('constant.BOUTIQUE_TAX'), 'view');
                if($checkAuthorization == '0')
                {
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.unauthorized_access');
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode);
                }
                $getCompanyDetails = Company::find($request->company_id);
                if ($getCompanyDetails && !empty($getCompanyDetails))
                {
                    $mainArray = [];
                    $mainArray['company_name'] = $getCompanyDetails->tax_company_name;
                    $mainArray['EUTIN'] = (!empty($getCompanyDetails->EUTIN)) ? $getCompanyDetails->EUTIN : '';
                    $mainArray['NTIN'] = (!empty($getCompanyDetails->NTIN)) ? $getCompanyDetails->NTIN : '';
                    $mainArray['LTA'] = (!empty($getCompanyDetails->LTA)) ? $getCompanyDetails->LTA : '';
                    $mainArray['default_vat_rate'] = (!empty($getCompanyDetails->default_vat_rate)) ? $getCompanyDetails->default_vat_rate : '';
                    $mainArray['company_tax_documents'] = [];

                    if(isset($getCompanyDetails->companyTaxDocuments) && $getCompanyDetails->companyTaxDocuments->count() > 0)
                    {
                        foreach ($getCompanyDetails->companyTaxDocuments as $docsKey => $docsValue)
                        {
                            $listArray = [];
                            $companyDocsPath = ((isset($docsValue->company_doc_file_name) && !empty($docsValue->company_doc_file_name)) && Storage::exists($this->companyTaxDocumentsUploadPath.$docsValue->company_doc_file_name) && Storage::size($this->companyTaxDocumentsUploadPath.$docsValue->company_doc_file_name) > 0) ? Storage::url($this->companyTaxDocumentsUploadPath.$docsValue->company_doc_file_name) : '';
//                          $companyDocsPath = '';

                            $listArray['id'] = $docsValue->id;
                            $listArray['company_id'] = $docsValue->company_id;
                            $listArray['company_tax_doc_name'] = (isset($docsValue->company_tax_doc_name) && !empty($docsValue->company_tax_doc_name)) ? $docsValue->company_tax_doc_name : '' ;
                            $listArray['company_doc_file_name'] = (isset($docsValue->company_doc_file_name) && !empty($docsValue->company_doc_file_name)) ? $docsValue->company_doc_file_name : '' ;
                            $listArray['company_tax_doc_url'] = $companyDocsPath;
                            $mainArray['company_tax_documents'][] = $listArray;
                        }
                    }
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.company_tax_details_fetched_successfully');
                    $outputArray['data'] = $mainArray;
                    $statusCode = 200;
                } else {
                    $outputArray['status'] = 1;
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
     * Save Company Tax Details
     */
    public function saveCompanyTaxDetails(Request $request)
    {
        $outputArray = [];
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        try
        {
            DB::beginTransaction();
            $validator = Validator::make($requestData, [
                'company_tax_documents.*' => 'mimes:pdf,doc,docx,jpeg,jpg,png|max:1052400',
                'company_id' => 'required',
                'company_name' => 'required',
                'EUTIN'  =>  'required'
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
                $getCompanyUserDetails = Company::find($request->company_id);
                if ($getCompanyUserDetails && !empty($getCompanyUserDetails))
                {
                    $insertData = [];
                    $insertData['id'] = $requestData['company_id'];
                    $insertData['tax_company_name'] = $requestData['company_name'];
                    $insertData['EUTIN'] = $requestData['EUTIN'];
                    $insertData['NTIN'] = (!empty($requestData['NTIN'])) ? $requestData['NTIN'] : NULL;
                    $insertData['LTA'] = (!empty($requestData['LTA'])) ? $requestData['LTA'] : NULL;
                    $insertData['default_vat_rate'] = (!empty($requestData['default_vat_rate'])) ? $requestData['default_vat_rate'] : NULL;

                    $checkAuthorization = Helpers::checkUserAuthorization($user->id, Config::get('constant.BOUTIQUE_TAX'), 'edit');
                    if($checkAuthorization == '0')
                    {
                        $outputArray['status'] = 0;
                        $outputArray['message'] = trans('apimessages.unauthorized_access');
                        $statusCode = 200;
                        return response()->json($outputArray, $statusCode);
                    }
                    else
                    {
                        $saveCompanyDetails = $this->objCompany->insertUpdate($insertData);
                        if($saveCompanyDetails)
                        {
                            if (Input::file('company_tax_documents'))
                            {
                                $fileDocsArray = Input::file('company_tax_documents');
                                if (isset($fileDocsArray) && count($fileDocsArray) > 0 && !empty($fileDocsArray))
                                {
                                    foreach($fileDocsArray as $fileDocKey => $fileDocValue)
                                    {
                                        $prefix = 'company_tax_docs_';
                                        $docsData = Helpers::createDocuments($fileDocValue, $this->companyTaxDocumentsUploadPath, $prefix, $requestData);
                                        if(isset($docsData) && $docsData['doc_name'] && $docsData['doc_original_name'])
                                        {
                                            CompanyTaxDocuments::firstOrCreate(['company_id' => $requestData['company_id'], 'company_tax_doc_name' => $docsData['doc_original_name'], 'company_doc_file_name' => $docsData['doc_name']]);
                                        }
                                    }
                                }
                            }
                            DB::commit();
                            $outputArray['status'] = 1;
                            $outputArray['message'] =  trans('apimessages.company_register_tax_details_saved_successfully');
                            $statusCode = 200;
                        }
                        else
                        {
                            DB::rollback();
                            $outputArray['status'] = 0;
                            $outputArray['message'] =  trans('apimessages.default_error_msg');
                            $statusCode = 200;
                        }
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
     * Get Company Customs Details
     */
    public function getCompanyCustomsDetails(Request $request)
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
            }
            else
            {
                $checkAuthorization = Helpers::checkUserAuthorization($user->id, Config::get('constant.BOUTIQUE_CUSTOMS'), 'view');
                if($checkAuthorization == '0')
                {
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.unauthorized_access');
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode);
                }
                $getCompanyDetails = Company::find($request->company_id);
                if ($getCompanyDetails && !empty($getCompanyDetails))
                {
                    $mainArray = [];
                    $mainArray['company_name'] = $getCompanyDetails->custom_company_name;
                    $mainArray['country'] = (!empty($getCompanyDetails->custom_country)) ? $getCompanyDetails->custom_country : '';
                    $mainArray['country_code'] = (!empty($getCompanyDetails->country_code)) ? $getCompanyDetails->country_code : '';
                    $mainArray['main_custom_office'] = (!empty($getCompanyDetails->main_custom_office)) ? $getCompanyDetails->main_custom_office : '';
                    $mainArray['EORI'] = (!empty($getCompanyDetails->EORI)) ? $getCompanyDetails->EORI : '';
                    $mainArray['company_customs_documents'] = [];

                    if(isset($getCompanyDetails->companyCustomDocuments) && $getCompanyDetails->companyCustomDocuments->count() > 0)
                    {
                        foreach ($getCompanyDetails->companyCustomDocuments as $docsKey => $docsValue)
                        {
                            $listArray = [];
                            $companyDocsPath = ((isset($docsValue->company_doc_file_name) && !empty($docsValue->company_doc_file_name)) && Storage::exists($this->companyCustomsDocumentsUploadPath.$docsValue->company_doc_file_name) && Storage::size($this->companyCustomsDocumentsUploadPath.$docsValue->company_doc_file_name) > 0) ? Storage::url($this->companyCustomsDocumentsUploadPath.$docsValue->company_doc_file_name) : '';
//                          $companyDocsPath = '';

                            $listArray['id'] = $docsValue->id;
                            $listArray['company_id'] = $docsValue->company_id;
                            $listArray['company_custom_doc_name'] = (isset($docsValue->company_tax_doc_name) && !empty($docsValue->company_tax_doc_name)) ? $docsValue->company_tax_doc_name : '' ;
                            $listArray['company_doc_file_name'] = (isset($docsValue->company_doc_file_name) && !empty($docsValue->company_doc_file_name)) ? $docsValue->company_doc_file_name : '' ;
                            $listArray['company_custom_doc_url'] = $companyDocsPath;
                            $mainArray['company_customs_documents'][] = $listArray;
                        }
                    }
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.company_customs_details_fetched_successfully');
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
     * Save Company Tax Details
     */
    public function saveCompanyCustomsDetails(Request $request)
    {
        $outputArray = [];
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        try
        {
            DB::beginTransaction();
            $validator = Validator::make($requestData, [
                'company_customs_documents.*' => 'mimes:pdf,doc,docx,jpeg,jpg,png|max:1052400',
                'company_name' => 'required',
                'country' => 'required',
                'country_code' => 'required',
                'main_custom_office'  =>  'required',
                'EORI'  =>  'required'
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
                $getCompanyUserDetails = Company::find($request->company_id);
                if ($getCompanyUserDetails && $getCompanyUserDetails->count())
                {
                    $insertData = [];
                    $insertData['id'] = $requestData['company_id'];
                    $insertData['custom_company_name'] = $requestData['company_name'];
                    $insertData['custom_country'] = $requestData['country'];
                    $insertData['country_code'] = $requestData['country_code'];
                    $insertData['main_custom_office'] = $requestData['main_custom_office'];
                    $insertData['EORI'] = $requestData['EORI'];

                    $checkAuthorization = Helpers::checkUserAuthorization($user->id, Config::get('constant.BOUTIQUE_CUSTOMS'), 'edit');
                    if($checkAuthorization == '0')
                    {
                        $outputArray['status'] = 0;
                        $outputArray['message'] = trans('apimessages.unauthorized_access');
                        $statusCode = 200;
                        return response()->json($outputArray, $statusCode);
                    }
                    else
                    {
                        $saveCompanyDetails = $this->objCompany->insertUpdate($insertData);
                        if($saveCompanyDetails)
                        {
                            if (Input::file('company_customs_documents'))
                            {
                                $fileDocsArray = Input::file('company_customs_documents');
                                if (isset($fileDocsArray) && count($fileDocsArray) > 0 && !empty($fileDocsArray))
                                {
                                    foreach($fileDocsArray as $fileDocKey => $fileDocValue)
                                    {
                                        $prefix = 'company_custom_docs_';
                                        $docsData = Helpers::createDocuments($fileDocValue, $this->companyCustomsDocumentsUploadPath, $prefix, $requestData);
                                        if(isset($docsData) && $docsData['doc_name'] && $docsData['doc_original_name'])
                                        {
                                            CompanyCustomDocuments::firstOrCreate(['company_id' => $requestData['company_id'], 'company_custom_doc_name' => $docsData['doc_original_name'], 'company_doc_file_name' => $docsData['doc_name']]);
                                        }
                                    }
                                }
                            }
                            DB::commit();
                            $outputArray['status'] = 1;
                            $outputArray['message'] =  trans('apimessages.company_register_customs_details_saved_successfully');
                            $statusCode = 200;
                        }
                        else
                        {
                            DB::rollback();
                            $outputArray['status'] = 0;
                            $outputArray['message'] =  trans('apimessages.default_error_msg');
                            $statusCode = 200;
                        }
                    }
                } else {
                    $outputArray['status'] = 1;
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
     * Get Company Permission
     */
    public function getCompanyAllPermission(Request $request)
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
            }
            else
            {
                $companyId = $requestData['company_id'];
                $getAllPermission = $this->objPermissions->getPermissionByCompanyId($companyId);
                if ($getAllPermission && $getAllPermission->count() > 0)
                {
                    $mainArray = [];
                    foreach ($getAllPermission as $perKey => $perValue)
                    {
                        $mainArray[] = $perValue;
                    }
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.company_all_permission_fetched_successfully');
                    $outputArray['data'] = $mainArray;
                    $statusCode = 200;
                } else {
                    $outputArray['status'] = 1;
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
     * Get Company Permission With Roles
     */
    public function getCompanyAllPermissionWithRoles(Request $request)
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
            }
            else
            {
                $companyId = $requestData['company_id'];
                $getRoles = Roles::all();
                $getAllPermission = $this->objPermissions->getPermissionByCompanyId($companyId);

                if ($getRoles && $getRoles->count() > 0 && $getAllPermission && $getAllPermission->count() > 0)
                {
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.company_all_permission_with_roles_fetched_successfully');
                    $outputArray['data'] = [];
                    $statusCode = 200;
                    foreach ($getRoles as $roleKey => $roleValue)
                    {
                        $outputArray['data'][$roleValue->slug] = array();
                        $outputArray['data'][$roleValue->slug]['id'] = $roleValue->id;
                        $outputArray['data'][$roleValue->slug]['permissions'] = array();
                        foreach($getAllPermission as $perKey => $perValue)
                        {
                            $editPermission = Permissions::whereRaw("FIND_IN_SET(".$roleValue->id.", default_edit_for)")->where('id', $perValue->id)->first();
                            $viewPermission = Permissions::whereRaw("FIND_IN_SET(".$roleValue->id.", default_view_for)")->where('id', $perValue->id)->first();
                            $outputArray['data'][$roleValue->slug]['permissions'][$perKey]['id'] = $perValue->id;
                            $outputArray['data'][$roleValue->slug]['permissions'][$perKey]['company_id'] = $perValue->company_id;
                            $outputArray['data'][$roleValue->slug]['permissions'][$perKey]['pageName'] = $perValue->slug;
                            $outputArray['data'][$roleValue->slug]['permissions'][$perKey]['labelName'] = $perValue->label_name;
                            $outputArray['data'][$roleValue->slug]['permissions'][$perKey]['edit'] = (!empty($editPermission)) ? 1 : 0;
                            $outputArray['data'][$roleValue->slug]['permissions'][$perKey]['view'] = (!empty($viewPermission)) ? 1 : 0;
                        }
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
     * Set Read Notification
     */
    public function setReadNotification(Request $request)
    {
        $outputArray = [];
	$user = JWTAuth::parseToken()->authenticate();
	$requestData = $request->all();
        try
        {
            $validator = Validator::make($requestData,
                [
                    'notification_id' => 'required',
                ]);
            if ($validator->fails())
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray,$statusCode);
            }
            $getNotificationData = Notifications::find($requestData['notification_id']);
            if($getNotificationData && !empty($getNotificationData))
            {
                if(!empty($getNotificationData->read_by))
                {
                    $readByIdArray = explode(',', $getNotificationData->read_by);
                    if(!empty($readByIdArray) && !in_array($user->id, $readByIdArray))
                    {
                        $getNotificationData->read_by = $getNotificationData->read_by.','.$user->id;
                    }
                }
                else
                {
                    $getNotificationData->read_by = $user->id;
                }
                $response = $getNotificationData->save();
                if($response)
                {
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.notification_read_successfully');
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
                $outputArray['message'] = trans('apimessages.notification_not_found');
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
     * Get Read Notification
     */
    public function getReadNotification(Request $request)
    {
        $outputArray = [];
	$user = JWTAuth::parseToken()->authenticate();
	$requestData = $request->all();
        try
        {
            $filters = [];
            $filters['read_by'] = $user->id;
            $filters['notification_page'] = $request->notification_page; 
            if(isset($request->product_id) && !empty($request->product_id))
            {
                $rules = [
                    'product_id' => 'required'
                ];
                $filters['product_id'] = $request->product_id;
            }
            if(isset($request->company_id) && !empty($request->company_id))
            {
                $rules = [
                    'company_id' => 'required'
                ];
                $filters['company_id'] = $request->company_id;
            }
            if(isset($request->store_id) && !empty($request->store_id))
            {
                $rules = [
                    'store_id' => 'required'
                ];
                $filters['company_id'] = $request->company_id;
//              $filters['notification_page'] = $request->notification_page;
            }
            $rules = [
                'notification_page' => 'required',
            ];
            $validator = Validator::make($requestData, $rules);
            if ($validator->fails())
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray,$statusCode);
            }
            $getReadNotification = $this->objNotifications->getAll($filters);
            if($getReadNotification && !empty($getReadNotification) && $getReadNotification->count() > 0)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.get_read_notification_successfully');
                $getReadThreadsCount = $getReadNotification->count();
                $outputArray['read_count'] = $getReadThreadsCount;
                $statusCode = 200;
                $outputArray['data'] = array();
                foreach ($getReadNotification as $readKey => $readValue)
                {
                    $getUserRoles= Helpers::getUserRole($readValue->created_by);
                    $outputArray['data'][$readKey] = $readValue;
                    $outputArray['data'][$readKey]['notification_type'] = ($getUserRoles && !empty($getUserRoles) && !empty($getUserRoles->role_id) && $getUserRoles->role_id == 1) ? 1 : 0;

                }
            }
            else
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $outputArray['read_count'] = 0;
                $statusCode = 200;
                $outputArray['data'] =[];
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
     * Get Un Read Notification
     */
    public function getUnReadNotification(Request $request)
    {
        $outputArray = [];
	$user = JWTAuth::parseToken()->authenticate();
	$requestData = $request->all();
        try
        {
            $filters = [];
            $filters['unread_by'] = $user->id;
            $filters['notification_page'] = $request->notification_page;
            if(isset($request->product_id) && !empty($request->product_id))
            {
                $rules = [
                    'product_id' => 'required'
                ];
                $filters['product_id'] = $request->product_id;
            }
            if(isset($request->company_id) && !empty($request->company_id))
            {
                $rules = [
                    'company_id' => 'required'
                ];
                $filters['company_id'] = $request->company_id;
//                $filters['notification_page'] = $request->notification_page;
            }
            elseif(isset($request->store_id) && !empty($request->store_id))
            {
                $rules = [
                    'store_id' => 'required'
                ];
                $filters['company_id'] = $request->company_id;
//                $filters['notification_page'] = $request->notification_page;
            }
            $rules = [
                'notification_page' => 'required',
            ];
            $validator = Validator::make($requestData, $rules);
            if ($validator->fails())
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray,$statusCode);
            }
            $getUnReadNotification = $this->objNotifications->getAll($filters);
            if($getUnReadNotification && !empty($getUnReadNotification) && $getUnReadNotification->count() > 0)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.get_unread_notification_successfully');
                $getReadThreadsCount = $getUnReadNotification->count();
                $outputArray['unread_count'] = $getReadThreadsCount;
                $statusCode = 200;
                $outputArray['data'] = array();
                foreach ($getUnReadNotification as $readKey => $readValue)
                {
                    $getUserRoles= Helpers::getUserRole($readValue->created_by);
                    $outputArray['data'][$readKey] = $readValue;
                    $outputArray['data'][$readKey]['notification_type'] = ($getUserRoles && !empty($getUserRoles) && !empty($getUserRoles->role_id) && $getUserRoles->role_id == 1) ? 1 : 0;
                }
            }
            else
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $outputArray['unread_count'] = 0;
                $statusCode = 200;
                $outputArray['data'] =[];
            }
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }

//  Create a new Company user.
    public function saveCompanyUserDetail(Request $request)
    {
        try{

            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id,Config::get('constant.BOUTIQUE_USER'),'edit');
            if($checkAuthorization == '0'){
                return response()->json([
                    'status' => 0,
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }

            DB::beginTransaction();

            $rules = [
                'action' => 'required',
                'name' => 'required',
                'password' => 'nullable|min:8|max:20',
                'position' => 'required',
                'role_id' => 'required',
                'user_name' => 'required',
                'user_unique_id' => [
                        'required',
                        Rule::unique('users', 'user_unique_id')->ignore($request->id),
                    ],
                'email' => [
                        'required',
                        'email',
                        'max:40',
                        Rule::unique('users', 'email')->ignore($request->id),
                    ],
                'user_image' => 'image|mimes:jpeg,png,jpg|max:5120',
                'user_permission' => 'required',
                'company_id' => 'required'
            ];
            if($request->role_id == 5)
            {
                $rules['custom_user_role'] = 'required';
            }
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ],400);
            }

            $userData = [];
            $userOldImage = "";

            if($request->action == Config::get('constant.API_ACTION_UPDATE'))
            {
                if($request->id != 0){
                    $userOldData = $this->objUser->find($request->id);
                    if(isset($userOldData) && count($userOldData)>0){
                        $userData['id'] = $userOldData->id;
                        $userOldImage = $userOldData->photo;
                    }
                    else{
                        DB::rollback();
                        return response()->json([
                            'status' => '0',
                            'message' => trans('apimessages.company_user_not_found')
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

            $userData['name'] = $request->name;
            $userData['user_name'] = $request->user_name;

            if(isset($request->password)){
                $userData['password'] = bcrypt($request->password);
            }

            $userData['position'] = $request->position;
            $userData['user_unique_id'] = $request->user_unique_id;
            $userData['email'] = $request->email;
            if(isset($request->custom_user_role) && !empty($request->custom_user_role))
            {
                $userData['custom_role_name'] = $request->custom_user_role;
            }
            

            if(Input::file('user_image')){
                $file = Input::file('user_image');
                if (!empty($file)) {
                    $userData['photo'] = Helpers::createUpdateImage($file,$this->userOriginalImageUploadPath, $this->userThumbImageUploadPath, $this->userThumbImageHeight, $userData, $userOldImage );
                }
            }

            $user = $this->objUser->insertUpdate($userData);

            if($user)
            {
                //Company User insert
                $company_user = [];

                $oldCompanyUser = $this->objCompanyUser->getCompanyByUserId($user->id);

                if($oldCompanyUser){
                    $company_user['id'] = $oldCompanyUser->id;
                }

                $company_user['user_id'] = $user->id;
                $company_user['company_id'] = $request->company_id;

                $companyUser = $this->objCompanyUser->insertUpdate($company_user);

                //User Roles insert
                $user_roles = [];

                $oldUserRoles = $this->objUserRoles->getUserRolesByUserId($user->id);
                if($oldUserRoles){
                    foreach ($oldUserRoles as $key => $value) {
                        $user_roles['id'] = $value->id;
                    }
                }

                $user_roles['user_id'] = $user->id;
                $user_roles['role_id'] = $request->role_id;

                $userRoles = $this->objUserRoles->insertUpdate($user_roles);

                $edit = [];
                $view = [];
                foreach (json_decode(Input::get('user_permission'), true) as $key => $value)
                {
                    if($value['edit'] == "1"){
                        $edit[] = $value['id'];
                    }
                    if($value['view'] == "1"){
                        $view[] = $value['id'];
                    }
                }

                $user_permission = [];

                $oldUserPermission = $this->objUserPermission->getAllUserPermissions($user->id);
                if($oldUserPermission){
                    $user_permission['id'] = $oldUserPermission->id;
                }

                //User Permission insert
                $user_permission['user_id'] = $user->id;
                $user_permission['edit'] = implode($edit, ",");
                $user_permission['view'] = implode($view, ",");
                $userPermission = $this->objUserPermission->insertUpdate($user_permission);
            }
            else
            {
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.error_adding_user_details'),
                    'code' => $e->getStatusCode()
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => '1',
                'message' => trans('apimessages.company_user_detail_added_successfully'),
                'data' => $user
            ]);

        }
        catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_adding_user_details'),
                'code' => $e->getStatusCode()
            ]);
        }
    }

    /**
     * Delete Company. User
     */
    public function deleteCompanyUserDetail(Request $request)
    {
        try{

            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id,Config::get('constant.BOUTIQUE_USER'),'edit');
            if($checkAuthorization == '0'){
                return response()->json([
                            'status' => 0,
                            'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }

            DB::beginTransaction();

            $rules = [
                'user_id' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ],400);
            }

            $userData = $this->objUser->find($request->user_id);

            if(isset($userData) && count($userData)>0)
            {
                $this->objUser->deleteUser($request->user_id);
            }
            else{
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.record_id_not_specified')
                ],400);
            }

            DB::commit();

            return response()->json([
                'status' => '1',
                'message' => trans('apimessages.user_deleted_successfully'),
            ]);
        }
        catch (Exception $e){
            DB::rollback();
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_deleting_user_details'),
                'code' => $e->getStatusCode()
            ]);
        }
    }
}
