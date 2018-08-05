<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Config;
use Validator;
use Illuminate\Validation\Rule;
use DB;
use Auth;
use Input;

use App\Roles;
use App\UserRoles;
use App\User;
use App\Permissions;
use App\UserPermission;
use App\Company;
use App\CompanyUser;
use App\Store;

use App\Products;
use App\ProductMaterials;
use App\ProductColors;
use App\ProductImages;
use App\ProductInventory;

use App\Orders;
use App\OrderBoutiqueItems;
use App\OrderBoutiqueAttributes;
use App\OrderBoutiqueDocuments;
use App\OrderPackageBoxes;

use Storage;
use Helpers;
use \stdClass;
use JWTAuth;
use JWTAuthException;


class OrdersController extends Controller
{
    public function __construct()
    {
        $this->objRoles = new Roles();
        $this->objUser = new User();
        $this->objUserRoles = new UserRoles();
        $this->objPermissions = new Permissions();
        $this->objUserPermission = new UserPermission();

        $this->objCompany = new Company();
        $this->objCompanyUser = new CompanyUser();

        $this->objStore = new Store();

        $this->objProducts = new Products();
        $this->objProductImages = new ProductImages();
        $this->objProductColors = new ProductColors();
        $this->objProductMaterials = new ProductMaterials();
        $this->objProductsInventory = new ProductInventory();

        $this->objOrders = new Orders();
        $this->objOrderBoutiqueItems = new OrderBoutiqueItems();
        $this->objOrderBoutiqueAttributes = new OrderBoutiqueAttributes();
        $this->objOrderBoutiqueDocuments = new OrderBoutiqueDocuments();
        $this->objOrderPackageBoxes = new OrderPackageBoxes();

        $this->companyOriginalImageUploadPath = Config::get('constant.COMPANY_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->companyThumbImageUploadPath = Config::get('constant.COMPANY_THUMB_IMAGE_UPLOAD_PATH');

        $this->productOriginalImageUploadPath = Config::get('constant.PRODUCT_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->productThumbImageUploadPath = Config::get('constant.PRODUCT_THUMB_IMAGE_UPLOAD_PATH');
        $this->productThumbImageHeight = Config::get('constant.PRODUCT_THUMB_IMAGE_HEIGHT');
        $this->productThumbImageWidth = Config::get('constant.PRODUCT_THUMB_IMAGE_WIDTH');

        $this->productRecordPerPage = Config::get('constant.PRODUCT_RECORD_PER_PAGE');

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

        $this->brandOriginalImageUploadPath = Config::get('constant.BRANDS_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->brandThumbImageUploadPath = Config::get('constant.BRANDS_THUMB_IMAGE_UPLOAD_PATH');
        $this->brandThumbImageHeight = Config::get('constant.BRANDS_THUMB_IMAGE_HEIGHT');
        $this->brandThumbImageWidth = Config::get('constant.BRANDS_THUMB_IMAGE_WIDTH');

        $this->colorOriginalImageUploadPath = Config::get('constant.COLOR_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->colorThumbImageUploadPath = Config::get('constant.COLOR_THUMB_IMAGE_UPLOAD_PATH');
        $this->colorThumbImageHeight = Config::get('constant.COLOR_THUMB_IMAGE_HEIGHT');
        $this->colorThumbImageWidth = Config::get('constant.COLOR_THUMB_IMAGE_WIDTH');

        $this->materialOriginalImageUploadPath = Config::get('constant.MATERIAL_ORIGINAL_IMAGE_UPLOAD_PATH');
        $this->materialThumbImageUploadPath = Config::get('constant.MATERIAL_THUMB_IMAGE_UPLOAD_PATH');
        $this->materialThumbImageHeight = Config::get('constant.MATERIAL_THUMB_IMAGE_HEIGHT');
        $this->materialThumbImageWidth = Config::get('constant.MATERIAL_THUMB_IMAGE_WIDTH');


        $this->orderInvoiceDocUploadPath = Config::get('constant.ORDER_INVOICE_DOCUMENTS_UPLOAD_PATH');
        $this->orderExportDocUploadPath = Config::get('constant.ORDER_EXPORT_DOCUMENTS_UPLOAD_PATH');
        $this->orderOthersDocUploadPath = Config::get('constant.ORDER_OTHERS_DOCUMENTS_UPLOAD_PATH');
        $this->orderParcelDocUploadPath = Config::get('constant.ORDER_PARCEL_DOCUMENTS_UPLOAD_PATH');

        $this->productInventoryRecordPerPage = Config::get('constant.PRODUCT_INVENTORY_RECORD_PER_PAGE');

        $this->defaultImage = Config::get('constant.DEFAULT_IMAGE_PATH');
        $this->defaultPlusImage = Config::get('constant.DEFAULT_PLUS_IMAGE_PATH');
    }

    // Insert Order Details
    public function saveOrder(Request $request)
    {
        try
        {
            $filters = [];
            $outputArray = [];
            DB::beginTransaction();
            $rules = [
                'order_json' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails())
            {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ], 400);
            }
            $request_save_order = [];
            if(isset($request->order_json) && !empty($request->order_json))
            {
                $request_save_order = json_decode($request->order_json, true);
            }
            else
            {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.request_parameters_wrong')
                ], 400);
            }

            if($request_save_order && !empty($request_save_order) && count($request_save_order['order_boutique']) > 0)
            {
                $orderSave = [];
                $orderSave['order_number'] = $request_save_order['order_number'];
                $orderSave['order_date'] = $request_save_order['order_date'];
                $orderSave['purchased_from'] = $request_save_order['purchased_from'];
                $orderSave['payment_by'] = $request_save_order['payment_by'];
                $orderSave['order_status'] = $request_save_order['order_status'];
                $orderSave['billing_address'] = $request_save_order['billing_address'];
                $orderSave['delivery_address'] = $request_save_order['delivery_address'];
                $orderSave['boutique_count'] = $request_save_order['boutique_count'];
                $orderSave['items_count'] = $request_save_order['items_count'];
                $orderSave['shipping_type'] = $request_save_order['shipping_type'];
                $orderSave['shipping_price'] = $request_save_order['shipping_price'];
                $orderSave['group_name'] = $request_save_order['group_name'];
                $orderSave['eu_countires'] = $request_save_order['eu_countires'];

                $saveOrder = $this->objOrders->insertUpdate($orderSave);

                if($saveOrder)
                {
                    $orderBoutiqueData = [];
                    foreach ($request_save_order['order_boutique'] as $ob_key => $order_boutique)
                    {
                        if(isset($order_boutique['boutique_unique_code']) && !empty($order_boutique['boutique_unique_code']))
                        {
                            $getCompanyData = Company::where('company_unique_id', $order_boutique['boutique_unique_code'])->first();
                            if($getCompanyData && !empty($getCompanyData))
                            {
                                $orderBoutiqueData['order_id'] = $saveOrder->id;
                                $orderBoutiqueData['boutique_id'] = $getCompanyData->id;
                                $orderBoutiqueData['boutique_unique_code'] = $order_boutique['boutique_unique_code'];

                                $saveOrderDocuments = $this->objOrderBoutiqueDocuments->insertUpdate($orderBoutiqueData);
                                $saveOrderAttributes = $this->objOrderBoutiqueAttributes->insertUpdate($orderBoutiqueData);

                                if($saveOrderAttributes && isset($order_boutique['boutique_items']) && !empty($order_boutique['boutique_items']))
                                {
                                    foreach ($order_boutique['boutique_items'] as $biKey => $biValue)
                                    {
                                        $getProductDetails = Products::where('company_product_number', $biValue['product_unique_code'])->first();
                                        if($getProductDetails && !empty($getProductDetails))
                                        {
                                            $boutique_items = [];
                                            $boutique_items['order_id'] = $saveOrder->id;
                                            $boutique_items['boutique_id'] = $getCompanyData->id;
                                            $boutique_items['boutique_unique_code'] = $order_boutique['boutique_unique_code'];
                                            $boutique_items['product_id'] = $getProductDetails->id;
                                            $boutique_items['product_unique_code'] = $biValue['product_unique_code'];

                                            $boutique_items['item_size'] = $biValue['item_size'];
                                            $boutique_items['item_color'] = $biValue['item_color'];
                                            $boutique_items['item_material'] = $biValue['item_material'];
                                            $boutique_items['item_warehouse_name'] = $biValue['item_warehouse_name'];

                                            $boutique_items['item_confirmed_status'] = $biValue['item_confirmed_status'];
                                            $boutique_items['item_shipped_status'] = $biValue['item_shipped_status'];
                                            $boutique_items['item_returned_status'] = $biValue['item_returned_status'];
                                            $boutique_items['item_refunded_status'] = $biValue['item_refunded_status'];

                                            $boutique_items['delivery_country'] = $biValue['delivery_country'];
                                            $boutique_items['import_rate'] = $biValue['import_rate'];
                                            $boutique_items['prepaid_tax'] = $biValue['prepaid_tax'];
                                            $boutique_items['item_wise_subtotal'] = $biValue['item_wise_subtotal'];

                                            $saveOrderItemsData = $this->objOrderBoutiqueItems->insertUpdate($boutique_items);
                                        }
                                        else
                                        {
                                            DB::rollback();
                                            $outputArray['status'] = 0;
                                            $outputArray['message'] = trans('apimessages.default_error_msg');
                                            $statusCode = 400;
                                            return response()->json($outputArray, $statusCode);
                                        }
                                    }
                                }
                                else
                                {
                                    DB::rollback();
                                    $outputArray['status'] = 0;
                                    $outputArray['message'] = trans('apimessages.default_error_msg');
                                    $statusCode = 400;
                                    return response()->json($outputArray, $statusCode);
                                }
                            }
                            else
                            {
                                DB::rollback();
                                $outputArray['status'] = 0;
                                $outputArray['message'] = trans('apimessages.default_error_msg');
                                $statusCode = 400;
                                return response()->json($outputArray, $statusCode);
                            }
                        }
                        else
                        {
                            DB::rollback();
                            $outputArray['status'] = 0;
                            $outputArray['message'] = trans('apimessages.default_error_msg');
                            $statusCode = 400;
                            return response()->json($outputArray, $statusCode);
                        }
                    }
                    DB::commit();
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.order_saved_successfully');
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode);
                }
                else
                {
                    DB::rollback();
                    $outputArray['status'] = 0;
                    $outputArray['message'] = trans('apimessages.default_error_msg');
                    $statusCode = 200;
                    return response()->json($outputArray, $statusCode);
                }
            }
            else
            {
                DB::rollback();
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.default_error_msg');
                $statusCode = 400;
                return response()->json($outputArray, $statusCode);
            }

        } catch (Exception $e) {
            DB::rollback();
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
    }

