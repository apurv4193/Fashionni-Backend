<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\User;
use App\UsersChat;
use App\Company;
use App\Store;
use App\CompanyUser;
use App\CompanyDocuments;
use App\CompanyTaxDocuments;
use App\UserRoles;
use App\Roles;
use App\Permissions;
use App\UserPermission;
use \stdClass;
use JWTAuth;
use JWTAuthException;
use Config;
use Validator;
use DB;
use Helpers;
use Storage;
use Input;
use Auth;
use File;
use Image;

class SuperCompanyController extends Controller
{
    public function __construct() {

        $this->objUser = new User();
        $this->objUserRoles = new UserRoles();
        $this->objCompany = new Company();
        $this->objCompanyUser = new CompanyUser();
        $this->objUsersChat = new UsersChat();
        $this->objCompanyDocuments = new CompanyDocuments();
        $this->objCompanyTaxDocuments = new CompanyTaxDocuments();
        $this->objStore = new Store();        
        $this->objRoles = new Roles();
        $this->objPermissions = new Permissions();
        $this->objUserPermission = new UserPermission();

        $this->companyDocumentsUploadPath = Config::get('constant.COMPANY_DOCUMENTS_UPLOAD_PATH');
        $this->companyTaxDocumentsUploadPath = Config::get('constant.COMPANY_TAX_DOCUMENTS_UPLOAD_PATH');
        $this->companyOriginalImageUploadPath = Config::get('constant.COMPANY_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->companyThumbImageUploadPath = Config::get('constant.COMPANY_THUMB_IMAGE_UPLOAD_PATH');
        $this->companyThumbImageHeight = Config::get('constant.COMPANY_THUMB_IMAGE_HEIGHT');
        $this->companyThumbImageWidth = Config::get('constant.COMPANY_THUMB_IMAGE_WIDTH');

        $this->storeOriginalImageUploadPath = Config::get('constant.STORE_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->storeThumbImageUploadPath = Config::get('constant.STORE_THUMB_IMAGE_UPLOAD_PATH');
        $this->storeThumbImageHeight = Config::get('constant.STORE_THUMB_IMAGE_HEIGHT');
        $this->storeThumbImageWidth = Config::get('constant.STORE_THUMB_IMAGE_WIDTH');
        
        $this->userOriginalImageUploadPath = Config::get('constant.USER_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->userThumbImageUploadPath = Config::get('constant.USER_THUMB_IMAGE_UPLOAD_PATH');
        $this->userThumbImageHeight = Config::get('constant.USER_THUMB_IMAGE_HEIGHT');
        $this->userThumbImageWidth = Config::get('constant.USER_THUMB_IMAGE_WIDTH');
        
        $this->defaultImage = Config::get('constant.DEFAULT_IMAGE_PATH');
        $this->defaultPlusImage = Config::get('constant.DEFAULT_PLUS_IMAGE_PATH');
    }

    /**
     * Save super sub admin User
     */    
    public function saveSuperSubAdminUser(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $userRoleSlug = '';
        $userRoles = $this->objUserRoles->getUserRolesByUserId(Auth::user()->id);
        if(isset($userRoles) && !empty($userRoles))
        {
            $userRoleSlug = $userRoles[0]->roles->slug;
        }
        if($user && !empty($user) && $user->id != 1 && $userRoleSlug != Config::get('constant.SUPER_ADMIN_SLUG'))
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
                'action' => 'required',
                'user_name' => 'required',
                'name' => 'required',
                'position' => 'required',                
//                'user_unique_id' => [
//                    'required',
//                    Rule::unique('users', 'user_unique_id')->ignore($request->id),
//                ],
                'user_unique_id' => 'required|unique:users,user_unique_id,'.$request->id.',id,deleted_at,NULL',
                'email' => 'required|email|max:40|unique:users,email,'.$request->id.',id,deleted_at,NULL',
//                'email' => [
//                    'required',
//                    'email',
//                    'max:40',
//                    Rule::unique('users', 'email')->ignore($request->id),
//                ],
                'password' => 'nullable|min:8|max:20',
                'user_image' => 'image|mimes:jpeg,jpg,bmp,png,gif|max:5120',
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
                        
            $userData = [];
            $userOldImage = '';
            if(isset($request->action) && $request->action == Config::get('constant.API_ACTION_UPDATE'))
            {
                if(isset($request->id) && $request->id > 0)
                {
                    $userOldData = $this->objUser->find($request->id);
                    if(isset($userOldData) && !empty($userOldData))
                    {
                        $userData['id'] = $userOldData->id;
                        $userOldImage = $userOldData->photo;
                    }
                    else
                    {
                        DB::rollback();
                        return response()->json([
                            'status' => 0,
                            'message' => trans('apimessages.super_sub_user_not_found')
                        ], 400);
                    }
                }
                else
                {
                    DB::rollback();
                    return response()->json([
                        'status' => 0,
                        'message' => trans('apimessages.record_id_not_specified')
                    ], 400);
                }
            }
            $userData['name'] = $request->name;
            $userData['user_name'] = $request->user_name;
            if(isset($request->password))
            {
                $userData['password'] = bcrypt($request->password);
            }
            $userData['position'] = $request->position;
            $userData['user_unique_id'] = $request->user_unique_id;
            $userData['email'] = $request->email;
            if(Input::file('user_image'))
            {
                $file = Input::file('user_image');
                if (!empty($file)) 
                {
                    $userData['photo'] = Helpers::createUpdateImage($file,$this->userOriginalImageUploadPath, $this->userThumbImageUploadPath, $this->userThumbImageHeight, $userData, $userOldImage);
                }
            }
           
            $userData = $this->objUser->insertUpdate($userData);
            if($userData)
            {
                $userData->user_image = (!empty($userData->photo) && $userData->photo != '' && Storage::exists($this->userOriginalImageUploadPath.$userData->photo) && Storage::size($this->userOriginalImageUploadPath.$userData->photo) > 0) ? Storage::url($this->userOriginalImageUploadPath.$userData->photo) : url($this->defaultImage);
                
                // User Roles insert
                $user_roles = [];
                $oldUserRoles = $this->objUserRoles->getUserRolesByUserId($userData->id);
                if($oldUserRoles)
                {
                    foreach ($oldUserRoles as $key => $value) 
                    {
                        $user_roles['id'] = $value->id;
                    }
                }
                $user_roles['user_id'] = $userData->id;
                $user_roles['role_id'] = 1;
                $userRoles = $this->objUserRoles->insertUpdate($user_roles);
            }
            else
            {
                DB::rollback();
                return response()->json([
                    'status' => 0,
                    'message' => trans('apimessages.error_adding_user_details'),
                    'code' => $e->getStatusCode()
                ]);
            }
            DB::commit();
            $outputArray['status'] = 1;
            $outputArray['message'] = (isset($request->id) && $request->id > 0) ? trans('apimessages.user_details_updated_successfully') : trans('apimessages.user_details_added_successfully');
            $outputArray['data'] = $userData;
            $statusCode = 200; 
            
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }
    
    /**
     * get all Super sub admin Users
     */    
    public function getSuperSubAdminUsers(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        $outputArray = [];
        try
        {
            $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 0;
            $paginate = (!empty($pageNo) && $pageNo > 0) ? true : false;
            $userRoleSlug = '';
            $userRoles = $this->objUserRoles->getUserRolesByUserId(Auth::user()->id);
            if(isset($userRoles) && !empty($userRoles))
            {
                $userRoleSlug = $userRoles[0]->roles->slug;
            }
            if($user && !empty($user) && $user->id != 1 && $userRoleSlug != Config::get('constant.SUPER_ADMIN_SLUG'))
            {
                return response()->json([
                    'status' => 0,
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }            
            $filters = [];
            $filters['user_id'] = 1;
            $filters['role_id'] = 1;                    
            $getSuperAdminUsers = $this->objUserRoles->getSuperAdminUsers($filters, $paginate);            
            if($getSuperAdminUsers && !empty($getSuperAdminUsers) && $getSuperAdminUsers->count() > 0) 
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.super_sub_admin_users_listing_fetched_successfully');
                $outputArray['data'] = array();
                $statusCode = 200; 
                $mainArray = [];
                foreach ($getSuperAdminUsers as $key => $superAdminUser)
                {
                    if(isset($superAdminUser->user) && $superAdminUser->user && !empty($superAdminUser->user))
                    {
                        $superAdminUsers = $superAdminUser->user;
                        $superAdminUsers->role_id = $superAdminUser->role_id;
                        $superAdminUsers->user_image = (!empty($superAdminUsers->photo) && $superAdminUsers->photo != '' && Storage::exists($this->userOriginalImageUploadPath.$superAdminUsers->photo) && Storage::size($this->userOriginalImageUploadPath.$superAdminUsers->photo) > 0) ? Storage::url($this->userOriginalImageUploadPath.$superAdminUsers->photo) : url($this->defaultImage);
                        $superAdminUsers->photo = $superAdminUsers->photo;
                        $mainArray[] = $superAdminUsers;
                    }
                }
                $outputArray['data'] = $mainArray;
            }
            else
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.super_admin_users_not_found');
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
     * Register new company and stores.
     */
    public function superCompanyRegister(Request $request)
    {
        try
        {
            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id,Config::get('constant.BOUTIQUE'),'edit');
            if($checkAuthorization == '0')
            {
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.unauthorized_access')
                ], 200);
            }

            DB::beginTransaction();
            $this->token = JWTAuth::getToken();

            $rules = [
                'password' => 'nullable|min:8|max:20',
                'company_name' => ['required', 'max:50'],
                'address' => 'required',
                'postal_code' => 'required|min:3',
                'city' => 'required',
                'state' => 'required',
                'country' => 'required',
                'store' => 'required',
            ];
            
            $request['user_unique_id'] = $request->company_unique_id;

            if($request->action == Config::get('constant.API_ACTION_CREATE'))
            {
                $rules['email'] = [
                        'required',
                        'email',
                        'max:40',
                        Rule::unique('users', 'email'),
                        Rule::unique('company', 'company_email')
                    ];                
                
                $rules['company_unique_id'] = 'required|unique:company,company_unique_id,NULL,id,deleted_at,NULL';
                $rules['user_unique_id'] = 'required|unique:users,user_unique_id,NULL,id,deleted_at,NULL';
                $rules['company_image'] = 'required|image|mimes:jpeg,png,jpg|max:5120';
                
//                $rules['company_unique_id'] = [
//                        'required',
////                      Rule::unique('company', 'company_unique_id')
//                    ];
//                $rules['user_unique_id'] = [
//                        'required',
////                      Rule::unique('users', 'user_unique_id')
//                    ];
            }
            elseif($request->action == Config::get('constant.API_ACTION_UPDATE'))
            {
                $companyUserData = $this->objCompanyUser->getCompanyDefaultByUserId($request->id);
                $rules['email'] = [
                    'required',
                    'email',
                    'max:40',
                    Rule::unique('users', 'email')->ignore($companyUserData->getUser['id']),
                    Rule::unique('company', 'company_email')->ignore($request->id)
                ];
                
                $rules['company_unique_id'] = 'required|unique:company,company_unique_id,'.$request->id.',id,deleted_at,NULL';
                $rules['user_unique_id'] = 'required|unique:users,user_unique_id,'.$companyUserData->getUser['id'].',id,deleted_at,NULL';
                
//              $rules['company_unique_id'] = [
//                        'required|unique:company,company_unique_id,'.$request->id.',id,deleted_at,NULL',
////                       Rule::unique('company', 'company_unique_id')->ignore($request->id)
//                    ];
                
//              $rules['user_unique_id'] = [
//                       'required|unique:users,user_unique_id,'.$companyUserData->getUser['id'].',id,deleted_at,NULL',
////                      Rule::unique('users', 'user_unique_id')->ignore($companyUserData->getUser['id'])
//                    ];
            }

            // foreach($request->get('store') as $key => $val)
            // {
            //     $rules['store.'.$key.'.store_name'] = ['required', 'max:50', 'regex:/^[a-zA-Z0-9-_\.\/]+$/'];
            //     $rules['store.'.$key.'.short_name'] = ['required', 'max:50', 'regex:/^[a-zA-Z0-9-_\.\/]+$/'];
            //     $rules['store.'.$key.'.address'] = 'required';
            //     $rules['store.'.$key.'.postal_code'] = ['required', 'regex:/^\d{5}(?:[-\s]\d{4})?$/'];
            //     $rules['store.'.$key.'.city'] = 'required';
            //     $rules['store.'.$key.'.state'] = 'required';
            //     $rules['store.'.$key.'.country'] = 'required';
            // }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ],400);
            }

            //company insert
            $companyData = [];
            $companyOldImage = "";

            if($request->action == Config::get('constant.API_ACTION_UPDATE'))
            {
                if($request->id != 0){
                    $companyOldData = $this->objCompany->find($request->id);
                    if(isset($companyOldData) && count($companyOldData) > 0){
                        $companyData['id'] = $companyOldData->id;
                        $companyOldImage = $companyOldData->company_image;
                    }
                    else
                    {
                        return response()->json([
                            'status' => '0',
                            'message' => trans('apimessages.company_not_found')
                        ],400);
                    }
                }
                else
                {
                    DB::rollback();
                    return response()->json([
                        'status' => '0',
                        'message' => trans('apimessages.record_id_not_specified')
                    ],400);
                }
            }

            $companyData['company_name'] = $request->company_name;
            $companyData['company_email'] = $request->email;
            $companyData['postal_code'] = $request->postal_code;
            $companyData['address'] = $request->address;
            $companyData['city'] = $request->city;
            $companyData['state'] = $request->state;
            $companyData['country'] = $request->country;
            $companyData['company_unique_id'] = $request->company_unique_id;

            if($request->action == Config::get('constant.API_ACTION_CREATE'))
            {
                $companyData['company_slug'] = Helpers::createSlug($request->company_name);
//              for generate Unique slug
                $checkCompanySlug = $this->objCompany->getCompanyBySlug($companyData['company_slug']);

                if(count($checkCompanySlug)>0){
                    $companyData['company_slug'] = $companyData['company_slug'].'_'.mt_rand();
                }
            }

            if (Input::file()) {
                $file = Input::file('company_image');
                if (!empty($file)) {
                    $companyData['company_image'] = Helpers::createUpdateImage($file,$this->companyOriginalImageUploadPath, $this->companyThumbImageUploadPath, $this->companyThumbImageHeight, $companyData, $companyOldImage );
                }
            }

            $company = $this->objCompany->insertUpdate($companyData);

            if($request->action == Config::get('constant.API_ACTION_CREATE'))
            {
                //user insert
                $userData = [];
                $userData['name'] = $request->company_name;
                $userData['email'] = $request->email;
                $userData['position'] = Config::get('constant.ADMIN_SLUG');
                $userData['password'] = bcrypt($request->password);
                $userData['random_number'] = Helpers::generateRandomNoString(10);
                $userData['user_unique_id'] = $request->user_unique_id;

                $user = $this->objUser->insertUpdate($userData);
                                
                //company_user insert
                $company_user = [];
                $company_user['user_id'] = $user->id;
                $company_user['company_id'] = $company->id;
                $company_user['default'] = '1';

                $companyUser = $this->objCompanyUser->insertUpdate($company_user);

                //User Roles insert
                $user_roles = [];
                $user_roles['user_id'] = $user->id;
                $user_roles['role_id'] = $this->objRoles->getRoleBySlug(Config::get('constant.ADMIN_SLUG'))->id;

                $userRoles = $this->objUserRoles->insertUpdate($user_roles);

                //User Permission insert
                $user_permission = [];
                $user_permission['user_id'] = $user->id;
//              $user_permission['edit'] = '1,2,3,4,5,6,7';
//              $user_permission['view'] = '1,2,3,4,5,6,7';
                $user_permission['edit'] = Helpers::getDefaultPermission();
                $user_permission['view'] = Helpers::getDefaultPermission();
                $userPermission = $this->objUserPermission->insertUpdate($user_permission);
                
                $userRoles = $this->objUserRoles->getUserRolesByUserId(Auth::user()->id);
                $userRoleSlug = "";
                if($userRoles && !empty($userRoles))
                {
                    $userRoleSlug = $userRoles[0]->roles->slug;
                }                
//              Default user chat entry in users_chat table
                if(isset($user) && !empty($user) && $userRoleSlug == Config::get('constant.SUPER_ADMIN_SLUG'))
                {
                    $usersChat = [];
                    $usersChat['user_id'] = $user->id;
                    $usersChat['created_by'] = Auth::user()->id;
                    $usersChat['company_id'] = $company->id;
                    $usersChat['is_default'] = 1;
                    $usersChat = $this->objUsersChat->insertUpdate($usersChat);
                }
            }
            
            if($request->action == Config::get('constant.API_ACTION_UPDATE'))
            { 
                // user update
                $userData = [];
                $userData['id'] = $companyUserData->getUser['id'];
                $userData['name'] = $request->company_name;
                $userData['email'] = $request->email;
                $userData['user_unique_id'] = $request->user_unique_id;
                // $userData['position'] = "-";
                if(isset($request->password) && $request->password != "") 
                {
                    $userData['password'] = bcrypt($request->password);
                }
                $user = $this->objUser->insertUpdate($userData);
            }

            //  store insert{"store_name":"s1","short_name":"s","address":"iscon","postal_code":123456,"city":"ahmedabad","state":"gujrat","country":"india"}
            if(isset($request->store) && $request->store != ""){
                foreach (json_decode($request->store, true) as $key => $value)
                {
                    $data = $value;
                    $storeData = [];
                    $storeOldImage = "";

                    if($data['action'] == Config::get('constant.API_ACTION_UPDATE'))
                    {
                        if($data['id'] != 0){
                            $storeOldData = $this->objStore->find($data['id']);
                            if(isset($storeOldData) && count($storeOldData)>0){
                                $storeData['id'] = $storeOldData->id;
                                $storeOldImage = $storeOldData->store_image;
                                $storeSlug = $storeOldData->store_slug;
                            }
                            else{
                                DB::rollback();
                                return response()->json([
                                    'status' => '0',
                                    'message' => trans('apimessages.store_not_found')
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
                    elseif($data['action'] == Config::get('constant.API_ACTION_DELETE'))
                    {
                        if($data['id'] != 0){
                            $checkStoreCount = $this->objStore->getStoreByCompanyId($company->id);
                            if(count($checkStoreCount)>1){
                                $storeData = $this->objStore->deleteStore($data['id']);
                                continue;
                            }
                            else{
                                DB::rollback();
                                return response()->json([
                                    'status' => '0',
                                    'message' => trans('apimessages.last_store_can_not_delete_message')
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

                    $storeData['company_id'] = $company->id;
                    $storeData['store_name'] = $data['store_name'];
                    $storeData['short_name'] = $data['short_name'];
                    $storeData['address'] = $data['address'];
                    $storeData['postal_code'] = $data['postal_code'];
                    $storeData['city'] = $data['city'];
                    $storeData['country'] = $data['country'];
                    $storeData['state'] = $data['state'];
                    $storeData['store_lat'] = (isset($data['store_lat']) && !empty($data['store_lat'])) ? $data['store_lat'] : NULL;
                    $storeData['store_lng'] = (isset($data['store_lng']) && !empty($data['store_lng'])) ? $data['store_lng'] : NULL;;

                    if($data['action'] == Config::get('constant.API_ACTION_CREATE'))
                    {
                        $storeData['store_slug'] = Helpers::createSlug($data['store_name']);
                        
                        // for generate Unique slug
                        $checkStoreSlug = $this->objStore->getStoreBySlug($storeData['store_slug']);

                        if(count($checkStoreSlug)>0){
                            $storeData['store_slug'] = $storeData['store_slug'].'_'.mt_rand();
                        }
                    }

                    if(isset($data['store_image']) && $data['store_image'] != ""){
                        $file = Input::file($data['store_image']);
                        if (!empty($file)) {
                            $storeData['store_image'] = Helpers::createUpdateImage($file,$this->storeOriginalImageUploadPath, $this->storeThumbImageUploadPath, $this->storeThumbImageHeight, $storeData, $storeOldImage );
                        }
                    }

                    $store = $this->objStore->insertUpdate($storeData);

                    $userPermissionData = $this->objUserPermission->getAllUserPermissions($user->id);
                    
                    $edit = explode(",", $userPermissionData->edit);
                    $view = explode( ",", $userPermissionData->view);

                    if($data['action'] == Config::get('constant.API_ACTION_CREATE'))
                    {
                        //permission insert
                        $permissionData = [];
                        $permissionData['slug'] = $storeData['store_slug'];
                        $permissionData['label_name'] = $storeData['store_name'];
                        $permissionData['company_id'] = $storeData['company_id'];
                        $permissionData['default_edit_for'] = '1,2,3';
                        $permissionData['default_view_for'] = '1,2,3';

                        $permission = $this->objPermissions->insertUpdate($permissionData);
                        $edit[] = $permission->id;
                        $view[] = $permission->id;

                    }

                    if($data['action'] == Config::get('constant.API_ACTION_UPDATE'))
                    {
                        $permissionOldData = $this->objPermissions->getPermissionBySlug($storeSlug);

                        //permission insert
                        if(isset($permissionOldData) && count($permissionOldData)>0){                    
                            $permissionData = [];
                            $permissionData['id'] = $permissionOldData->id;
                            $permissionData['label_name'] = $storeData['store_name'];

                            $permission = $this->objPermissions->insertUpdate($permissionData);
                        }
                    }

                    //User Permission update
                    $user_permission = [];
                    $user_permission['id'] = $userPermissionData->id;
                    $user_permission['user_id'] = $user->id;
                    $user_permission['edit'] = implode($edit, ",");
                    $user_permission['view'] = implode($view, ",");

                    $userPermission = $this->objUserPermission->insertUpdate($user_permission);
                }
            }
            else{
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.company_must_have_atleast_one_store'),
                    'code' => $e->getStatusCode()
                ]);
            }

            DB::commit();
            
            if($request->action == Config::get('constant.API_ACTION_CREATE'))
            {
                $message = trans('apimessages.company_created_successfully');
            }
            if($request->action == Config::get('constant.API_ACTION_UPDATE'))
            {
                $message = trans('apimessages.company_updated_successfully');
            }
            
            return response()->json([
                'status' => '1',
                'message' => $message,
                'data' => [
                    'companyDetail' => $company
                ]
            ]);
        }
        catch (Exception $e){
            DB::rollback();
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_registering_user'),
                'code' => $e->getStatusCode()
            ]);
        }
    }


    //  get Company List for Super Admin
    public function getSuperCompanyList(Request $request)
    {
        try
        {
            $userRoles = $this->objUserRoles->getUserRole($request->user()->id);
            if($userRoles != 1)
            {
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.unauthorized_access')
                ],400);
            }
            $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 0;
            $paginate = (!empty($pageNo) && $pageNo > 0) ? true : false;
            
            $filters = [];
            if(isset($request->search_key) && !empty($request->search_key))
            {
                $filters['search_key'] = $request->search_key;
            }
            
            $companyDetail = $this->objCompany->getCompanyAllDetail($filters, $paginate);
            
            if($companyDetail && $companyDetail->count() > 0 && !empty($companyDetail))
            {
                foreach ($companyDetail as $key => $_companyDetail)
                {        
                    $_companyDetail->company_image = ($_companyDetail->company_image != NULL && $_companyDetail->company_image != '' && Storage::exists($this->companyThumbImageUploadPath.$_companyDetail->company_image) && Storage::size($this->companyThumbImageUploadPath.$_companyDetail->company_image) > 0) ? Storage::url($this->companyThumbImageUploadPath . $_companyDetail->company_image) : url($this->defaultPlusImage);
                }
                return response()->json([
                    'status' => '1',
                    'message' => trans('apimessages.super_admin_company_list'),
                    'data' => $companyDetail
                ],200);
            }
            else
            {
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.company_not_found_listing'),
                    'data' => []
                ],200);
            }
        } catch (Exception $e)
        {
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_company'),
                'code' => $e->getStatusCode()
            ]);
        }
    }

    //  get Company details by id for Super Admin
    public function getSuperCompanyDetailById (Request $request)
    {
        try
        {
            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id,Config::get('constant.BOUTIQUE'),'view');
            if($checkAuthorization == '0'){
                return response()->json([
                            'status' => '0',
                            'message' => trans('apimessages.unauthorized_access')
                ], 200);
            }

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
                ],400);
            }

