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
use App\Notifications;
use App\Brands;
use App\Colors;
use App\Http\Resources\ColorsResource;
use JWTAuth;
use JWTAuthException;


class ColorsController extends Controller
{

    public function __construct()
    {
        $this->objUser = new User();
        $this->objUserRoles = new UserRoles();
        $this->objCompany = new Company();
        $this->objBrands = new Brands();
        $this->objColors = new Colors();
        $this->objNotifications = new Notifications();

        $this->companyOriginalImageUploadPath = Config::get('constant.COMPANY_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->companyThumbImageUploadPath = Config::get('constant.COMPANY_THUMB_IMAGE_UPLOAD_PATH');

        $this->colorOriginalImageUploadPath = Config::get('constant.COLOR_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->colorThumbImageUploadPath = Config::get('constant.COLOR_THUMB_IMAGE_UPLOAD_PATH');
        $this->colorThumbImageHeight = Config::get('constant.COLOR_THUMB_IMAGE_HEIGHT');
        $this->colorThumbImageWidth = Config::get('constant.COLOR_THUMB_IMAGE_WIDTH');

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
                $colorsData = Colors::paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
            }
            else
            {
                $colorsData = Colors::get();
            }
            if($colorsData && !empty($colorsData) && $colorsData->count() > 0)
            {
                $dataArray['status'] = 1;
                $dataArray['message'] = trans('apimessages.colors_listing_fetched_successfully');
                $outputArray = ColorsResource::collection($colorsData);
                $outputArray->additional = $dataArray;
                return $outputArray;
            }
            else
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.colors_listing_not_found');
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
                'color_image' => 'mimes:jpeg,jpg,bmp,png,gif|max:52400',
                'color_name_en' => 'required|unique:colors,color_name_en,NULL,id,deleted_at,NULL',
                'color_name_ch' => 'required|unique:colors,color_name_ch,NULL,id,deleted_at,NULL',
                'color_name_ge' => 'required|unique:colors,color_name_ge,NULL,id,deleted_at,NULL',
                'color_name_fr' => 'required|unique:colors,color_name_fr,NULL,id,deleted_at,NULL',
                'color_name_it' => 'required|unique:colors,color_name_it,NULL,id,deleted_at,NULL',
                'color_name_sp' => 'required|unique:colors,color_name_sp,NULL,id,deleted_at,NULL',
                'color_name_ru' => 'required|unique:colors,color_name_ru,NULL,id,deleted_at,NULL',
                'color_name_jp' => 'required|unique:colors,color_name_jp,NULL,id,deleted_at,NULL',
            );
            $validator = Validator::make($requestData, $rules);
            if ($validator->fails())
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }

            $color_unique_id = mt_rand();
            $getColor = Colors::where('color_unique_id', $color_unique_id)->first();
            if(!empty($getColor))
            {
                $color_unique_id = mt_rand();
            }
            $colorOldImage = '';
            $color_image = '';
            if(Input::file('color_image'))
            {
                $file = Input::file('color_image');
                if (!empty($file))
                {
                    $color_image = Helpers::createUpdateImage($file, $this->colorOriginalImageUploadPath, $this->colorThumbImageUploadPath, $this->colorThumbImageHeight, $requestData, $colorOldImage);
                }
            }
            $colors = Colors::create([
                'color_name_en' => $request->color_name_en,
                'color_name_ch' => $request->color_name_ch,
                'color_name_ge' => $request->color_name_ge,
                'color_name_fr' => $request->color_name_fr,
                'color_name_it' => $request->color_name_it,
                'color_name_sp' => $request->color_name_sp,
                'color_name_ru' => $request->color_name_ru,
                'color_name_jp' => $request->color_name_jp,
                'color_unique_id' => $color_unique_id,
                'color_image' => $color_image,
            ]);
            if($colors)
            {
                $outputArray = new ColorsResource($colors);
                $dataArray['status'] = 1;
                $dataArray['message'] = trans('apimessages.color_added_successfully');
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
        // return new ColorsResource($colors);
    }