    //  Get Order Boutique List for Super Admin
    public function getOrderBoutiqueListing(Request $request)
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
            if(isset($request->country) && !empty($request->country))
            {
                $filters['country'] = $request->country;
            }
            if(isset($request->boutique_id) && !empty($request->boutique_id))
            {
                $filters['boutique_id'] = $request->boutique_id;
            }
            if(isset($request->boutique_alphabet) && !empty($request->boutique_alphabet))
            {
                $filters['boutique_alphabet'] = $request->boutique_alphabet;
            }
            if(isset($request->start_date) && !empty($request->start_date) && isset($request->end_date) && !empty($request->end_date))
            {
                $filters['start_date'] = $request->start_date;
                $filters['end_date'] = $request->end_date;
            }

            $companyDetail = $this->objOrders->getCompanyAllDetail($filters, $paginate);

            if($companyDetail && $companyDetail->count() > 0 && !empty($companyDetail))
            {
                foreach ($companyDetail as $key => $_companyDetail)
                {
                    $_companyDetail->company_image = ($_companyDetail->company_image != NULL && $_companyDetail->company_image != '' && Storage::exists($this->companyThumbImageUploadPath.$_companyDetail->company_image) && Storage::size($this->companyThumbImageUploadPath.$_companyDetail->company_image) > 0) ? Storage::url($this->companyThumbImageUploadPath . $_companyDetail->company_image) : url($this->defaultPlusImage);
                }
                return response()->json([
                    'status' => '1',
                    'message' => trans('apimessages.boutique_list'),
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
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
    }

    //  Get Order list.
    public function getOrderList(Request $request)
    {
        try
        {
            $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 0;
            $paginate = (!empty($pageNo) && $pageNo > 0) ? true : false;
            $filters = [];
            $outputArray = [];

            $userRoles = $this->objUserRoles->getUserRole($request->user()->id);

            if($userRoles != 1 && $userRoles != 2)
            {
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }
            $rules = [
                'page' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails())
            {
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ],400);
            }

            if(isset($request->search_key) && !empty($request->search_key))
            {
                $filters['search_key'] = $request->search_key;
            }
            if(isset($request->boutique_id) && !empty($request->boutique_id))
            {
                $filters['boutique_id'] = $request->boutique_id;
            }

            $getOrders = $this->objOrders->getAll($filters, $paginate);

            if($getOrders && !empty($getOrders) && $getOrders->count() > 0)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.order_listing_fetched_successfully');
                $outputArray['data'] = array();
                $statusCode = 200;
                $outputArray['data'] = $getOrders;
            }
            else
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.orders_not_found');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
            return response()->json($outputArray, $statusCode);
        } catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
    }

    //  Get product list.
    public function getOrderPackageBoxes(Request $request)
    {
        try
        {
            $pageNo = (isset($request->page) && !empty($request->page)) ? $request->page : 0;
            $paginate = (!empty($pageNo) && $pageNo > 0) ? true : false;
            $filters = [];
            $outputArray = [];

            $userRoles = $this->objUserRoles->getUserRole($request->user()->id);

            if($userRoles != 1 && $userRoles != 2)
            {
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }

            $getOrderPackageBoxes = $this->objOrderPackageBoxes->getAll($filters, $paginate);

            if($getOrderPackageBoxes && !empty($getOrderPackageBoxes) && $getOrderPackageBoxes->count() > 0)
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.order_package_boxes_fetched_successfully');
                $outputArray['data'] = array();
                $statusCode = 200;
                $outputArray['data'] = $getOrderPackageBoxes;
            }
            else
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.order_package_boxes_not_found');
                $statusCode = 200;
                $outputArray['data'] = array();
            }
            return response()->json($outputArray, $statusCode);
        }
        catch (Exception $e) {
            return response()->json([
                'status' => '0',
                'message' => trans('apimessages.error_getting_product_details'),
                'code' => $e->getStatusCode()
            ]);
        }
    }

    //  Get Order Details
    public function getOrderDetails(Request $request)
    {
        try
        {
            $filters = [];
            $outputArray = [];

            $userRoles = $this->objUserRoles->getUserRole($request->user()->id);
            if($userRoles != 1 && $userRoles != 2)
            {
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }
            $rules = [
                'order_id' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails())
            {
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ],400);
            }
            if($userRoles == 2)
            {
                $getUserCompany = CompanyUser::where('user_id', Auth::user()->id)->first();
                if($getUserCompany && !empty($getUserCompany))
                {
                    $filters['boutique_id'] = $getUserCompany->company_id;
                }
                else
                {
                    return response()->json([
                        'status' => '0',
                        'message' => trans('apimessages.user_company_not_found')
                    ], 400);
                }
            }
            elseif (isset($request->boutique_id) && $request->boutique_id > 0)
            {
                $filters['boutique_id'] = $request->boutique_id;
            }

            $filters['order_id'] = $request->order_id;
            $orderData = $this->objOrders->orderDetails($filters);

            if($orderData && !empty($orderData))
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.order_details_fetched_successfully');
                $outputArray['data'] = array();
                $statusCode = 200;
                $mainArray = $orderData->toArray();
                $mainArray['order_boutique'] = array();
                $total_price_array = [];
                $total_price_array['product_outlet_price'] = [];
                $total_price_array['import_rate'] = [];
                $total_price_array['net_price'] = [];
                $total_price_array['prepaid_tax'] = [];

                if($mainArray['order_boutique_documents'] && !empty($mainArray['order_boutique_documents']))
                {
                    foreach($mainArray['order_boutique_documents'] as $imageKey => $imageName)
                    {
                        $mainArray['order_boutique_documents'][$imageKey]['invoice_doc_url'] = (isset($imageName['invoice_doc']) && $imageName['invoice_doc'] != NULL && $imageName['invoice_doc'] != '' && Storage::exists($this->orderInvoiceDocUploadPath.$imageName['invoice_doc']) && Storage::size($this->orderInvoiceDocUploadPath.$imageName['invoice_doc']) > 0) ? Storage::url($this->orderInvoiceDocUploadPath . $imageName['invoice_doc']) : '';

                        $mainArray['order_boutique_documents'][$imageKey]['parcel_doc_url'] = (isset($imageName['parcel_doc']) && $imageName['parcel_doc'] != NULL && $imageName['parcel_doc'] != '' && Storage::exists($this->orderParcelDocUploadPath.$imageName['parcel_doc']) && Storage::size($this->orderParcelDocUploadPath.$imageName['parcel_doc']) > 0) ? Storage::url($this->orderParcelDocUploadPath . $imageName['parcel_doc']) : '';

                        $mainArray['order_boutique_documents'][$imageKey]['export_doc_url'] = (isset($imageName['export_doc']) && $imageName['export_doc'] != NULL && $imageName['export_doc'] != '' && Storage::exists($this->orderExportDocUploadPath.$imageName['export_doc']) && Storage::size($this->orderExportDocUploadPath.$imageName['export_doc']) > 0) ? Storage::url($this->orderExportDocUploadPath . $imageName['export_doc']) : '';

                        $mainArray['order_boutique_documents'][$imageKey]['others_doc_url'] = (isset($imageName['others_doc']) && $imageName['others_doc'] != NULL && $imageName['others_doc'] != '' && Storage::exists($this->orderOthersDocUploadPath.$imageName['others_doc']) && Storage::size($this->orderOthersDocUploadPath.$imageName['others_doc']) > 0) ? Storage::url($this->orderOthersDocUploadPath.$imageName['others_doc']) : '';

                        $mainArray['order_boutique_documents'][$imageKey]['company_name'] = (isset($imageName['company']) && !empty($imageName['company']) && !empty($imageName['company']['company_name'])) ? $imageName['company']['company_name'] : '';

                        unset($imageName['company'], $mainArray['order_boutique_documents'][$imageKey]['company']);
                    }
                }

                if($mainArray['order_boutique_attributes'] && !empty($mainArray['order_boutique_attributes']))
                {
                    foreach($mainArray['order_boutique_attributes'] as $order_boutique_key => $order_boutique_value)
                    {
                        $mainArray['order_boutique'][$order_boutique_key] = $order_boutique_value;

                        if($mainArray['order_boutique_items'] && !empty($mainArray['order_boutique_items']))
                        {
                            $listArray = array();
                            foreach($mainArray['order_boutique_items'] as $itemsKey => $itemsValue)
                            {
                                if($itemsValue['boutique_id'] && $itemsValue['boutique_id'] == $order_boutique_value['boutique_id'])
                                {
                                    if($itemsValue['item_confirmed_status'] == 1)
                                    {
                                        if($itemsValue['order_boutique_product'] && !empty($itemsValue['order_boutique_product']))
                                        {
                                            $total_price_array['product_outlet_price'][$itemsKey] = $itemsValue['order_boutique_product']['product_outlet_price'];
                                            $total_price_array['net_price'][$itemsKey] = $itemsValue['order_boutique_product']['product_outlet_price'] - $itemsValue['order_boutique_product']['product_vat'];
                                        }
                                        if($itemsValue['import_rate'])
                                        {
                                            $total_price_array['import_rate'][$itemsKey] = $itemsValue['import_rate'];
                                        }
                                        if($itemsValue['prepaid_tax'])
                                        {
                                            $total_price_array['prepaid_tax'][$itemsKey] = $itemsValue['prepaid_tax'];
                                        }
                                    }
                                    if(isset($itemsValue['order_boutique_product']) && !empty($itemsValue['order_boutique_product']) && isset($itemsValue['order_boutique_product']['product_images']) && !empty($itemsValue['order_boutique_product']['product_images']))
                                    {
                                        $imageName = reset($itemsValue['order_boutique_product']['product_images']);
                                        $itemsValue['productImage'] = (isset($imageName['file_name']) && $imageName['file_name'] != NULL && $imageName['file_name'] != '' && Storage::exists($this->productOriginalImageUploadPath.$imageName['file_name']) && Storage::size($this->productOriginalImageUploadPath.$imageName['file_name']) > 0) ? Storage::url($this->productOriginalImageUploadPath . $imageName['file_name']) : url($this->defaultImage);
                                    }
                                    unset($itemsValue['order_boutique_product']['product_images']);

                                    $mainArray['order_boutique'][$order_boutique_key]['boutique_items'][] = $itemsValue;
                                }
                            }
                        }
                    }

                    $mainArray['total_outlet_price'] = (isset($total_price_array['product_outlet_price']) && !empty($total_price_array['product_outlet_price'])) ? array_sum($total_price_array['product_outlet_price']) : 0;

                    $mainArray['total_outlet_price'] = round($mainArray['total_outlet_price'], 2);

                    $mainArray['total_net_price'] = (isset($total_price_array['net_price']) && !empty($total_price_array['net_price'])) ? array_sum($total_price_array['net_price']) : 0;

                    $mainArray['total_net_price'] = round($mainArray['total_net_price'], 2);

                    $mainArray['total_import_rate'] = (isset($total_price_array['import_rate']) && !empty($total_price_array['import_rate'])) ? array_sum($total_price_array['import_rate']) : 0;

                    $mainArray['total_import_rate'] = round($mainArray['total_import_rate'], 2);

                    $mainArray['total_prepaid_tax'] = (isset($total_price_array['prepaid_tax']) && !empty($total_price_array['prepaid_tax'])) ? array_sum($total_price_array['prepaid_tax']) : 0;

                    $mainArray['total_prepaid_tax'] = round($mainArray['total_prepaid_tax'], 2);

                    $mainArray['sub_total'] = $mainArray['total_net_price'] + $mainArray['total_prepaid_tax'];

                    $mainArray['sub_total'] = round($mainArray['sub_total'], 2);

                    if($mainArray['eu_countires'] == 1)
                    {
                        if($mainArray['shipping_type'] == 1 && $mainArray['total_outlet_price'] > 1000)
                        {
                            $mainArray['total_price'] = $mainArray['total_outlet_price'];
                            $mainArray['total_price'] = round($mainArray['total_price'], 2);
                        }
                        else
                        {
                            $mainArray['total_price'] = $mainArray['total_outlet_price'] + $mainArray['shipping_price'];
                            $mainArray['total_price'] = round($mainArray['total_price'], 2);
                        }
                    }
                    else
                    {
                        if($mainArray['shipping_type'] == 1 && $mainArray['total_outlet_price'] > 1000)
                        {
                            $mainArray['total_price'] = $mainArray['sub_total'];
                            $mainArray['total_price'] = round($mainArray['total_price'], 2);
                        }
                        else
                        {
                            $mainArray['total_price'] = $mainArray['sub_total'] + $mainArray['shipping_price'];
                            $mainArray['total_price'] = round($mainArray['total_price'], 2);
                        }
                    }
                    unset($mainArray['order_boutique_items'], $mainArray['order_boutique_attributes']);
                    $outputArray['data'] = $mainArray;
                }
                else
                {
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.orders_details_not_found');
                    $statusCode = 200;
                    $outputArray['data'] = new stdClass();
                }
            }
            else
            {
                $outputArray['status'] = 1;
                $outputArray['message'] = trans('apimessages.orders_details_not_found');
                $statusCode = 200;
                $outputArray['data'] = new stdClass();
            }
            return response()->json($outputArray, $statusCode);
        }
        catch (Exception $e) {
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
    }

    // Save Order Details
    public function saveOrderDetails(Request $request)
    {
        try
        {
            $filters = [];
            $outputArray = [];
            DB::beginTransaction();
            $userRoles = $this->objUserRoles->getUserRole($request->user()->id);
            if($userRoles != 1 && $userRoles != 2)
            {
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }
            if($userRoles == 2)
            {
                $getUserCompany = CompanyUser::where('user_id', Auth::user()->id)->first();
                if($getUserCompany && !empty($getUserCompany))
                {
                    $filters['boutique_id'] = $getUserCompany->company_id;
                }
                else
                {
                    DB::rollback();
                    return response()->json([
                        'status' => '0',
                        'message' => trans('apimessages.user_company_not_found')
                    ], 400);
                }
            }
            $rules = [
                'save_order' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails())
            {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ], 400);
            }
            $request_save_order = [];
            if(isset($request->save_order) && !empty($request->save_order))
            {
                $request_save_order = json_decode($request->save_order, true);
            }
            else
            {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.request_parameters_wrong')
                ], 400);
            }

            if($request_save_order && !empty($request_save_order) && isset($request_save_order['id']) && isset($request_save_order['order_boutique']) && !empty($request_save_order['order_boutique']))
            {
                $orderData = [];
                foreach ($request_save_order['order_boutique'] as $ob_key => $order_boutique)
                {
                    $orderData['id'] = $order_boutique['id'];
                    $orderData['order_id'] = $order_boutique['order_id'];
                    $orderData['boutique_id'] = $order_boutique['boutique_id'];
                    $orderData['package_weight'] = $order_boutique['package_weight'];
                    $orderData['package_box_name'] = $order_boutique['package_box_name'];
                    $orderData['package_size'] = $order_boutique['package_size'];
                    $orderData['confirmed_items'] = $order_boutique['confirmed_items'];
                    $orderData['package_volumetric_weight'] = $order_boutique['package_volumetric_weight'];
                    if(isset($order_boutique['boutique_items']) && !empty($order_boutique['boutique_items']))
                    {
                        foreach ($order_boutique['boutique_items'] as $biKey => $biValue)
                        {
                            $boutique_items = [];
                            $boutique_items['id'] = $biValue['id'];
                            $boutique_items['boutique_id'] = $biValue['boutique_id'];
                            $boutique_items['product_id'] = $biValue['product_id'];
                            $boutique_items['product_unique_code'] = $biValue['product_unique_code'];
                            $boutique_items['item_confirmed_status'] = $biValue['item_confirmed_status'];
                            $boutique_items['item_shipped_status'] = $biValue['item_shipped_status'];
                            $boutique_items['item_returned_status'] = $biValue['item_returned_status'];
                            $boutique_items['item_refunded_status'] = $biValue['item_refunded_status'];
                            $saveOrderItemsData = $this->objOrderBoutiqueItems->insertUpdate($boutique_items);
                        }
                    }
                    $saveOrderData = $this->objOrderBoutiqueAttributes->insertUpdate($orderData);
                }
                if(isset($saveOrderData) && $saveOrderData)
                {
                    DB::commit();
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.order_saved_successfully');
                    $statusCode = 200;
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
                $outputArray['message'] = trans('apimessages.default_error_msg');
                $statusCode = 400;
            }
            return response()->json($outputArray, $statusCode);
        } catch (Exception $e) {
            DB::rollback();
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
    }

    //  Save Boutique Documents
    public function saveBoutiqueDocuments(Request $request)
    {
        try
        {
            $requestData = $request->all();
            $outputArray = [];
            DB::beginTransaction();
            $userRoles = $this->objUserRoles->getUserRole($request->user()->id);
            if($userRoles != 1 && $userRoles != 2)
            {
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }
            $rules = [
                'order_id' => 'required',
                'boutique_id' => 'required',
                'invoice_doc' => 'mimes:pdf,doc,docx,jpeg,jpg,png|max:1052400',
                'parcel_doc' => 'mimes:pdf,doc,docx,jpeg,jpg,png|max:1052400',
                'export_doc' => 'mimes:pdf,doc,docx,jpeg,jpg,png|max:1052400',
                'others_doc' => 'mimes:pdf,doc,docx,jpeg,jpg,png|max:1052400',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails())
            {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ],400);
            }

            $getOrderBoutiqueDocuments = OrderBoutiqueDocuments::where('order_id', $request->order_id)->where('boutique_id', $request->boutique_id)->first();

            if(Input::file('invoice_doc'))
            {
                $file = Input::file('invoice_doc');
                if($getOrderBoutiqueDocuments && !empty($getOrderBoutiqueDocuments) && !empty($getOrderBoutiqueDocuments->invoice_doc))
                {
                    $oldFileName = $getOrderBoutiqueDocuments->invoice_doc;
                    $getOrderBoutiqueDocuments->invoice_doc = NULL;
                    $invoiceDocSave = $getOrderBoutiqueDocuments->save();
                    if($invoiceDocSave)
                    {
                        Helpers::deleteFileToStorage($oldFileName, $this->orderInvoiceDocUploadPath);
                    }
                }
                $prefix = 'invoice_docs_';
                $invoiceDocData = [];
                if($getOrderBoutiqueDocuments && !empty($getOrderBoutiqueDocuments) && $getOrderBoutiqueDocuments->id > 0)
                {
                   $invoiceDocData['id'] = $getOrderBoutiqueDocuments->id;
                }
                $invoiceDocData['order_id'] = $request->order_id;
                $invoiceDocData['boutique_id'] = $request->boutique_id;

                $uploadInvoiceDocData = Helpers::createDocuments($file, $this->orderInvoiceDocUploadPath, $prefix, null);

                if($uploadInvoiceDocData['doc_name'] && !empty($uploadInvoiceDocData['doc_name']))
                {
                    $invoiceDocData['invoice_doc'] = $uploadInvoiceDocData['doc_name'];
                    $saveDocFile = $this->objOrderBoutiqueDocuments->insertUpdate($invoiceDocData);
                }
            }
            if(Input::file('parcel_doc'))
            {
                $file = Input::file('parcel_doc');
                if($getOrderBoutiqueDocuments && !empty($getOrderBoutiqueDocuments) && !empty($getOrderBoutiqueDocuments->parcel_doc))
                {
                    $oldFileName = $getOrderBoutiqueDocuments->parcel_doc;
                    $getOrderBoutiqueDocuments->parcel_doc = NULL;
                    $parcelDocSave = $getOrderBoutiqueDocuments->save();
                    if($parcelDocSave)
                    {
                        Helpers::deleteFileToStorage($oldFileName, $this->orderParcelDocUploadPath);
                    }
                }
                $prefix = 'parcel_docs_';
                $parcelDocData = [];
                if($getOrderBoutiqueDocuments && !empty($getOrderBoutiqueDocuments) && $getOrderBoutiqueDocuments->id > 0)
                {
                   $parcelDocData['id'] = $getOrderBoutiqueDocuments->id;
                }
                $parcelDocData['order_id'] = $request->order_id;
                $parcelDocData['boutique_id'] = $request->boutique_id;

                $uploadParcelDocData = Helpers::createDocuments($file, $this->orderParcelDocUploadPath, $prefix, null);

                if($uploadParcelDocData['doc_name'] && !empty($uploadParcelDocData['doc_name']))
                {
                    $parcelDocData['parcel_doc'] = $uploadParcelDocData['doc_name'];
                    $saveDocFile = $this->objOrderBoutiqueDocuments->insertUpdate($parcelDocData);
                }
            }
            if(Input::file('export_doc'))
            {
                $file = Input::file('export_doc');
                if($getOrderBoutiqueDocuments && !empty($getOrderBoutiqueDocuments) && !empty($getOrderBoutiqueDocuments->export_doc))
                {
                    $oldFileName = $getOrderBoutiqueDocuments->export_doc;
                    $getOrderBoutiqueDocuments->export_doc = NULL;
                    $exportDocSave = $getOrderBoutiqueDocuments->save();
                    if($exportDocSave)
                    {
                        Helpers::deleteFileToStorage($oldFileName, $this->orderExportDocUploadPath);
                    }
                }
                $prefix = 'export_docs_';
                $exportDocData = [];
                if($getOrderBoutiqueDocuments && !empty($getOrderBoutiqueDocuments) && $getOrderBoutiqueDocuments->id > 0)
                {
                   $exportDocData['id'] = $getOrderBoutiqueDocuments->id;
                }
                $exportDocData['order_id'] = $request->order_id;
                $exportDocData['boutique_id'] = $request->boutique_id;

                $uploadExportDocData = Helpers::createDocuments($file, $this->orderExportDocUploadPath, $prefix, null);

                if($uploadExportDocData['doc_name'] && !empty($uploadExportDocData['doc_name']))
                {
                    $exportDocData['export_doc'] = $uploadExportDocData['doc_name'];
                    $saveDocFile = $this->objOrderBoutiqueDocuments->insertUpdate($exportDocData);
                }
            }
            if(Input::file('others_doc'))
            {
                $file = Input::file('others_doc');
                if($getOrderBoutiqueDocuments && !empty($getOrderBoutiqueDocuments) && !empty($getOrderBoutiqueDocuments->others_doc))
                {
                    $oldFileName = $getOrderBoutiqueDocuments->others_doc;
                    $getOrderBoutiqueDocuments->others_doc = NULL;
                    $othersDocSave = $getOrderBoutiqueDocuments->save();
                    if($othersDocSave)
                    {
                        Helpers::deleteFileToStorage($oldFileName, $this->orderOthersDocUploadPath);
                    }
                }
                $prefix = 'others_doc_';
                $othersDocData = [];
                if($getOrderBoutiqueDocuments && !empty($getOrderBoutiqueDocuments) && $getOrderBoutiqueDocuments->id > 0)
                {
                   $othersDocData['id'] = $getOrderBoutiqueDocuments->id;
                }
                $othersDocData['order_id'] = $request->order_id;
                $othersDocData['boutique_id'] = $request->boutique_id;

                $uploadOthersDocData = Helpers::createDocuments($file, $this->orderOthersDocUploadPath, $prefix, null);

                if($uploadOthersDocData['doc_name'] && !empty($uploadOthersDocData['doc_name']))
                {
                    $othersDocData['others_doc'] = $uploadOthersDocData['doc_name'];
                    $saveDocFile = $this->objOrderBoutiqueDocuments->insertUpdate($othersDocData);
                }
            }
            if((isset($saveDocFile) && $saveDocFile))
            {
                $saveDocFile->invoice_doc_url = (isset($saveDocFile->invoice_doc) && $saveDocFile->invoice_doc != NULL && $saveDocFile->invoice_doc != '' && Storage::exists($this->orderInvoiceDocUploadPath.$saveDocFile->invoice_doc) && Storage::size($this->orderInvoiceDocUploadPath.$saveDocFile->invoice_doc) > 0) ? Storage::url($this->orderInvoiceDocUploadPath.$saveDocFile->invoice_doc) : '';

                $saveDocFile->parcel_doc_url = (isset($saveDocFile->parcel_doc) && $saveDocFile->parcel_doc != NULL && $saveDocFile->parcel_doc != '' && Storage::exists($this->orderParcelDocUploadPath.$saveDocFile->parcel_doc) && Storage::size($this->orderParcelDocUploadPath.$saveDocFile->parcel_doc) > 0) ? Storage::url($this->orderParcelDocUploadPath.$saveDocFile->parcel_doc) : '';

                $saveDocFile->export_doc_url = (isset($saveDocFile->export_doc) && $saveDocFile->export_doc != NULL && $saveDocFile->export_doc != '' && Storage::exists($this->orderExportDocUploadPath.$saveDocFile->export_doc) && Storage::size($this->orderExportDocUploadPath.$saveDocFile->export_doc) > 0) ? Storage::url($this->orderExportDocUploadPath.$saveDocFile->export_doc) : '';

               $saveDocFile->others_doc_url = (isset($saveDocFile->others_doc) && $saveDocFile->others_doc != NULL && $saveDocFile->others_doc != '' && Storage::exists($this->orderOthersDocUploadPath.$saveDocFile->others_doc) && Storage::size($this->orderOthersDocUploadPath.$saveDocFile->others_doc) > 0) ? Storage::url($this->orderOthersDocUploadPath.$saveDocFile->others_doc) : '';

                DB::commit();
                $outputArray['status'] = 1;
                $outputArray['message'] =  trans('apimessages.order_document_save_successfully');
                $outputArray['data'] = $saveDocFile;
                $statusCode = 200;
            }
            else
            {
                DB::rollback();
                $outputArray['status'] = 0;
                $outputArray['message'] = trans('apimessages.default_error_msg');
                $outputArray['data'] = array();
                $statusCode = 200;
            }
            return response()->json($outputArray, $statusCode);
        } catch (Exception $e)  {
            DB::rollback();
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
    }

    //  Delete Boutique Documents
    public function deleteBoutiqueDocument(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        $outputArray = [];
        try
        {
            DB::beginTransaction();
            $userRoles = $this->objUserRoles->getUserRole($request->user()->id);
            if($userRoles != 1 && $userRoles != 2)
            {
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }
            $rules = [
                'id' => 'required',
                'doc_slug' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails())
            {
                DB::rollback();
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ],400);
            }

            $boutiqueDocumentDetails = OrderBoutiqueDocuments::find($request->id);
            $doc_slug = '';
            if($request->doc_slug && !empty($request->doc_slug) && ($request->doc_slug == 'invoice_doc' || $request->doc_slug == 'parcel_doc' || $request->doc_slug == 'export_doc' || $request->doc_slug == 'others_doc'))
            {
                $doc_slug = $request->doc_slug;
            }

            if(isset($boutiqueDocumentDetails) && !empty($boutiqueDocumentDetails) && !empty($doc_slug) && !empty($boutiqueDocumentDetails->{$doc_slug}))
            {
                $oldFileName = $boutiqueDocumentDetails->{$doc_slug};

                if($request->doc_slug == 'invoice_doc')
                {
                   $fileUploadPath = $this->orderInvoiceDocUploadPath;
                }
                elseif($request->doc_slug == 'parcel_doc')
                {
                    $fileUploadPath = $this->orderParcelDocUploadPath;
                }
                elseif($request->doc_slug == 'export_doc')
                {
                    $fileUploadPath = $this->orderExportDocUploadPath;
                }
                elseif($request->doc_slug == 'others_doc')
                {
                    $fileUploadPath = $this->orderOthersDocUploadPath;
                }
                else
                {
                    $fileUploadPath = '';
                }

                Helpers::deleteFileToStorage($oldFileName, $fileUploadPath);
                $boutiqueDocumentDetails->{$doc_slug} = NULL;
                $response = $boutiqueDocumentDetails->save();
                if($response)
                {
                    DB::commit();
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.order_document_deleted_successfully');
                    $statusCode = 200;
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
                $outputArray['message'] =  trans('apimessages.order_boutique_document_not_found');
                $statusCode = 200;
            }
            return response()->json($outputArray, $statusCode);
        }catch (Exception $e) {
            DB::rollback();
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
    }

    //  Change Order Status
    public function changeOrderStatus(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $requestData = $request->all();
        $outputArray = [];
        try
        {
            DB::beginTransaction();
            $userRoles = $this->objUserRoles->getUserRole($request->user()->id);
            if($userRoles != 1)
            {
                return response()->json([
                    'status' => '0',
                    'message' => trans('apimessages.unauthorized_access')
                ], 400);
            }
            $rules = [
                'order_id' => 'required',
                'order_status' => 'required'
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails())
            {
                return response()->json([
                    'status' => '0',
                    'message' => $validator->messages()->all()[0]
                ],400);
            }

            $ordersDetails = Orders::find($request->order_id);
            if(isset($ordersDetails) && !empty($ordersDetails))
            {
                $ordersDetails->order_status = $request->order_status;
                $response = $ordersDetails->save();
                if($response)
                {
                    DB::commit();
                    $outputArray['status'] = 1;
                    $outputArray['message'] = trans('apimessages.order_status_updated_successfully');
                    $statusCode = 200;
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
                $outputArray['message'] =  trans('apimessages.order_not_found');
                $statusCode = 200;
            }
            return response()->json($outputArray, $statusCode);
        }catch (Exception $e) {
            DB::rollback();
            $outputArray['status'] = 0;
            $outputArray['message'] = $e->getMessage();
            $statusCode = $e->getStatusCode();
            return response()->json($outputArray, $statusCode);
        }
    }


}
