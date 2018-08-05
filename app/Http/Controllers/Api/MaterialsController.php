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
use App\Brands;
use App\Materials;
use App\Notifications;
use App\Http\Resources\MaterialsResource;
use JWTAuth;
use JWTAuthException;

class MaterialsController extends Controller
{
    public function __construct()
    {
        $this->objUser = new User();
        $this->objUserRoles = new UserRoles();
        $this->objCompany = new Company();
        $this->objBrands = new Brands();
        $this->objMaterials = new Materials();
        $this->objNotifications = new Notifications();

        $this->materialOriginalImageUploadPath = Config::get('constant.MATERIAL_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->materialThumbImageUploadPath = Config::get('constant.MATERIAL_THUMB_IMAGE_UPLOAD_PATH');
        $this->materialThumbImageHeight = Config::get('constant.MATERIAL_THUMB_IMAGE_HEIGHT');
        $this->materialThumbImageWidth = Config::get('constant.MATERIAL_THUMB_IMAGE_WIDTH');

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
                $materialData = Materials::paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
            }
            else
            {
                $materialData = Materials::get();
            }
            if($materialData && !empty($materialData) && $materialData->count() > 0)
            {
                $dataArray['status'] = 1;
                $dataArray['message'] = trans('apimessages.materials_listing_fetched_successfully');
                $outputArray = MaterialsResource::collection($materialData);
                $outputArray->additional = $dataArray;
                return $outputArray;
            }
            else
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.materials_listing_not_found');
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
                'material_image' => 'mimes:jpeg,jpg,bmp,png,gif|max:52400',
                'material_name_en' => 'required|unique:materials,material_name_en,NULL,id,deleted_at,NULL',
                'material_name_ch' => 'required|unique:materials,material_name_ch,NULL,id,deleted_at,NULL',
                'material_name_ge' => 'required|unique:materials,material_name_ge,NULL,id,deleted_at,NULL',
                'material_name_fr' => 'required|unique:materials,material_name_fr,NULL,id,deleted_at,NULL',
                'material_name_it' => 'required|unique:materials,material_name_it,NULL,id,deleted_at,NULL',
                'material_name_sp' => 'required|unique:materials,material_name_sp,NULL,id,deleted_at,NULL',
                'material_name_ru' => 'required|unique:materials,material_name_ru,NULL,id,deleted_at,NULL',
                'material_name_jp' => 'required|unique:materials,material_name_jp,NULL,id,deleted_at,NULL',
            );
            $validator = Validator::make($requestData, $rules);
            if ($validator->fails())
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }

            $material_unique_id = mt_rand();
            $getMaterial = Materials::where('material_unique_id', $material_unique_id)->first();
            if($getMaterial && !empty($getMaterial))
            {
                $material_unique_id = mt_rand();
            }
            $materialOldImage = '';
            $material_image = '';
            if(Input::file('material_image'))
            {
                $file = Input::file('material_image');
                if (!empty($file))
                {
                    $material_image = Helpers::createUpdateImage($file, $this->materialOriginalImageUploadPath, $this->materialThumbImageUploadPath, $this->materialThumbImageHeight, $requestData, $materialOldImage);
                }
            }
            $materials = Materials::create([
                'material_name_en' => $request->material_name_en,
                'material_name_ch' => $request->material_name_ch,
                'material_name_ge' => $request->material_name_ge,
                'material_name_fr' => $request->material_name_fr,
                'material_name_it' => $request->material_name_it,
                'material_name_sp' => $request->material_name_sp,
                'material_name_ru' => $request->material_name_ru,
                'material_name_jp' => $request->material_name_jp,
                'material_unique_id' => $material_unique_id,
                'material_image' => $material_image,
            ]);
            if($materials)
            {
                $outputArray = new MaterialsResource($materials);
                $dataArray['status'] = 1;
                $dataArray['message'] = trans('apimessages.material_added_successfully');
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
        // return new MaterialsResource($materials);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $materials
     * @return \Illuminate\Http\Response
     */
    public function show($materials)
    {
        $user = JWTAuth::parseToken()->authenticate();
        try
        {
            $materials = Materials::find($materials);
            if($materials && !empty($materials))
            {
                $outputArray = new MaterialsResource($materials);
                $dataArray['status'] = 1;
                $dataArray['message'] = trans('apimessages.material_details_fetched_successfully');
                $outputArray->additional = $dataArray;
                $statusCode = 200;
                return $outputArray;
            }
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] =  trans('apimessages.material_not_found');
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
        }  catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        // return new MaterialsResource($materials);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Materials $materials)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = Input::all();

        try
        {
            $rules = array(
                'material_image' => 'mimes:jpeg,jpg,bmp,png,gif|max:52400',
                'id' => 'required',
                'material_name_en' => 'required|unique:materials,material_name_en,'.$requestData['id'].',id,deleted_at,NULL',
                'material_name_ch' => 'required|unique:materials,material_name_ch,'.$requestData['id'].',id,deleted_at,NULL',
                'material_name_ge' => 'required|unique:materials,material_name_ge,'.$requestData['id'].',id,deleted_at,NULL',
                'material_name_fr' => 'required|unique:materials,material_name_fr,'.$requestData['id'].',id,deleted_at,NULL',
                'material_name_it' => 'required|unique:materials,material_name_it,'.$requestData['id'].',id,deleted_at,NULL',
                'material_name_sp' => 'required|unique:materials,material_name_sp,'.$requestData['id'].',id,deleted_at,NULL',
                'material_name_ru' => 'required|unique:materials,material_name_ru,'.$requestData['id'].',id,deleted_at,NULL',
                'material_name_jp' => 'required|unique:materials,material_name_jp,'.$requestData['id'].',id,deleted_at,NULL',
            );
            $validator = Validator::make($requestData, $rules);
            if ($validator->fails())
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = $validator->messages()->all()[0];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }

            $materialData = Materials::find($requestData['id']);
            if($materialData && !empty($materialData))
            {
                $material_image = !empty($materialData->material_image) ? $materialData->material_image : NULL;

                if(Input::file('material_image'))
                {
                    $file = Input::file('material_image');
                    if (!empty($file))
                    {
                        $material_image = Helpers::createUpdateImage($file, $this->materialOriginalImageUploadPath, $this->materialThumbImageUploadPath, $this->materialThumbImageHeight, $requestData, $materialData->material_image);
                    }
                }

                $requestData['material_image'] = $material_image;
                $updateMaterial = $materialData->update($requestData);
                if($updateMaterial)
                {
                    $materialUpdatedData = Materials::find($requestData['id']);
                    $outputArray = new MaterialsResource($materialUpdatedData);
                    $dataArray['status'] = 1;
                    $dataArray['message'] = trans('apimessages.material_updated_successfully');
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
                $outputArray['message'] =  trans('apimessages.material_not_found');
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }

        // return new MaterialsResource($materials);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try
        {
            $materials = Materials::find($id);
            if($materials && !empty($materials))
            {
                $oldImgName = $materials->material_image;
                Helpers::deleteFileToStorage($oldImgName, $this->materialOriginalImageUploadPath);
                Helpers::deleteFileToStorage($oldImgName, $this->materialThumbImageUploadPath);
                $materials->delete();
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.material_deleted_successfully');
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] =  trans('apimessages.material_not_found');
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
     * Remove multiple materials .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteMultipleMaterials(Request $request)
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
                    $materials = Materials::find($id);
                    if($materials && !empty($materials))
                    {
                        $oldImgName = $materials->material_image;
                        Helpers::deleteFileToStorage($oldImgName, $this->materialOriginalImageUploadPath);
                        Helpers::deleteFileToStorage($oldImgName, $this->materialThumbImageUploadPath);
                        $response = $materials->delete();                        
                    }
                }
                if(isset($response) && $response)
                {
                    DB::commit();
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.material_deleted_successfully');
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode); 
                }
                else
                {
                    $outputArray['status'] = 0;
                    $outputArray['message'] =  trans('apimessages.material_not_found');
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode);
                }
            }
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] =  trans('apimessages.material_not_found');
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
     * Delete material Image
     */
    public function deleteMaterialImage(Request $request)
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
            $materials = Materials::find($id);
            if($materials && !empty($materials) && !empty($materials->material_image))
            {
                $oldImgName = $materials->material_image;
                Helpers::deleteFileToStorage($oldImgName, $this->materialOriginalImageUploadPath);
                Helpers::deleteFileToStorage($oldImgName, $this->materialThumbImageUploadPath);
                $materials->material_image = NULL;
                $response = $materials->save();
                if($response)
                {
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.material_image_deleted_successfully');
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
                $outputArray['message'] =  trans('apimessages.material_image_not_found');
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
