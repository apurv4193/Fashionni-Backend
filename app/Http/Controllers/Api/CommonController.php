<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\User;
use App\Company;
use App\Store;
use App\UserRoles;
use App\Permissions;
use App\UserPermission;
use App\CompanyDocuments;
use App\CompanyTaxDocuments;
use App\CompanyCustomDocuments;
use App\CompanyBankDetail;
use App\Products;
use App\ProductImages;
use \stdClass;
use Config;
use Validator;
use DB;
use Helpers;
use Input;
use Auth;
use JWTAuth;
use JWTAuthException;

class CommonController extends Controller
{
    public function __construct() 
    {
        $this->objUserRoles = new UserRoles();
        $this->objPermissions = new Permissions();
        $this->objUserPermission = new UserPermission();
        $this->objProducts = new Products();
        $this->objProductImages = new ProductImages();

        $this->companyOriginalImageUploadPath = Config::get('constant.COMPANY_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->companyThumbImageUploadPath = Config::get('constant.COMPANY_THUMB_IMAGE_UPLOAD_PATH');
        $this->companyThumbImageHeight = Config::get('constant.COMPANY_THUMB_IMAGE_HEIGHT');
        $this->companyThumbImageWidth = Config::get('constant.COMPANY_THUMB_IMAGE_WIDTH');

        $this->storeOriginalImageUploadPath = Config::get('constant.STORE_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->storeThumbImageUploadPath = Config::get('constant.STORE_THUMB_IMAGE_UPLOAD_PATH');
        $this->storeThumbImageHeight = Config::get('constant.STORE_THUMB_IMAGE_HEIGHT');
        $this->storeThumbImageWidth = Config::get('constant.STORE_THUMB_IMAGE_WIDTH');

        $this->companyCustomsDocumentsUploadPath = Config::get('constant.COMPANY_CUSTOMS_DOCUMENTS_UPLOAD_PATH');
        $this->companyDocumentsUploadPath = Config::get('constant.COMPANY_DOCUMENTS_UPLOAD_PATH');
        $this->companyTaxDocumentsUploadPath = Config::get('constant.COMPANY_TAX_DOCUMENTS_UPLOAD_PATH');

        $this->bankOriginalImageUploadPath = Config::get('constant.BANK_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->bankThumbImageUploadPath = Config::get('constant.BANK_THUMB_IMAGE_UPLOAD_PATH');
        $this->bankThumbImageHeight = Config::get('constant.BANK_THUMB_IMAGE_HEIGHT');
        $this->bankThumbImageWidth = Config::get('constant.BANK_THUMB_IMAGE_WIDTH');

        $this->userOriginalImageUploadPath = Config::get('constant.USER_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->userThumbImageUploadPath = Config::get('constant.USER_THUMB_IMAGE_UPLOAD_PATH');
        $this->userThumbImageHeight = Config::get('constant.USER_THUMB_IMAGE_HEIGHT');
        $this->userThumbImageWidth = Config::get('constant.USER_THUMB_IMAGE_WIDTH');

        $this->storeContactPersonOriginalImageUploadPath = Config::get('constant.STORE_CONTACT_PERSON_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->storeContactPersonThumbImageUploadPath = Config::get('constant.STORE_CONTACT_PERSON_THUMB_IMAGE_UPLOAD_PATH');
        $this->companyContactPersonOriginalImageUploadPath = Config::get('constant.COMPANY_CONTACT_PERSON_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->companyContactPersonThumbImageUploadPath = Config::get('constant.COMPANY_CONTACT_PERSON_THUMB_IMAGE_UPLOAD_PATH');
        
        $this->productOriginalImageUploadPath = Config::get('constant.PRODUCT_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->productThumbImageUploadPath = Config::get('constant.PRODUCT_THUMB_IMAGE_UPLOAD_PATH');
        $this->productThumbImageHeight = Config::get('constant.PRODUCT_THUMB_IMAGE_HEIGHT');
        $this->productThumbImageWidth = Config::get('constant.PRODUCT_THUMB_IMAGE_WIDTH');
        
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
    }

    /**
     * Delete product image type wise.
     */
    
    public function typeWiseProductImageDelete(Request $request) 
    {
        try 
        {
            DB::beginTransaction();
            $rules = [
                'type' => 'required',                
                'type_id' => 'required'
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
            
            if($request->type == Config::get('constant.PRODUCT_IMG_SLUG'))
            {
                $this->objClass = new ProductImages();
            }
            elseif ($request->type == Config::get('constant.WASH_CARE_IMG_SLUG') || $request->type == Config::get('constant.BRAND_INFO_IMG_SLUG') || $request->type == Config::get('constant.CODE_IMG_SLUG')) 
            {
                $this->objClass = new Products();
            }            
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.type_not_found');
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);                
            }
            
            $typeData = new \stdClass();
            $typeOldImage = '';
            $typeOriginalImagePath = '';
            $typeThumbImagePath = '';
            
            $typeData = $this->objClass->find($request->id);
            if($typeData && !empty($typeData))
            {
                if($request->type == Config::get('constant.PRODUCT_IMG_SLUG'))
                {
                    $typeOldImage = (isset($typeData) && !empty($typeData) && !empty($typeData->file_name)) ? $typeData->file_name : '';
                    $deleteProductImage = $typeData->delete();
                    $typeOriginalImagePath = $this->productOriginalImageUploadPath;
                    $typeThumbImagePath = $this->productThumbImageUploadPath;
                }
                elseif ($request->type == Config::get('constant.CODE_IMG_SLUG')) 
                {
                    $typeOldImage = (isset($typeData) && !empty($typeData) && !empty($typeData->code_image)) ? $typeData->code_image : '';
                    $typeData->code_image = NULL;
                    $deleteProductImage = $typeData->save(); 
                    $typeOriginalImagePath = $this->productCodeOriginalImageUploadPath;
                    $typeThumbImagePath = $this->productCodeThumbImageUploadPath;
                }
                elseif($request->type == Config::get('constant.BRAND_INFO_IMG_SLUG'))
                {
                    $typeOldImage = (isset($typeData) && !empty($typeData) && !empty($typeData->brand_label_with_original_information_image)) ? $typeData->brand_label_with_original_information_image : '';
                    $typeData->brand_label_with_original_information_image = NULL;

                    $deleteProductImage = $typeData->save();
                    $typeOriginalImagePath = $this->productBrandLabelOriginalImageUploadPath;
                    $typeThumbImagePath = $this->productBrandLabelThumbImageUploadPath;
                }
                elseif($request->type == Config::get('constant.WASH_CARE_IMG_SLUG'))
                {
                    $typeOldImage = (isset($typeData) && !empty($typeData) && !empty($typeData->wash_care_with_material_image)) ? $typeData->wash_care_with_material_image : '';
                    $typeData->brand_label_with_original_information_image = NULL;                        
                    $deleteProductImage = $typeData->save();
                    $typeOriginalImagePath = $this->productWashCareOriginalImageUploadPath;
                    $typeThumbImagePath = $this->productWashCareThumbImageUploadPath;
                }
                else
                {
                    $deleteProductImage = false;
                }
                if($deleteProductImage)
                {
                    Helpers::deleteFileToStorage($typeOldImage, $typeOriginalImagePath);
                    Helpers::deleteFileToStorage($typeOldImage, $typeThumbImagePath);
                    DB::commit();
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.product_image_deleted_successfully');
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
                $outputArray['message'] =  trans('apimessages.product_not_found');
                $statusCode = 200;
            }
        } catch (Exception $e){
            DB::rollback();
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }
    
    
    /**
     * Update product image type wise.
     */
    public function typeWiseProductImageUpdate(Request $request) 
    {
        try 
        {
            DB::beginTransaction();
            $rules = [
                'action' => 'required',
                'type' => 'required',                
                'type_id' => 'required',
                'type_image' => 'required|image|mimes:jpeg,jpg,bmp,png,gif|max:5120',
            ];
            if($request->type == Config::get('constant.PRODUCT_IMG_SLUG'))
            {
                $rules = [
                    'file_position' => 'required'                   
                ];
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
            
            if($request->type == Config::get('constant.PRODUCT_IMG_SLUG'))
            {
                $this->objClass = new ProductImages();
            }
            elseif($request->type == Config::get('constant.WASH_CARE_IMG_SLUG') || $request->type == Config::get('constant.BRAND_INFO_IMG_SLUG') || $request->type == Config::get('constant.CODE_IMG_SLUG'))
            {
                $this->objClass = new Products();
            }           
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.type_not_found');
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);                
            }
            
            $typeData = [];
            $typeOldImage = '';
            
            if($request->type == Config::get('constant.PRODUCT_IMG_SLUG'))
            {
                if($request->action == Config::get('constant.API_ACTION_CREATE'))
                {
                    $typeData['product_id'] = $request->type_id;
                    $typeData['file_position'] = $request->file_position;
                }
                elseif ($request->action == Config::get('constant.API_ACTION_UPDATE')) 
                {
                    if($request->type_id > 0)
                    {
                        $typeOldData = $this->objClass->find($request->type_id);
                        if(isset($typeOldData) && !empty($typeOldData))
                        {
                            $typeData['id'] = $typeOldData->id;
                            $typeData['product_id'] = $typeOldData->product_id;
                            $typeData['file_position'] = $request->file_position;
                        }
                        else
                        {
                            $outputArray['status'] = 0;
                            $outputArray['message'] =  trans('apimessages.record_not_found');
                            $statusCode = 200;
                            return response()->json($outputArray, $statusCode);
                        }
                    }                
                }
            }
            elseif($request->type == Config::get('constant.WASH_CARE_IMG_SLUG') || $request->type == Config::get('constant.BRAND_INFO_IMG_SLUG') || $request->type == Config::get('constant.CODE_IMG_SLUG'))
            {
                if($request->action == Config::get('constant.API_ACTION_CREATE'))
                {
                    $typeData['id'] = $request->type_id;
                }
                elseif ($request->action == Config::get('constant.API_ACTION_UPDATE')) 
                {
                    if($request->type_id > 0)
                    {
                        $typeOldData = $this->objClass->find($request->type_id);
                        if(isset($typeOldData) && !empty($typeOldData))
                        {
                            $typeData['id'] = $typeOldData->id;
                        }
                        else
                        {
                            $outputArray['status'] = 0;
                            $outputArray['message'] =  trans('apimessages.record_not_found');
                            $statusCode = 200;
                            return response()->json($outputArray, $statusCode);
                        }
                    }                
                }
            }            
            
            if (Input::file('type_image')) 
            {
                $file = Input::file('type_image');
                if (!empty($file)) 
                {
                    if($request->type == Config::get('constant.PRODUCT_IMG_SLUG'))
                    {
                        $typeOldImage = (isset($typeOldData) && !empty($typeOldData) && !empty($typeOldData->file_name)) ? $typeOldData->file_name : '';
                        $typeData['file_name'] = Helpers::createUpdateImage($file, $this->productOriginalImageUploadPath, $this->productThumbImageUploadPath, $this->productThumbImageHeight, $typeData, $typeOldImage );
                    }
                    elseif($request->type == Config::get('constant.CODE_IMG_SLUG'))
                    {
                        $typeOldImage = (isset($typeOldData) && !empty($typeOldData) && !empty($typeOldData->code_image)) ? $typeOldData->code_image : '';
                        $typeData['code_image'] = Helpers::createUpdateImage($file,$this->productCodeOriginalImageUploadPath, $this->productCodeThumbImageUploadPath, $this->productCodeThumbImageHeight, $typeData, $typeOldImage );                    
                    }
                    elseif($request->type == Config::get('constant.BRAND_INFO_IMG_SLUG'))
                    {
                        $typeOldImage = (isset($typeOldData) && !empty($typeOldData) && !empty($typeOldData->brand_label_with_original_information_image)) ? $typeOldData->brand_label_with_original_information_image : '';
                        
                        $typeData['brand_label_with_original_information_image'] = Helpers::createUpdateImage($file,$this->productBrandLabelOriginalImageUploadPath, $this->productBrandLabelThumbImageUploadPath, $this->productBrandLabelThumbImageHeight, $typeData, $typeOldImage);                    
                    }
                    elseif($request->type == Config::get('constant.WASH_CARE_IMG_SLUG'))
                    {
                        $typeOldImage = (isset($typeOldData) && !empty($typeOldData) && !empty($typeOldData->wash_care_with_material_image)) ? $typeOldData->wash_care_with_material_image : '';
                        
                        $typeData['wash_care_with_material_image'] = Helpers::createUpdateImage($file,$this->productWashCareOriginalImageUploadPath, $this->productWashCareThumbImageUploadPath, $this->productWashCareThumbImageHeight, $typeData, $typeOldImage );                    
                    }                    
                }
            }

            $typeSaveData = $this->objClass->insertUpdate($typeData);
            if($typeSaveData)
            {
                DB::commit();
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.image_updated_successfully');
                $statusCode = 200;
            }
            else
            {
                $outputArray['status'] = 0;
                $outputArray['message'] =  trans('apimessages.default_error_msg');
                $statusCode = 200;
            }
        } catch (Exception $e){
            DB::rollback();
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
        return response()->json($outputArray, $statusCode);
    }
    
    /**
     * Update Logo type wise.
     */
    public function typeWiseLogoUpdate(Request $request) 
    {
        try
        {

            DB::beginTransaction();

            $rules = [
                'type' => 'required',
                'type_id' => 'required',
                'action' => 'required'
            ];

            if($request->action == Config::get('constant.API_ACTION_CREATE') || $request->action == Config::get('constant.API_ACTION_UPDATE'))
            {
                $rules['type_image'] = 'required|image|mimes:jpeg,png,jpg|max:5120';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ],200);
            }
            
            if($request->type == "company")
            {
                $this->objClass = new Company();
                $authSlug = Config::get('constant.BOUTIQUE_REG');
            }
            elseif($request->type == "company_contact")
            {
                $this->objClass = new Company();
                $authSlug = Config::get('constant.BOUTIQUE_COMPANY');
            }
            elseif($request->type == "store" || $request->type == "store_contact")
            {
                if(isset($request->store_slug) && $request->store_slug != "")
                {
                    $this->objClass = new Store();
                    $authSlug = $request->store_slug;
                }
                else
                {
                    return response()->json([
                                'status' => '0',
                                'message' => trans('apimessages.store_slug_not_found')
                    ], 200);
                }
            }
            elseif($request->type == "bank")
            {
                $this->objClass = new CompanyBankDetail();
                $authSlug = Config::get('constant.BOUTIQUE_BANK');
            }
            elseif($request->type == "user")
            {
                $this->objClass = new User();
                $authSlug = Config::get('constant.BOUTIQUE_USER');
            }
            else
            {
                return response()->json([
                            'status' => '0',
                            'message' => trans('apimessages.type_not_found')
                ], 200);
            }

            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id,$authSlug,'edit');
            if($checkAuthorization == '0'){
                return response()->json([
                            'status' => '0',
                            'message' => trans('apimessages.unauthorized_access')
                ], 200);
            }

            $typeData = [];
            $typeOldImage = "";
            $typeOldData = $this->objClass->find($request->type_id);
            if(isset($typeOldData) && count($typeOldData)>0){
                $typeData['id'] = $typeOldData->id;
            }
            else{
                return response()->json([
                    'status' => '1',
                    'message' => trans('apimessages.record_not_found')
                ]);
            }
            
            if($request->action == Config::get('constant.API_ACTION_DELETE'))
            {
                if($request->type == "company")
                {
                    $typeOldImage = $typeOldData->company_image;
                    $typeData['company_image'] = "";
                    Helpers::deleteFileToStorage($typeOldImage, $this->companyOriginalImageUploadPath);
                    Helpers::deleteFileToStorage($typeOldImage, $this->companyThumbImageUploadPath);
                }
                elseif($request->type == "store")
                {
                    $typeOldImage = $typeOldData->store_image;
                    $typeData['store_image'] = "";
                    Helpers::deleteFileToStorage($typeOldImage, $this->storeOriginalImageUploadPath);
                    Helpers::deleteFileToStorage($typeOldImage, $this->storeThumbImageUploadPath);
                }
                elseif($request->type == "bank")
                {
                    $typeOldImage = $typeOldData->bank_image;
                    $typeData['bank_image'] = "";
                    Helpers::deleteFileToStorage($typeOldImage, $this->bankOriginalImageUploadPath);
                    Helpers::deleteFileToStorage($typeOldImage, $this->bankThumbImageUploadPath);
                }
                elseif($request->type == "user")
                {
                    $typeOldImage = $typeOldData->photo;
                    $typeData['photo'] = "";
                    Helpers::deleteFileToStorage($typeOldImage, $this->userOriginalImageUploadPath);
                    Helpers::deleteFileToStorage($typeOldImage, $this->userThumbImageUploadPath);
                }
                elseif($request->type == "store_contact")
                {
                    $typeOldImage = $typeOldData->store_contact_person_image;
                    $typeData['store_contact_person_image'] = "";
                    Helpers::deleteFileToStorage($typeOldImage, $this->storeContactPersonOriginalImageUploadPath);
                    Helpers::deleteFileToStorage($typeOldImage, $this->storeContactPersonThumbImageUploadPath);
                }
                elseif($request->type == "company_contact")
                {
                    $typeOldImage = $typeOldData->contact_person_image;
                    $typeData['contact_person_image'] = "";
                    Helpers::deleteFileToStorage($typeOldImage, $this->companyContactPersonOriginalImageUploadPath);
                    Helpers::deleteFileToStorage($typeOldImage, $this->companyContactPersonThumbImageUploadPath);
                }

                $typeSaveData = $this->objClass->insertUpdate($typeData);
                
                return response()->json([
                    'status' => '1',
                    'message' => trans('apimessages.image_updated_successfully')
                ]);
            }

            if($request->action == Config::get('constant.API_ACTION_UPDATE'))
            {
                if($request->type_id != 0){
                    if($request->type == "company")
                    {
                        $typeOldImage = $typeOldData->company_image;
                        $typeData['company_image'] = "";
                    }
                    elseif($request->type == "store")
                    {
                        $typeOldImage = $typeOldData->store_image;
                        $typeData['store_image'] = "";
                    }
                    elseif($request->type == "bank")
                    {
                        $typeOldImage = $typeOldData->bank_image;
                        $typeData['bank_image'] = "";
                    }
                    elseif($request->type == "user")
                    {
                        $typeOldImage = $typeOldData->photo;
                        $typeData['photo'] = "";
                    }
                    elseif($request->type == "store_contact")
                    {
                        $typeOldImage = $typeOldData->store_contact_person_image;
                        $typeData['store_contact_person_image'] = "";
                    }
                    elseif($request->type == "company_contact")
                    {
                        $typeOldImage = $typeOldData->contact_person_image;
                        $typeData['contact_person_image'] = "";
                    }
                }
                else{
                    DB::rollback();
                    return response()->json([
                        'status' => '0',
                        'message' => trans('apimessages.record_id_not_specified')
                    ],200);
                }
            }

            if (Input::file()) {
                $file = Input::file('type_image');
                if (!empty($file)) 
                {
                    if($request->type == "company")
                    {
                        $typeData['company_image'] = Helpers::createUpdateImage($file,$this->companyOriginalImageUploadPath, $this->companyThumbImageUploadPath, $this->companyThumbImageHeight, $typeData, $typeOldImage );
                    }
                    elseif($request->type == "store")
                    {
                        $typeData['store_image'] = Helpers::createUpdateImage($file,$this->storeOriginalImageUploadPath, $this->storeThumbImageUploadPath, $this->storeThumbImageHeight, $typeData, $typeOldImage );                    
                    }
                    elseif($request->type == "bank")
                    {
                        $typeData['bank_image'] = Helpers::createUpdateImage($file,$this->bankOriginalImageUploadPath, $this->bankThumbImageUploadPath, $this->bankThumbImageHeight, $typeData, $typeOldImage );                    
                    }
                    elseif($request->type == "user")
                    {
                        $typeData['photo'] = Helpers::createUpdateImage($file,$this->userOriginalImageUploadPath, $this->userThumbImageUploadPath, $this->userThumbImageHeight, $typeData, $typeOldImage );                    
                    }
                    elseif($request->type == "store_contact")
                    {
                        $typeData['store_contact_person_image'] = Helpers::createUpdateImage($file,$this->storeContactPersonOriginalImageUploadPath, $this->storeContactPersonThumbImageUploadPath, $this->storeThumbImageHeight, $typeData, $typeOldImage );
                    }
                    elseif($request->type == "company_contact")
                    {
                        $typeData['contact_person_image'] = Helpers::createUpdateImage($file,$this->companyContactPersonOriginalImageUploadPath, $this->companyContactPersonThumbImageUploadPath, $this->companyThumbImageHeight, $typeData, $typeOldImage );
                    }
                }
            }

            $typeSaveData = $this->objClass->insertUpdate($typeData);

            DB::commit();

            return response()->json([
                'status' => '1',
                'message' => trans('apimessages.image_updated_successfully')
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
    
    /**
     * Update Document type wise.
     */
    public function typeWiseDocumentUpdate(Request $request) 
    {
        try{

            DB::beginTransaction();

            $rules = [
                'type' => 'required',
                'company_id' => 'required',
                'action' => 'required'
            ];

            if($request->action == Config::get('constant.API_ACTION_CREATE') || $request->action == Config::get('constant.API_ACTION_UPDATE'))
            {
                $rules['type_document'] = 'required';
            }

            if($request->action == Config::get('constant.API_ACTION_DELETE') || $request->action == Config::get('constant.API_ACTION_UPDATE'))
            {
                $rules['doc_name'] = 'required';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ],200);
            }

            if($request->type == "company")
            {
                $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id,Config::get('constant.BOUTIQUE_REG'),'edit');
                if($checkAuthorization == '0'){
                    return response()->json([
                                'status' => '0',
                                'message' => trans('apimessages.unauthorized_access')
                    ], 200);
                }
                
                $this->objClass = new CompanyDocuments();
            }
            elseif($request->type == "tax")
            {
                $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id,Config::get('constant.BOUTIQUE_TAX'),'edit');
                if($checkAuthorization == '0'){
                    return response()->json([
                                'status' => '0',
                                'message' => trans('apimessages.unauthorized_access')
                    ], 200);
                }
                
                $this->objClass = new CompanyTaxDocuments();
            }
            elseif($request->type == "custom")
            {
                $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id,Config::get('constant.BOUTIQUE_CUSTOMS'),'edit');
                if($checkAuthorization == '0'){
                    return response()->json([
                                'status' => '0',
                                'message' => trans('apimessages.unauthorized_access')
                    ], 200);
                }

                $this->objClass = new CompanyCustomDocuments();
            }
            else
            {
                return response()->json([
                            'status' => '0',
                            'message' => trans('apimessages.type_not_found')
                ], 200);
            }

            if($request->action == Config::get('constant.API_ACTION_CREATE')){
                $checkDocumentCount = $this->objClass->getDocumentCountByCompanyId($request->company_id);
                if($checkDocumentCount >= 4){
                    return response()->json([
                                'status' => '0',
                                'message' => trans('apimessages.document_upload_limit_exceeded')
                    ], 200);       
                }
            }

            $typeOldDocument = "";
            $typeOldData = [];
            
            $typeData = [];
            
            $typeData['company_id'] = $request->company_id;

            if($request->action == Config::get('constant.API_ACTION_UPDATE') || $request->action == Config::get('constant.API_ACTION_DELETE'))
            {
                if($request->company_id != 0){
                    if($request->type == "company")
                    {
                        $typeOldData = $this->objClass->getCompanyDocumentByCompanyIdAndFileName($request->company_id,$request->doc_name);
                        if(isset($typeOldData) && count($typeOldData)>0){
                            $typeData['id'] = $typeOldData->id;
                        }
                        else{
                            return response()->json([
                                'status' => '1',
                                'message' => trans('apimessages.record_not_found')
                            ]);
                        }
                        $typeOldDocument = $typeOldData->company_doc_file_name;
                        Helpers::deleteFileToStorage($typeOldDocument, $this->companyDocumentsUploadPath);
                        $typeData['company_doc_file_name'] = "";
                    }
                    elseif($request->type == "tax")
                    {
                        $typeOldData = $this->objClass->getCompanyTaxDocumentByCompanyIdAndFileName($request->company_id,$request->doc_name);
                        if(isset($typeOldData) && count($typeOldData)>0){
                            $typeData['id'] = $typeOldData->id;
                        }
                        else{
                            return response()->json([
                                'status' => '1',
                                'message' => trans('apimessages.record_not_found')
                            ]);
                        }
                        $typeOldDocument = $typeOldData->company_doc_file_name;
                        Helpers::deleteFileToStorage($typeOldDocument, $this->companyTaxDocumentsUploadPath);
                        $typeData['company_doc_file_name'] = "";
                    }
                    elseif($request->type == "custom")
                    {
                        $typeOldData = $this->objClass->getCompanyCustomDocumentsByCompanyIdAndFileName($request->company_id,$request->doc_name);
                        if(isset($typeOldData) && count($typeOldData)>0){
                            $typeData['id'] = $typeOldData->id;
                        }
                        else{
                            return response()->json([
                                'status' => '1',
                                'message' => trans('apimessages.record_not_found')
                            ]);
                        }
                        $typeOldDocument = $typeOldData->company_doc_file_name;
                        Helpers::deleteFileToStorage($typeOldDocument, $this->companyCustomsDocumentsUploadPath);
                        $typeData['company_doc_file_name'] = "";
                    }
                }
                else{
                    DB::rollback();
                    return response()->json([
                        'status' => '0',
                        'message' => trans('apimessages.record_id_not_specified')
                    ],200);
                }
            }

            if($request->action == Config::get('constant.API_ACTION_DELETE'))
            {
                $typeSaveData = $this->objClass->find($typeData['id']);
                $typeSaveData->delete();
                if($typeSaveData)
                {
                    DB::commit();
                    return response()->json([
                        'status' => '1',
                        'message' => trans('apimessages.document_deleted_successfully')
                    ]);
                }
                else
                {
                    return response()->json([
                        'status' => '0',
                        'message' => trans('apimessages.default_error_msg')
                    ]);
                }
            }

            $filename = "";
            
            if (Input::file('type_document')) {
                $file = Input::file('type_document');
                if (!empty($file))
                {
                    if($request->type == "company")
                    {
                        $prefix = 'company_docs_';
                        $documentUploadData = Helpers::createDocuments($file,$this->companyDocumentsUploadPath, $prefix, null);
                        $typeData['company_doc_file_name'] = $documentUploadData['doc_name'];
                        $typeData['company_doc_name'] = $documentUploadData['doc_original_name'];
                        $filename = $documentUploadData['doc_name'];
                    }
                    elseif($request->type == "tax")
                    {
                        $prefix = 'company_tax_docs_';
                        $documentUploadData = Helpers::createDocuments($file,$this->companyTaxDocumentsUploadPath, $prefix, null);
                        $typeData['company_doc_file_name'] = $documentUploadData['doc_name'];
                        $typeData['company_tax_doc_name'] = $documentUploadData['doc_original_name'];
                        $filename = $documentUploadData['doc_name'];
                    }
                    elseif($request->type == "custom")
                    {
                        $prefix = 'company_custom_docs_';
                        $documentUploadData = Helpers::createDocuments($file,$this->companyCustomsDocumentsUploadPath, $prefix, null);
                        $typeData['company_doc_file_name'] = $documentUploadData['doc_name'];
                        $typeData['company_custom_doc_name'] = $documentUploadData['doc_original_name'];
                        $filename = $documentUploadData['doc_name'];
                    }
                }
            }

            $typeSaveData = $this->objClass->insertUpdate($typeData);

            DB::commit();

            return response()->json([
                'status' => '1',
                'message' => trans('apimessages.document_updated_successfully'),
                'company_doc_file_name' => $filename,

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
