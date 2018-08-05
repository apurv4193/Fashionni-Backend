<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuth;
use JWTAuthException;
use Config;
use App\User;
use App\Company;
use App\CompanyBankDetail;
use App\CompanyUser;
use Validator;
use DB;
use Auth;
use Input;
use Storage;
use Illuminate\Validation\Rule;
use App\Helpers\Helpers as Helpers;

class BankController extends Controller
{
    public function __construct() {
        $this->objUser = new User();
        $this->objCompany = new Company();
        $this->objCompanyUser = new CompanyUser();
        $this->objCompanyBankDetail = new CompanyBankDetail();

        $this->bankOriginalImageUploadPath = Config::get('constant.BANK_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->bankThumbImageUploadPath = Config::get('constant.BANK_THUMB_IMAGE_UPLOAD_PATH');
        $this->bankThumbImageHeight = Config::get('constant.BANK_THUMB_IMAGE_HEIGHT');
        $this->bankThumbImageWidth = Config::get('constant.BANK_THUMB_IMAGE_WIDTH');

    }

    //Get bank list.
    public function getBankList(Request $request){
        try{
            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id,Config::get('constant.BOUTIQUE_BANK'),'view');
            if($checkAuthorization == '0'){
                return response()->json([
                            'status' => 0,
                            'message' => trans('apimessages.unauthorized_access')
                ], 400);
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

            $bankList = [];

            $bankList = $this->objCompanyBankDetail->where('company_id', $request->company_id)->get();
            if(isset($bankList) && count($bankList)>0){                
                foreach ($bankList as $key => $value) {
                    $bankList[$key]->bank_image = (!empty($value->bank_image)) ? Storage::url($this->bankThumbImageUploadPath . $value->bank_image) : '';
                }
            }else{
                DB::rollback();
                return response()->json([
                    'status' => '1',
                    'message' => trans('apimessages.bank_not_found'),
                    'data' => [],
                ],200);
            }

            DB::commit();
            return response()->json([
                'status' => '1',
                'message' => trans('apimessages.success'),
                'data' => $bankList
            ],200);

        }
        catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_adding_bank_details'),
                'code' => $e->getStatusCode()
            ]);
        }
    }

    //Register a new bank detail.
    public function saveCompanyBankDetails(Request $request)
    {
        try
        {
            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id,Config::get('constant.BOUTIQUE_BANK'),'edit');
            if($checkAuthorization == '0'){
                return response()->json([
                            'status' => 0,
                            'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }
            $requestData = $request->all();
            DB::beginTransaction();
            $rules = [];
            
            $validator = $validator = Validator::make($requestData, [
                'company_id' => 'required'
            ]);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ],400);
            }
            
            $bankJson = json_decode($request->bank, true);
           
            if($bankJson && !empty($bankJson))
            {
                foreach ($bankJson as $key => $value) 
                {
                    $data = $value;
                    $bankData = [];
                    $bankOldImage = "";

                    if($data['action'] == Config::get('constant.API_ACTION_UPDATE'))
                    {
                        if($data['id'] > 0)
                        {
                            $bankOldData = $this->objCompanyBankDetail->find($data['id']);
                            if(isset($bankOldData) && count($bankOldData)>0){
                                $bankData['id'] = $bankOldData->id;
                                $bankOldImage = $bankOldData->bank_image;
                            }
                            else{
                                DB::rollback();
                                return response()->json([
                                    'status' => '0',
                                    'message' => trans('apimessages.bank_not_found')
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
                            $this->objCompanyBankDetail->find($data['id'])->delete();
                            continue;
                        }
                        else{
                            DB::rollback();
                            return response()->json([
                                'status' => '0',
                                'message' => trans('apimessages.record_id_not_specified')
                            ],400);
                        }
                    }

                    $bankData['company_name'] = $data['company_name'];
                    $bankData['company_address'] = $data['comapny_address'];
                    $bankData['bank_name'] = $data['bank_name'];
                    $bankData['bank_address'] = $data['bank_address'];
                    $bankData['IBAN_account_no'] = $data['iban_account_no'];
                    $bankData['SWIFT_BIC'] = $data['swift_bic'];
                    $bankData['company_id'] = $request->company_id;

                    if(isset($data['bank_image']) && $data['bank_image'] != ""){
                        $file = Input::file($data['bank_image']);
                        if (!empty($file)) {
                            $bankData['bank_image'] = Helpers::createUpdateImage($file,$this->bankOriginalImageUploadPath, $this->bankThumbImageUploadPath, $this->bankThumbImageHeight, $bankData, $bankOldImage );
                        }
                    }

                    $bank = $this->objCompanyBankDetail->insertUpdate($bankData);
                }
                DB::commit();
                return response()->json([
                    'status' => '1',
                    'message' => trans('apimessages.company_bank_detail_added_successfully'),
                    'data' => $bankData
                ]);
            }
            else
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.norecordsfound');
                $outputArray['data'] = [];
                $statusCode = 200;
                return response()->json($outputArray, $statusCode);
            }
        }
        catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_adding_bank_details'),
                'code' => $e->getStatusCode()
            ]);
        }

    }

    /**
     * Delete Company Bank.
     */
    public function deleteCompanyBankDetails(Request $request) 
    {
        try{
            
            $checkAuthorization = Helpers::checkUserAuthorization(Auth::user()->id,Config::get('constant.BOUTIQUE_BANK'),'edit');
            
            if($checkAuthorization == '0'){
                return response()->json([
                            'status' => 0,
                            'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }

            DB::beginTransaction();

            $rules = [
                'bank_id' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ],400);
            }

            $companyBankData = $this->objCompanyBankDetail->find($request->bank_id);

            if(isset($companyBankData) && count($companyBankData)>0)
            {
                //Company Bank delete
                $companyBankData->delete();
            }

            DB::commit();

            return response()->json([
                'status' => '1',
                'message' => trans('apimessages.company_bank_deleted_successfully'),
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
