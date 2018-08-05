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
use App\Notifications;

class CategoriesObservers
{
    public function created(Categories $category)
    {
        $pages = ['category_details'];
	if(!empty($pages)) 
	{
            $notifications = [];
            foreach($pages as $page) 
            {
                if(isset($category) && !empty($category))
                {
                    $getName = Helpers::getMultipleLanguageName($category, 'category');
                }
                else
                {
                    $getName = '';
                }
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has created a new category '.(!empty($getName) ? $getName : '-'),
                    'read_by' => auth()->user()->id,
                    'created_by' => auth()->user()->id,
                    'company_id' => '0',
                    'store_id' => '0',
                    'product_id' => '0',
                    'category_id' => $category->id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            Notifications::insert($notifications);
	}
    }

    public function updated(Categories $category)
    {  
        $activities = array_diff($category->getAttributes(), $category->getOriginal());
        $notifications = [];

        $track_events = [
            'is_parent',
            'category_name_en',
            'category_name_ch',        
            'category_name_ge',        
            'category_name_fr',        
            'category_name_it',        
            'category_name_sp',        
            'category_name_ru',        
            'category_name_jp',        
            'category_unique_id',        
            'category_level',        
        ];

        $fields_page_mapping = [
            'is_parent' => ['category_details'],
            'category_name_en' => ['category_details'],
            'category_name_ch' => ['category_details'],
            'category_name_ge' => ['category_details'],
            'category_name_fr' => ['category_details'],
            'category_name_it' => ['category_details'],
            'category_name_sp' => ['category_details'],
            'category_name_ru' => ['category_details'],
            'category_name_jp' => ['category_details'],
            'category_unique_id' => ['category_details'],
        ];

        foreach ($activities as $key => $activity)
        {
            if(in_array($key, $track_events))
            {
                $old = $category->getOriginal($key);
                $new = $category->getAttribute($key);
                
                $field = ucwords(str_replace("_", " ", $key));
                $notification_text = '';
                
                if($key && $key == 'is_parent')
                {
                    $this->objCategory = new Categories();                    
                    $getOldCategoryData = $this->objCategory->getCategoryById($old);
                    $getNewCategoryData = $this->objCategory->getCategoryById($new);
                    $getOldName = '';
                    $getNewName = '';
                    if(isset($getOldCategoryData) && !empty($getOldCategoryData))
                    {
                        $getOldName = Helpers::getMultipleLanguageName($getOldCategoryData, 'category');
                    }
                    if(isset($getNewCategoryData) && !empty($getNewCategoryData))
                    {
                        $getNewName = Helpers::getMultipleLanguageName($getNewCategoryData, 'category');
                    }
                    $notification_text = auth()->user()->name.' has updated parent category from "'.$getOldName.'" to "'.$getNewName.'"';                         
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
                            'company_id' => '0',
                            'store_id' => '0',
                            'product_id' => '0',
                            'category_id' => $category->id,
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

    public function deleted(Categories $category)
    {
        $pages = ['category_details'];
        if(!empty($pages)) 
        {
            $notifications = [];
            foreach($pages as $page) 
            {
                if(isset($category) && !empty($category))
                {
                    $getName = Helpers::getMultipleLanguageName($category, 'category');
                }
                else
                {
                    $getName = '';
                }
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has deleted a category of '.(!empty($getName) ? $getName : '-'),
                    'read_by' => auth()->user()->id,
                    'created_by' => auth()->user()->id,
                    'company_id' => '0',
                    'store_id' => '0',
                    'product_id' => '0',
                    'category_id' => $category->id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            Notifications::insert($notifications);
        }
    }
}
