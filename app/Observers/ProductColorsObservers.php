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
use App\ProductColors;
use App\Notifications;

class ProductColorsObservers
{
    public function created(ProductColors $productColors)
    {
        $pages = ['product_details'];
        if(!empty($pages)) 
        {
            $notifications = [];
            foreach($pages as $page) 
            {
                if(isset($productColors->color) && !empty($productColors->color))
                {
                    $getName = Helpers::getMultipleLanguageName($productColors->color, 'color');
                }
                else
                {
                    $getName = '';
                }
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has created a new product color '.(!empty($getName) ? $getName : '-'),
                    'read_by' => Auth::id(),
                    'created_by' => Auth::id(),
                    'company_id' => (isset($productColors->product) && !empty($productColors->product) && !empty($productColors->product->company_id)) ? $productColors->product->company_id : 0,
                    'store_id' => '0',
                    'product_id' => $productColors->product_id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            Notifications::insert($notifications);
        }
    }

    public function updated(ProductColors $productColors)
    {
        $activities = array_diff($productColors->getAttributes(), $productColors->getOriginal());
        $notifications = [];

        $track_events = [
            'product_id',
            'color_id',            
        ];

        $fields_page_mapping = [
            'product_id' => ['product_details'],
            'color_id' => ['product_details'],
        ];

        foreach ($activities as $key => $activity)
        {
            if(in_array($key, $track_events))
            {
                $old = $productColors->getOriginal($key);
                $new = $productColors->getAttribute($key);

                $field = ucwords(str_replace("_", " ", $key));
                $notification_text = '';

                $notification_text = auth()->user()->name.' has updated color '.$field.' from "'.(!empty($old) ? $old : '-').'" to "'.$new.'"';
                $pages = (isset($fields_page_mapping[$key]))?$fields_page_mapping[$key]:array();

                if(!empty($pages))
                {
                    foreach ($pages as $page)
                    {
                        $notifications[] = [
                            'notification_text' => $notification_text,
                            'read_by' => auth()->user()->id,
                            'created_by' => auth()->user()->id,
                            'company_id' => (isset($productColors->product) && !empty($productColors->product) && !empty($productColors->product->company_id)) ? $productColors->product->company_id : 0,
                            'store_id' => '0',
                            'product_id' => $productColors->product_id,
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

    public function deleted(ProductColors $productColors)
    {
        $pages = ['product_details'];
        if(!empty($pages))
        {
            $notifications = [];
            foreach($pages as $page)
            {
                if(isset($productColors->color) && !empty($productColors->color))
                {
                    $getName = Helpers::getMultipleLanguageName($productColors->color, 'color');
                }
                else
                {
                    $getName = '';
                }
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has deleted a product color '.(!empty($getName) ? $getName : '-'),
                    'read_by' => Auth::id(),
                    'created_by' => Auth::id(),
                    'company_id' => (isset($productColors->product) && !empty($productColors->product) && !empty($productColors->product->company_id)) ? $productColors->product->company_id : 0,
                    'store_id' => '0',
                    'product_id' => $productColors->product_id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            Notifications::insert($notifications);
        }
    }
}
