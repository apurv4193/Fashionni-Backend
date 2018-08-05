<?php
/**
 * Created by Apurv Prajapati.
 * Date: 26-04-2018
 * Time: 11:26 AM IST
 */

namespace App\Observers;

use Auth;
use Helpers;
use Config;
use App\Brands;
use App\Notifications;

class BrandsObservers
{
    public function created(Brands $brand)
    {
        $pages = ['brand_details'];
	if(!empty($pages)) 
	{
            $notifications = [];
            foreach($pages as $page) 
            {
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has created a new brand '.(!empty($brand->brand_name) ? $brand->brand_name : '-'),
                    'read_by' => auth()->user()->id,
                    'created_by' => auth()->user()->id,
                    'company_id' => '0',
                    'store_id' => '0',
                    'product_id' => '0',
                    'category_id' => '0',
                    'color_id' => '0',
                    'brand_id' => $brand->id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            Notifications::insert($notifications);
	}
    }

    public function updated(Brands $brand)
    {  
        $activities = array_diff($brand->getAttributes(), $brand->getOriginal());
        $notifications = [];

        $track_events = [
            'brand_name',
            'brand_image',
        ];

        $fields_page_mapping = [
            'brand_name' => ['brand_details'],
            'brand_image' => ['brand_details'],
        ];

        foreach ($activities as $key => $activity)
        {
            if(in_array($key, $track_events))
            {
                $old = $brand->getOriginal($key);
                $new = $brand->getAttribute($key);
                
                $field = ucwords(str_replace("_", " ", $key));
                
                $notification_text = auth()->user()->name.' has updated '.$field.' from "'.(!empty($old) ? $old : '-').'" to "'.$new.'"';        
                $pages = (isset($fields_page_mapping[$key]))?$fields_page_mapping[$key]:array();

                if(!empty($pages))
                {
                    foreach ($pages as $page)
                    {
                        $notifications[] = [
                            'notification_text' => $notification_text,
                            'read_by' => auth()->user()->id,
                            'created_by' => auth()->user()->id,
                            'company_id' => '0',
                            'store_id' => '0',
                            'product_id' => '0',
                            'category_id' => '0',
                            'color_id' => '0',
                            'brand_id' => $brand->id,
                            'notification_page' => $page,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                }
            }
        }
        Notifications::insert($notifications);
    }

    public function deleted(Brands $brand)
    {
        $pages = ['brand_details'];
        if(!empty($pages)) 
        {
            $notifications = [];
            foreach($pages as $page) 
            {
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has deleted a brand of '.(!empty($brand->brand_name) ? $brand->brand_name : '-'),
                    'read_by' => auth()->user()->id,
                    'created_by' => auth()->user()->id,
                    'company_id' => '0',
                    'store_id' => '0',
                    'product_id' => '0',
                    'category_id' => '0',
                    'color_id' => '0',
                    'brand_id' => $brand->id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            Notifications::insert($notifications);
        }
    }
}
