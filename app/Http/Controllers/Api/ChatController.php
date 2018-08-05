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
use App\UsersChat;
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

class ChatController extends Controller
{
    public function __construct()
    {
        $this->objUser = new User();
        $this->objUserRoles = new UserRoles();
        $this->objUsersChat = new UsersChat();
        $this->objCompany = new Company();
        $this->objCompany = new CompanyUser();
        $this->objCategories = new Categories();
        $this->objCategoryImages = new CategoryImages();

        $this->companyOriginalImageUploadPath = Config::get('constant.COMPANY_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->companyThumbImageUploadPath = Config::get('constant.COMPANY_THUMB_IMAGE_UPLOAD_PATH');
        $this->companyThumbImageHeight = Config::get('constant.COMPANY_THUMB_IMAGE_HEIGHT');
        $this->companyThumbImageWidth = Config::get('constant.COMPANY_THUMB_IMAGE_WIDTH');
        
        $this->userOriginalImageUploadPath = Config::get('constant.USER_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->userThumbImageUploadPath = Config::get('constant.USER_THUMB_IMAGE_UPLOAD_PATH');
        $this->userThumbImageHeight = Config::get('constant.USER_THUMB_IMAGE_HEIGHT');
        $this->userThumbImageWidth = Config::get('constant.USER_THUMB_IMAGE_WIDTH');

        $this->defaultImage = Config::get('constant.DEFAULT_IMAGE_PATH');
    }
    
    /**
     * Search Chat Users
     */
    public function searchChatUsers(Request $request)
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
            $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 0;
            $paginate = (!empty($pageNo) && $pageNo > 0) ? true : false;
            
            $filters = [];
            $filters['created_by'] = Auth::user()->id;
            if(isset($request->search_key) && !empty($request->search_key))
            {
                $filters['search_key'] = $request->search_key;
            }
            $getChatUsers = $this->objUsersChat->getAll($filters, $paginate);
            
            if($getChatUsers && !empty($getChatUsers) && $getChatUsers->count() > 0) 
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.boutique_and_boutique_users_listing_fetched_successfully');
                $outputArray['data'] = array();
                $statusCode = 200;
                foreach ($getChatUsers as $key => $companyValue)
                {
                    $companyValue->company_name = '';
                    $companyValue->company_email = '';
                    $companyValue->company_image = '';
                    
                    $companyValue->user_name = '';
                    $companyValue->user_email = '';
                    $companyValue->user_image = '';
                    
                    $companyValue->user_role_name = '';
                    $companyValue->user_role_slug = '';
                    
                    if(isset($companyValue->company) && !empty($companyValue->company))
                    {
                        $companyValue->company_name = (!empty($companyValue->company->company_name) && $companyValue->company->company_name != '') ? $companyValue->company->company_name : '';
                        
                        $companyValue->company_email = (!empty($companyValue->company->company_email) && $companyValue->company->company_email != '') ? $companyValue->company->company_email : '';
                                                
                        $companyValue->company_image = (!empty($companyValue->company->company_image) && $companyValue->company->company_image != '' && Storage::exists($this->companyOriginalImageUploadPath.$companyValue->company->company_image) && Storage::size($this->companyOriginalImageUploadPath.$companyValue->company->company_image) > 0) ? Storage::url($this->companyOriginalImageUploadPath.$companyValue->company->company_image) : url($this->defaultImage);
                    } 
                    if(isset($companyValue->getUser) && !empty($companyValue->getUser))
                    {
                        $userImgValue = $companyValue->getUser;
                        
                        $companyValue->user_name = (!empty($userImgValue->name) && $userImgValue->name != '') ? $userImgValue->name : '';
                        $companyValue->user_email = (!empty($userImgValue->email) && $userImgValue->email != '') ? $userImgValue->email : '';                        
                        $companyValue->user_image = (!empty($userImgValue->user_image) && $userImgValue->user_image != '' && Storage::exists($this->userOriginalImageUploadPath.$userImgValue->user_image) && Storage::size($this->userOriginalImageUploadPath.$userImgValue->user_image) > 0) ? Storage::url($this->userOriginalImageUploadPath.$userImgValue->user_image) : url($this->defaultImage);                                     
                        if(isset($userImgValue->roles) && $userImgValue->roles && $userImgValue->roles->count() > 0)
                        {
                            $companyValue->user_role_name = $userImgValue->roles[0]->name;
                            $companyValue->user_role_slug = $userImgValue->roles[0]->slug;
                            unset($userImgValue->roles);
                        }                   
                    }
                    unset($companyValue->company, $companyValue->getUser);
                }
                $outputArray['data'] = $getChatUsers;
            }
            else
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.boutique_and_boutique_users_not_found');
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
     * get all Boutique and Boutique Users
     */
    public function chatBoutiqueUsers(Request $request)
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
            $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 0;
            $paginate = (!empty($pageNo) && $pageNo > 0) ? true : false;
            
            $filters = [];
            $filters['created_by'] = Auth::user()->id;
            if(isset($request->search_key) && !empty($request->search_key))
            {
                $filters['search_key'] = $request->search_key;
            }
            $getChatUsers = $this->objUsersChat->getAll($filters, $paginate);
            
            if($getChatUsers && !empty($getChatUsers) && $getChatUsers->count() > 0) 
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.boutique_and_boutique_users_listing_fetched_successfully');
                $outputArray['data'] = array();
                $statusCode = 200;
                                
                foreach ($getChatUsers as $key => $companyValue)
                {
                    $companyValue->company_name = '';
                    $companyValue->company_email = '';
                    $companyValue->company_image = '';
                    
                    $companyValue->user_name = '';
                    $companyValue->user_email = '';
                    $companyValue->user_image = '';
                    
                    $companyValue->user_role_name = '';
                    $companyValue->user_role_slug = '';
                    
                    if(isset($companyValue->company) && !empty($companyValue->company))
                    {
                        $companyValue->company_name = (!empty($companyValue->company->company_name) && $companyValue->company->company_name != '') ? $companyValue->company->company_name : '';
                        
                        $companyValue->company_email = (!empty($companyValue->company->company_email) && $companyValue->company->company_email != '') ? $companyValue->company->company_email : '';
                                                
                        $companyValue->company_image = (!empty($companyValue->company->company_image) && $companyValue->company->company_image != '' && Storage::exists($this->companyOriginalImageUploadPath.$companyValue->company->company_image) && Storage::size($this->companyOriginalImageUploadPath.$companyValue->company->company_image) > 0) ? Storage::url($this->companyOriginalImageUploadPath.$companyValue->company->company_image) : url($this->defaultImage);
                    } 
                    if(isset($companyValue->getUser) && !empty($companyValue->getUser))
                    {
                        $userImgValue = $companyValue->getUser;
                        
                        $companyValue->user_name = (!empty($userImgValue->name) && $userImgValue->name != '') ? $userImgValue->name : '';
                        $companyValue->user_email = (!empty($userImgValue->email) && $userImgValue->email != '') ? $userImgValue->email : '';                        
                        $companyValue->user_image = (!empty($userImgValue->photo) && $userImgValue->photo != '' && Storage::exists($this->userOriginalImageUploadPath.$userImgValue->photo) && Storage::size($this->userOriginalImageUploadPath.$userImgValue->photo) > 0) ? Storage::url($this->userOriginalImageUploadPath.$userImgValue->photo) : url($this->defaultImage);                                     
                        if(isset($userImgValue->roles) && $userImgValue->roles && $userImgValue->roles->count() > 0)
                        {
                            $companyValue->user_role_name = $userImgValue->roles[0]->name;
                            $companyValue->user_role_slug = $userImgValue->roles[0]->slug;
                            unset($userImgValue->roles);
                        }                   
                    }
                    unset($companyValue->company, $companyValue->getUser);
                }
                $outputArray['data'] = $getChatUsers;
            }
            else
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.boutique_and_boutique_users_not_found');
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
     * get all Super Admin Users
     */    
    public function chatSuperAdminUsers(Request $request)
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
            $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 0;
            $paginate = (!empty($pageNo) && $pageNo > 0) ? true : false;
            
