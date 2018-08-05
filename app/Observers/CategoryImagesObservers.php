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
use App\Categories;
use App\CategoryImages;
use App\Notifications;

class CategoryImagesObservers
{
    public function created(CategoryImages $categoryImages)
    {
        $pages = ['category_details'];
        if(!empty($pages)) 
        {
            $notifications = [];
            foreach($pages as $page) 
            {
                $categoryName = '';
                if(isset($categoryImages->categroy) && !empty($categoryImages->categroy))
                {
                    $categoryName = Helpers::getMultipleLanguageName($categoryImages->categroy, 'category');
                }        
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has created a new '.$categoryImages->file_name.' category image for '.$categoryName.' category',
                    'read_by' => auth()->user()->id,
                    'created_by' => auth()->user()->id,
                    'company_id' => '0',
                    'store_id' => '0',
                    'product_id' => '0',
                    'category_id' => $categoryImages->category_id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            Notifications::insert($notifications);
        }
    }

    public function updated(CategoryImages $categoryImages)
    {
        $activities = array_diff($categoryImages->getAttributes(), $categoryImages->getOriginal());
        $notifications = [];

        $track_events = [
            'category_id',
            'file_name',
        ];

        $fields_page_mapping = [
            'category_id' => ['category_details'],
            'file_name' => ['category_details'],            
        ];

        foreach ($activities as $key => $activity)
        {
            if(in_array($key, $track_events))
            {
                $old = $categoryImages->getOriginal($key);
                $new = $categoryImages->getAttribute($key);

                $field = ucwords(str_replace("_", " ", $key));
                
                $notification_text = auth()->user()->name.' has updated category image from "'.(!empty($old) ? $old : '-').'" to "'.$new.'"';                
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
                            'category_id' => $categoryImages->category_id,
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

    public function deleted(CategoryImages $categoryImages)
    {
        $pages = ['category_details'];
        if(!empty($pages))
        {
            $notifications = [];
            foreach($pages as $page)
            {
                $categoryName = '';
                if(isset($categoryImages->categroy) && !empty($categoryImages->categroy))
                {
                    $categoryName = Helpers::getMultipleLanguageName($categoryImages->categroy, 'category');
                } 
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has deleted a '.$categoryImages->file_name.' category image for '.$categoryName.' category',
                    'read_by' => auth()->user()->id,
                    'created_by' => auth()->user()->id,
                    'company_id' => '0',
                    'store_id' => '0',
                    'product_id' => '0',
                    'category_id' => $categoryImages->category_id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            Notifications::insert($notifications);
        }
    }
}