    /**
     * Display the specified colors.
     *
     * @param  \App\Colors  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        try
        {
            $colors = Colors::find($id);
            if($colors && !empty($colors))
            {
                $outputArray = new ColorsResource($colors);
                $dataArray['status'] = 1;
                $dataArray['message'] = trans('apimessages.color_details_fetched_successfully');
                $outputArray->additional = $dataArray;
                $statusCode = 200;
                return $outputArray;
            }
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] =  trans('apimessages.color_not_found');
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
        }  catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
//      return new ColorsResource($colors);
    }

    /**
     * Update the specified colors in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Colors  $colors
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Colors $colors)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        //      $colors->update($request->only(['title', 'description']));
        try
        {
            $rules = array(
                'color_image' => 'mimes:jpeg,jpg,bmp,png,gif|max:52400',
                'id' => 'required',
                'color_name_en' => 'required|unique:colors,color_name_en,'.$requestData['id'].',id,deleted_at,NULL',
                'color_name_ch' => 'required|unique:colors,color_name_ch,'.$requestData['id'].',id,deleted_at,NULL',
                'color_name_ge' => 'required|unique:colors,color_name_ge,'.$requestData['id'].',id,deleted_at,NULL',
                'color_name_fr' => 'required|unique:colors,color_name_fr,'.$requestData['id'].',id,deleted_at,NULL',
                'color_name_it' => 'required|unique:colors,color_name_it,'.$requestData['id'].',id,deleted_at,NULL',
                'color_name_sp' => 'required|unique:colors,color_name_sp,'.$requestData['id'].',id,deleted_at,NULL',
                'color_name_ru' => 'required|unique:colors,color_name_ru,'.$requestData['id'].',id,deleted_at,NULL',
                'color_name_jp' => 'required|unique:colors,color_name_jp,'.$requestData['id'].',id,deleted_at,NULL',
            );
            $validator = Validator::make($requestData, $rules);
            if ($validator->fails())
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }

            $colorData = Colors::find($requestData['id']);
            if(!empty($colorData))
            {
                $color_image = !empty($colorData->color_image) ? $colorData->color_image : NULL;
                if(Input::file('color_image'))
                {
                    $file = Input::file('color_image');
                    if (!empty($file))
                    {
                        $color_image = Helpers::createUpdateImage($file, $this->colorOriginalImageUploadPath, $this->colorThumbImageUploadPath, $this->colorThumbImageHeight, $requestData, $colorData->color_image);
                    }
                }
                $requestData['color_image'] = $color_image;
                $updateColor = $colorData->update($requestData);
                if($updateColor)
                {
                    $colorUpdatedData = Colors::find($requestData['id']);
                    $outputArray = new ColorsResource($colorUpdatedData);
                    $dataArray['status'] = 1;
                    $dataArray['message'] = trans('apimessages.color_updated_successfully');
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
                $outputArray['message'] =  trans('apimessages.color_not_found');
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        //      return new ColorsResource($colors);
    }

    /**
     * Remove the specified colors from storage.
     *
     * @param  \App\Colors  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try
        {
            $colors = Colors::find($id);
            if($colors && !empty($colors))
            {
                $oldImgName = $colors->color_image;
                Helpers::deleteFileToStorage($oldImgName, $this->colorOriginalImageUploadPath);
                Helpers::deleteFileToStorage($oldImgName, $this->colorThumbImageUploadPath);
                $colors->delete();
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.color_deleted_successfully');
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] =  trans('apimessages.color_not_found');
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
     * Remove the Multiple colors.
     *
     * @param  \App\Colors  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteMultipleColors(Request $request)
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
                    $colors = Colors::find($id);
                    if($colors && !empty($colors))
                    {
                        $oldImgName = $colors->color_image;
                        Helpers::deleteFileToStorage($oldImgName, $this->colorOriginalImageUploadPath);
                        Helpers::deleteFileToStorage($oldImgName, $this->colorThumbImageUploadPath);
                        $response = $colors->delete();
                    }
                }                    
                if(isset($response) && $response)
                {
                    DB::commit();
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.color_deleted_successfully');
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode); 
                } 
                else
                {
                    $outputArray['status'] = 0;
                    $outputArray['message'] =  trans('apimessages.color_not_found');
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode);
                }               
            }
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] =  trans('apimessages.color_not_found');
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
     * Delete color Image
     */
    public function deleteColorImage(Request $request)
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
            $colors = Colors::find($id);
            if($colors && !empty($colors) && !empty($colors->color_image))
            {
                $oldImgName = $colors->color_image;
                Helpers::deleteFileToStorage($oldImgName, $this->colorOriginalImageUploadPath);
                Helpers::deleteFileToStorage($oldImgName, $this->colorThumbImageUploadPath);
                $colors->color_image = NULL;
                $response = $colors->save();
                if($response)
                {
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.color_image_deleted_successfully');
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
                $outputArray['message'] =  trans('apimessages.color_image_not_found');
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