            $filters = [];
//          $filters['created_by'] = Auth::user()->id;
            $getSuperAdminUsers = UserRoles::with(['user'])->where('role_id', 1)->get();   
            
            if($getSuperAdminUsers && !empty($getSuperAdminUsers) && $getSuperAdminUsers->count() > 0) 
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.super_admin_users_listing_fetched_successfully');
                $outputArray['data'] = array();
                $statusCode = 200; 
                $mainArray = [];
                foreach ($getSuperAdminUsers as $key => $superAdminUser)
                {
                    if(isset($superAdminUser->user) && $superAdminUser->user && !empty($superAdminUser->user))
                    {
                        $superAdminUsers = $superAdminUser->user;
                        $superAdminUsers->user_image = (!empty($superAdminUsers->user_image) && $superAdminUsers->user_image != '' && Storage::exists($this->userOriginalImageUploadPath.$superAdminUsers->user_image) && Storage::size($this->userOriginalImageUploadPath.$superAdminUsers->user_image) > 0) ? Storage::url($this->userOriginalImageUploadPath.$superAdminUsers->user_image) : url($this->defaultImage);
                        $superAdminUsers->photo = (!empty($superAdminUsers->photo) && $superAdminUsers->photo != '' && Storage::exists($this->userOriginalImageUploadPath.$superAdminUsers->photo) && Storage::size($this->userOriginalImageUploadPath.$superAdminUsers->photo) > 0) ? Storage::url($this->userOriginalImageUploadPath.$superAdminUsers->photo) : url($this->defaultImage);
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
     * Add Chat User
     */
    public function addChatUser(Request $request)
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
                'company_id' => 'required',
                'user_id' => 'required'
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
            
            $userExist = UsersChat::where('user_id', $request->user_id)
                    ->where('company_id', $request->company_id)
                    ->where('created_by', Auth::user()->id)
                    ->count();
            if($userExist == 0)
            {
                // Add Chat User             
                $saveChatUserData = [];
                $saveChatUserData['created_by'] = Auth::user()->id;
                $saveChatUserData['company_id'] = $request->company_id;
                $saveChatUserData['user_id'] = $request->user_id;
                $chatUserData = $this->objUsersChat->insertUpdate($saveChatUserData);

                if($chatUserData && !empty($chatUserData))
                {
                    DB::commit();
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.boutique_user_added_successfully_for_chat');
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode);
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
                DB::rollback();
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.boutique_user_already_exist_chat_listing');
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
