<?php
/**
 * Created by Apurv Prajapati.
 * Date: 19-04-2018
 * Time: 10:26 AM IST
 */

namespace App\Observers;

use Auth;
use App\Products;
use App\ProductInventory;
use App\Notifications;

class ProductInventoryObservers
{
    public function created(ProductInventory $productInventory)
    {
        $pages = ['product_details'];
        if(!empty($pages)) 
        {
            $notifications = [];
            foreach($pages as $page) 
            {
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has created a new product inventory '.$productInventory->fashionni_id,
                    'read_by' => Auth::id(),
                    'created_by' => Auth::id(),
                    'company_id' => (isset($productInventory->product) && !empty($productInventory->product) && !empty($productInventory->product->company_id)) ? $productInventory->product->company_id : 0,
                    'store_id' => '0',
                    'product_id' => $productInventory->product_id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            Notifications::insert($notifications);
        }
    }

    public function updated(ProductInventory $productInventory)
    {
        $activities = array_diff($productInventory->getAttributes(), $productInventory->getOriginal());
        $notifications = [];

        $track_events = [
            'product_id',
            'fashionni_id',
            'product_standard',
            'product_size',
            'product_quantity',
            'product_warehouse',
            'sold_by',
            'product_inventory_unique_id',
        ];

        $fields_page_mapping = [
            'product_id' => ['product_details'],
            'fashionni_id' => ['product_details'],
            'product_standard' => ['product_details'],
            'product_size' => ['product_details'],
            'product_quantity' => ['product_details'],
            'product_warehouse' => ['product_details'],
            'sold_by' => ['product_details'],
            'product_inventory_unique_id' => ['product_details'],
        ];

        foreach ($activities as $key => $activity)
        {
            if(in_array($key, $track_events))
            {
                $old = $productInventory->getOriginal($key);
                $new = $productInventory->getAttribute($key);

                $field = ucwords(str_replace("_", " ", $key));
                
                if($key && $key == 'product_id')
                {
                    if($old && !empty($old))
                    {
                        $productsDetails = Products::find($old);
                        if($productsDetails && !empty($productsDetails))
                        {
                           $getName = Helpers::getMultipleLanguageName($productsDetails, 'product');
                        }
                        else
                        {
                           $getName = '';
                        }
                       $oldBrandName = ($getName && !empty($getName)) ? $getName : '';
                    }
                    if($new && !empty($new))
                    {
                        $productsDetails = Products::find($new);
                        if($productsDetails && !empty($productsDetails))
                        {
                           $getName = Helpers::getMultipleLanguageName($productsDetails, 'product');
                        }
                        else
                        {
                           $getName = '';
                        }
                       $newBrandName = ($getName && !empty($getName)) ? $getName : '';
                    }
                    $notification_text = auth()->user()->name.' has updated product inventory of product from "'.(isset($oldBrandName) && !empty($oldBrandName) ? $oldBrandName : '-').'" to "'.(isset($newBrandName) && !empty($newBrandName) ? $newBrandName : '-').'"';
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
                            'company_id' => (isset($productInventory->product) && !empty($productInventory->product) && !empty($productInventory->product->company_id)) ? $productInventory->product->company_id : 0,
                            'store_id' => '0',
                            'product_id' => $productInventory->product_id,
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

    public function deleted(ProductInventory $productInventory)
    {
        $pages = ['product_details'];
        if(!empty($pages))
        {
            $notifications = [];
            foreach($pages as $page)
            {
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has deleted a product inventory of '.$productInventory->fashionni_id,
                    'read_by' => Auth::id(),
                    'created_by' => Auth::id(),
                    'company_id' => (isset($productInventory->product) && !empty($productInventory->product) && !empty($productInventory->product->company_id)) ? $productInventory->product->company_id : 0,
                    'store_id' => '0',
                    'product_id' => $productInventory->product_id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            Notifications::insert($notifications);
        }
    }
}