            $companyDetail = $this->objCompany->getCompanyDetail($request->company_id);

            if(isset($companyDetail) && !empty($companyDetail))
            {

                $companyDetail->company_image = ($companyDetail->company_image != NULL && $companyDetail->company_image != '' && Storage::exists($this->companyThumbImageUploadPath.$companyDetail->company_image) && Storage::size($this->companyThumbImageUploadPath.$companyDetail->company_image) > 0) ? Storage::url($this->companyThumbImageUploadPath . $companyDetail->company_image) : url($this->defaultPlusImage);

                foreach ($companyDetail->store as $_store) {
                    $_store->store_image = ($_store->store_image != NULL && $_store->store_image != '' && Storage::exists($this->storeThumbImageUploadPath.$_store->store_image) && Storage::size($this->storeThumbImageUploadPath.$_store->store_image) > 0) ? Storage::url($this->storeThumbImageUploadPath . $_store->store_image) : '';
                }

                return response()->json([
                'status' => '1',
                'message' => trans('apimessages.company_details'),
                'data' =>  $companyDetail
                ],200);
            }
            else
            {
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.company_details_not_found'),
                ],400);
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
     * Delete Company.
     */
    public function superCompanyDelete(Request $request)
    {
        try{

            $userRoles = $this->objUserRoles->getUserRolesByUserId(Auth::user()->id);
            foreach ($userRoles as $key => $value)
            {
                if($value->roles->slug == Config::get('constant.SUPER_ADMIN_SLUG'))
                {
                    break;
                }
                elseif($value->roles->slug != Config::get('constant.SUPER_ADMIN_SLUG'))
                {
                    return response()->json([
                                'status' => 0,
                                'message' => trans('apimessages.unauthorized_access')
                    ], 400);
                }
            }

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
                ],400);
            }

            $companyData = $this->objCompany->find($request->company_id);

            if(isset($companyData) && count($companyData)>0)
            {
                //user delete
                $this->objUser->deleteUserByEmail($companyData->company_email);

                //company_user delete
                $this->objCompanyUser->deleteCompanyAllUserByCompanyId($companyData->id);

                //Company delete
                $companyData->delete();
            }

            DB::commit();

            return response()->json([
                'status' => '1',
                'message' => trans('apimessages.company_deleted_successfully'),
            ]);
        }
        catch (Exception $e){
            DB::rollback();
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_registering_user'),
                'code' => $e->getStatusCode()
            ]);
        }
    }
}

