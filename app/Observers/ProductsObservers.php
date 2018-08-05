<?php
/**
 * Created by Apurv Prajapati.
 * Date: 19-04-2018
 * Time: 10:26 AM IST
 */

namespace App\Observers;

use Auth;
use Helpers;
use Config;
use App\Products;
use App\Brands;
use App\Notifications;
use Illuminate\Http\Request;
use Log;

class ProductsObservers
{
    public function created(Products $products)
    {
        if(isset($products->product_unique_id)) {
            $is_published = ($products->is_published == 1) ? "Product is published!" : "Product not yet published!";
            Log::info('New product added by '. auth()->user()->name .' - '.auth()->user()->id .'. Product id is '. $products->product_unique_id .' & '. $is_published. ' - '. \Request::ip());
        }
    }

    public function updated(Products $products)
    {  
        $activities = array_diff($products->getAttributes(), $products->getOriginal());
        $notifications = [];

        $track_events = [
            'company_id',
            'brand_id',
            'product_name_en',
            'product_name_ch',
            'product_name_ge',
            'product_name_fr',
            'product_name_it',
            'product_name_sp',
            'product_name_ru',
            'product_name_jp',
            'category_level1_id',
            'category_level2_id',
            'category_level3_id',
            'category_level4_id',
            'product_retail_price',
            'product_discount_rate',
            'product_discount_amount',
            'product_vat_rate',
            'product_vat',
            'product_outlet_price',
            'product_outlet_price_exclusive_vat',
            'fashionni_fees',
            'code_number',
            'code_image',
            'is_published',
            'brand_label_with_original_information_image',
            'wash_care_with_material_image',
            'short_description',
            'material_detail',
            'product_notice',
            'product_code_barcode',
            'product_code_boutique',
            'product_code_rfid',
        ];

        $fields_page_mapping = [
            'company_id' => ['product_details'],
            'brand_id' => ['product_details'],
            'created_by' => ['product_details'],
            'product_name_en' => ['product_details'],
            'product_name_ch' => ['product_details'],
            'product_name_ge' => ['product_details'],
            'product_name_fr' => ['product_details'],
            'product_name_it' => ['product_details'],
            'product_name_sp' => ['product_details'],
            'product_name_ru' => ['product_details'],
            'product_name_jp' => ['product_details'],
            'product_unique_id' => ['product_details'],
            'category_level1_id' => ['product_details'],
            'category_level2_id' => ['product_details'],
            'category_level3_id' => ['product_details'],
            'category_level4_id' => ['product_details'],
            'product_retail_price' => ['product_details'],
            'product_discount_rate' => ['product_details'],
            'product_discount_amount' => ['product_details'],
            'product_vat_rate' => ['product_details'],
            'product_vat' => ['product_details'],
            'product_outlet_price' => ['product_details'],
            'product_outlet_price_exclusive_vat' => ['product_details'],
            'fashionni_fees' => ['product_details'],
            'code_number' => ['product_details'],
            'code_image' => ['product_details'],
            'is_published' => ['product_details'],
            'brand_label_with_original_information_image' => ['product_details'],
            'wash_care_with_material_image' => ['product_details'],
            'short_description' => ['product_details'],
            'material_detail' => ['product_details'],
            'product_notice' => ['product_details'],
            'product_code_barcode' => ['product_details'],
            'product_code_boutique' => ['product_details'],
            'product_code_rfid' => ['product_details'],
        ];

        foreach ($activities as $key => $activity)
        {
            if(in_array($key, $track_events))
            {
                $old = $products->getOriginal($key);
                $new = $products->getAttribute($key);
                
                $field = ucwords(str_replace("_", " ", $key));
                $notification_text = '';
                
                if($key && $key == 'brand_id')
                {
                    if($old && !empty($old))
                    {
                       $brandsDetails = Brands::find($old);
                       $oldBrandName = ($brandsDetails && !empty($brandsDetails)) ? $brandsDetails->brand_name : '';
                    }
                    if(isset($products->brand) && !empty($products->brand) && !empty($products->brand->brand_name))
                    {
                        $notification_text = auth()->user()->name.' has updated brand from "'.((isset($oldBrandName) && !empty($oldBrandName)) ? $oldBrandName : '-').'" to "'.$products->brand->brand_name.'"'; 
                    }
                    else
                    {
                        $notification_text = auth()->user()->name.' has updated brand from "'.((isset($oldBrandName) && !empty($oldBrandName)) ? $oldBrandName : '-').'" to "-"';
                    }
                }
                elseif($key && ($key == 'category_level1_id' || $key == 'category_level2_id' || $key == 'category_level3_id' || $key == 'category_level4_id'))
                {
                    $this->objProduct = new Products();                    
                    $getOldCategoryData = $this->objProduct->getCategoryById($old);
                    $getNewCategoryData = $this->objProduct->getCategoryById($new);
                    
                    if($key == 'category_level1_id')
                    {
                        $notification_text = auth()->user()->name.' has updated category level 1 from "'.((isset($getOldCategoryData) && !empty($getOldCategoryData) && !empty($getOldCategoryData->category_name_en)) ? $getOldCategoryData->category_name_en : '-').'" to "'.((isset($getNewCategoryData) && !empty($getNewCategoryData) && !empty($getNewCategoryData->category_name_en)) ? $getNewCategoryData->category_name_en : '-').'"';  
                    }
                    elseif($key == 'category_level2_id')
                    {
                        $notification_text = auth()->user()->name.' has updated category level 2 from "'.((isset($getOldCategoryData) && !empty($getOldCategoryData) && !empty($getOldCategoryData->category_name_en)) ? $getOldCategoryData->category_name_en : '-').'" to "'.((isset($getNewCategoryData) && !empty($getNewCategoryData) && !empty($getNewCategoryData->category_name_en)) ? $getNewCategoryData->category_name_en : '-').'"';
                    }
                    elseif($key == 'category_level3_id')
                    {
                        $notification_text = auth()->user()->name.' has updated category level 3 from "'.((isset($getOldCategoryData) && !empty($getOldCategoryData) && !empty($getOldCategoryData->category_name_en)) ? $getOldCategoryData->category_name_en : '-').'" to "'.((isset($getNewCategoryData) && !empty($getNewCategoryData) && !empty($getNewCategoryData->category_name_en)) ? $getNewCategoryData->category_name_en : '-').'"';
                    }
                    elseif($key == 'category_level4_id')
                    {
                        $notification_text = auth()->user()->name.' has updated category level 4 from "'.((isset($getOldCategoryData) && !empty($getOldCategoryData) && !empty($getOldCategoryData->category_name_en)) ? $getOldCategoryData->category_name_en : '-').'" to "'.((isset($getNewCategoryData) && !empty($getNewCategoryData) && !empty($getNewCategoryData->category_name_en)) ? $getNewCategoryData->category_name_en : '-').'"';
                    }             
                }
                elseif($key && $key == 'is_published')
                {
                    if($new == 1)
                    {
                        $notification_text = auth()->user()->name.' has published this product';
                        Log::info('Product published by '. auth()->user()->name .' - '.auth()->user()->id .'. Product id is '. $products->product_unique_id .' - '. \Request::ip());
                    }
                    else
                    {
                        return true;
                    }
                }
                else
                {
                    $notification_text = auth()->user()->name.' has updated '.$field.' from "'.(!empty($old) ? $old : '-').'" to "'.$new.'"';              
                }
                $pages = (isset($fields_page_mapping[$key]))?$fields_page_mapping[$key]:array();

                if(!empty($pages))
                {
                    foreach ($pages as $page)
                    {
                        $notifications[] = [
                            'notification_text' => $notification_text,
                            'read_by' => auth()->user()->id,
                            'created_by' => auth()->user()->id,
                            'company_id' => $products->company_id,
                            'store_id' => '0',
                            'product_id' => $products->id,
                            'notification_page' => $page,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                    }
                }
            }
        }
        Notifications::insert($notifications);
    }

    public function deleted(Products $products)
    {
        $pages = ['product_details'];
        if(!empty($pages)) 
        {
            $notifications = [];
            foreach($pages as $page) 
            {
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has deleted a products of '.$products->product_name_en,
                    'read_by' => Auth::id(),
                    'created_by' => Auth::id(),
                    'company_id' => $products->company_id,
                    'store_id' => '0',
                    'product_id' => $products->id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            Log::info('Product deleted by '. auth()->user()->name .' - '.auth()->user()->id .'. Product id is '. $products->product_unique_id .' - '. \Request::ip());
            Notifications::insert($notifications);
        }
    }
}