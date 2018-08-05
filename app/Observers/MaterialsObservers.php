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
use App\Materials;
use App\Notifications;

class MaterialsObservers
{
    public function created(Materials $material)
    {
        $pages = ['material_details'];
	if(!empty($pages)) 
	{
            $notifications = [];
            foreach($pages as $page) 
            {
                if(isset($material) && !empty($material))
                {
                    $getName = Helpers::getMultipleLanguageName($material, 'material');
                }
                else
                {
                    $getName = '';
                }
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has created a new material '.(!empty($getName) ? $getName : '-'),
                    'read_by' => auth()->user()->id,
                    'created_by' => auth()->user()->id,
                    'company_id' => '0',
                    'store_id' => '0',
                    'product_id' => '0',
                    'category_id' => '0',
                    'color_id' => '0',
                    'material_id' => $material->id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            Notifications::insert($notifications);
	}
    }

    public function updated(Materials $material)
    {  
        $activities = array_diff($material->getAttributes(), $material->getOriginal());
        $notifications = [];

        $track_events = [
            'material_name_en',
            'material_name_ch',        
            'material_name_ge',        
            'material_name_fr',        
            'material_name_it',        
            'material_name_sp',        
            'material_name_ru',        
            'material_name_jp',     
            'material_unique_id',     
            'material_image',     
        ];

        $fields_page_mapping = [
            'material_name_en' => ['material_details'],
            'material_name_ch' => ['material_details'],
            'material_name_ge' => ['material_details'],
            'material_name_fr' => ['material_details'],
            'material_name_it' => ['material_details'],
            'material_name_sp' => ['material_details'],
            'material_name_ru' => ['material_details'],
            'material_name_jp' => ['material_details'],
            'material_unique_id' => ['material_details'],
            'material_image' => ['material_details'],
        ];

        foreach ($activities as $key => $activity)
        {
            if(in_array($key, $track_events))
            {
                $old = $material->getOriginal($key);
                $new = $material->getAttribute($key);
                
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
                            'material_id' => $material->id,
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

    public function deleted(Materials $material)
    {
        $pages = ['material_details'];
        if(!empty($pages)) 
        {
            $notifications = [];
            foreach($pages as $page) 
            {
                if(isset($material) && !empty($material))
                {
                    $getName = Helpers::getMultipleLanguageName($material, 'material');
                }
                else
                {
                    $getName = '';
                }
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has deleted a material of '.(!empty($getName) ? $getName : '-'),
                    'read_by' => auth()->user()->id,
                    'created_by' => auth()->user()->id,
                    'company_id' => '0',
                    'store_id' => '0',
                    'product_id' => '0',
                    'category_id' => '0',
                    'color_id' => '0',
                    'material_id' => $material->id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            Notifications::insert($notifications);
        }
    }
}
