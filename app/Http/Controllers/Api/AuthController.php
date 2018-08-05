<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use JWTAuth;
use JWTAuthException;
use Config;
use App\User;
use App\UsersDevice;
use App\CompanyUser;
use App\Company;
use Validator;
use Storage;
use \stdClass;
use Helpers;
use DB;
//use App\Transformers\UserDetailTransformer;

class AuthController extends Controller {

    public function __construct() 
    {
        $this->objUser = new User();
        $this->objUsersDevice = new UsersDevice();
        //$this->userDetailTransformer = $userDetailTransformer;
        
        $this->userOriginalImageUploadPath = Config::get('constant.USER_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->userThumbImageUploadPath = Config::get('constant.USER_THUMB_IMAGE_UPLOAD_PATH');
        $this->userThumbImageHeight = Config::get('constant.USER_THUMB_IMAGE_HEIGHT');
        $this->userThumbImageWidth = Config::get('constant.USER_THUMB_IMAGE_WIDTH');
        $this->defaultImage = Config::get('constant.DEFAULT_IMAGE_PATH');
    }

    public function getToken(Request $request) {

        $validator = Validator::make($request->all(), [
                    'email' => 'required|email',
                    'password' => 'required',
                    'user_type' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                        'status' => 0,
                        'message' => $validator->messages()->all()[0]
            ], 400);
        }
        $request->device_type = (isset($request->device_type) && $request->device_type != null) ? $request->device_type : '3';                 $request->device_token = (isset($request->device_token) && $request->device_token != null) ? $request->device_token : '';              $request->device_id = (isset($request->device_id) && $request->device_id != null) ? $request->device_id : '';
        
        $token = null;
        
        $userData = User::where('email', $request->email)->first();
        
        if($userData === null) {
            return response()->json([
                        'status' => 0,
                        'message' => trans('apimessages.user_not_found')
            ], 404);
        }
        
        if($request->user_type != 'admin' && $request->user_type != 'store') {
            return response()->json([
                        'status' => 0,
                        'message' => trans('apimessages.invalid_parameter')
            ], 400);
        }
        
        $userRoleData = $userData->hasRole(Config::get('constant.SUPER_ADMIN_SLUG'));
        
        if( ($request->user_type == 'admin' && $userRoleData == null) || ($request->user_type == 'store' && $userRoleData != null) ) {
            return response()->json([
                        'status' => 0,
                        'message' => trans('apimessages.invalid_login')
            ], 400);
        }
        
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                            'status' => 0,
                            'message' => trans('apimessages.email_password')
                ], 400);
            }
        } catch (JWTAuthException $e) {
            return response()->json([
                        'status' => 0,
                        'message' => trans('apimessages.failed_token')
                        
            ], 500);
        }
        $this->addDeviceToken($request->user(), $request->device_token, $request->device_type, $request->device_id);
        
        $request->user()->photo = ($request->user()->photo != NULL && $request->user()->photo != '' && Storage::exists($this->userThumbImageUploadPath.$request->user()->photo) && Storage::size($this->userThumbImageUploadPath.$request->user()->photo) > 0) ? Storage::url($this->userThumbImageUploadPath.$request->user()->photo) : url($this->defaultImage);
        
        return response()->json([
                    'status' => 1,
                    'message' => trans('apimessages.logged_in'),
                    'data' => [
                        'userDetail' => $request->user(),
                        'company_id' => isset($userData->userCompany->company_id) ? $userData->userCompany->company_id : 0,
                        'company_unique_id' => (isset($userData->userCompany->getCompany->company_unique_id) && !empty($userData->userCompany->getCompany->company_unique_id)) ? $userData->userCompany->getCompany->company_unique_id : 0,
                        'company_name' => (isset($userData->userCompany->getCompany->company_name) && !empty($userData->userCompany->getCompany->company_name)) ? $userData->userCompany->getCompany->company_name : '',
                        'is_default' => isset($userData->userCompany->default) ? $userData->userCompany->default : 0,
                        'loginToken' => compact('token')
                    ]
        ]);
    }
    
     /**
     * To add device token of login user if not exist
     * @param [object] $user
     * @param [string] $deviceToken
     * @return boolean
     */
    public function addDeviceToken($user, $deviceToken, $deviceType, $deviceId) 
    {
        try 
        {
            $userDeviceToken = UsersDevice::where('user_id', $user->id)->pluck('device_token');
            $userDeviceToken = $userDeviceToken->toArray();
            if (!empty($userDeviceToken) && count($userDeviceToken) > 0) 
            {
                if(!in_array($deviceToken, $userDeviceToken)) 
                {
                    $deviceData['user_id'] = $user->id;
                    $deviceData['device_token'] = $deviceToken;
                    $deviceData['device_type'] = $deviceType;
                    $deviceData['device_id'] = $deviceId;
                    UsersDevice::create($deviceData);
                }
            } else {
                $deviceData['user_id'] = $user->id;
                $deviceData['device_token'] = $deviceToken;
                $deviceData['device_type'] = $deviceType;
                $deviceData['device_id'] = $deviceId;
                UsersDevice::create($deviceData);
            }
            return 1;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Logout APis
     */ 
    public function logout(Request $request) 
    {
        try 
        {
            $user = JWTAuth::parseToken()->authenticate();
            DB::beginTransaction();            
            $request->device_type = ($request->device_type) ? $request->device_type : 3;            
            $request->device_token = ($request->device_token) ? $request->device_token : '';            
            $request->device_id = ($request->device_id) ? $request->device_id : '';
            
//          Delete device token of logged in user
//          $user = $request->user();
            UsersDevice::where('user_id', $user->id)->where('device_type', $request->device_type)->where('device_id', $request->device_id)->forceDelete();
            JWTAuth::invalidate(JWTAuth::getToken());
            DB::commit();
            $outputArray['status'] = 1;
            $outputArray['message'] = trans('apimessages.logout');
            $statusCode = 200;
            $outputArray['data'] = [];
            return response()->json($outputArray, $statusCode);
        } catch (Exception $e) 
        {
            DB::rollback();
            $outputArray['status'] = 0;
            $outputArray['message'] = trans('apimessages.default_error_msg');
            $statusCode =  $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
    }
}