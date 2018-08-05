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
use App\ProductImages;
use App\Notifications;

class ProductImagesObservers
{
    public function created(ProductImages $productImages)
    {
        $pages = ['product_details'];
        if(!empty($pages)) 
        {
            $notifications = [];
            foreach($pages as $page) 
            {
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has created a new product image '.$productImages->file_name,
                    'read_by' => Auth::id(),
                    'created_by' => Auth::id(),
                    'company_id' => (isset($productImages->product) && !empty($productImages->product) && !empty($productImages->product->company_id)) ? $productImages->product->company_id : 0,
                    'store_id' => '0',
                    'product_id' => $productImages->product_id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            Notifications::insert($notifications);
        }
    }

    public function updated(ProductImages $productImages)
    {
        $activities = array_diff($productImages->getAttributes(), $productImages->getOriginal());
        $notifications = [];

        $track_events = [
            'file_name',
            'file_position',
        ];

        $fields_page_mapping = [
            'file_name' => ['product_details'],
            'file_position' => ['product_details'],            
        ];

        foreach ($activities as $key => $activity)
        {
            if(in_array($key, $track_events))
            {
                $old = $productImages->getOriginal($key);
                $new = $productImages->getAttribute($key);

                $field = ucwords(str_replace("_", " ", $key));
                
                $notification_text = auth()->user()->name.' has updated product image from "'.(!empty($old) ? $old : '-').'" to "'.$new.'"';                
                $pages = (isset($fields_page_mapping[$key]))?$fields_page_mapping[$key]:array();

                if(!empty($pages))
                {
                    foreach ($pages as $page)
                    {
                        $notifications[] = [
                            'notification_text' => $notification_text,
                            'read_by' => auth()->user()->id,
                            'created_by' => auth()->user()->id,
                            'company_id' => (isset($productImages->product) && !empty($productImages->product) && !empty($productImages->product->company_id)) ? $productImages->product->company_id : 0,
                            'store_id' => '0',
                            'product_id' => $productImages->product_id,
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

    public function deleted(ProductImages $productImages)
    {
        $pages = ['product_details'];
        if(!empty($pages))
        {
            $notifications = [];
            foreach($pages as $page)
            {
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has deleted a product image of '.$productImages->file_name,
                    'read_by' => Auth::id(),
                    'created_by' => Auth::id(),
                    'company_id' => (isset($productImages->product) && !empty($productImages->product) && !empty($productImages->product->company_id)) ? $productImages->product->company_id : 0,
                    'store_id' => '0',
                    'product_id' => $productImages->product_id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            Notifications::insert($notifications);
        }
    }
}
