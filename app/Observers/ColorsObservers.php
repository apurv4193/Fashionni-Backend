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
use App\Colors;
use App\Notifications;

class ColorsObservers
{
    public function created(Colors $color)
    {
        $pages = ['color_details'];
    	if(!empty($pages))
    	{
                $notifications = [];
                foreach($pages as $page)
                {
                    if(isset($color) && !empty($color))
                    {
                        $getName = Helpers::getMultipleLanguageName($color, 'color');
                    }
                    else
                    {
                        $getName = '';
                    }
                    $notifications[] = [
                        'notification_text' => auth()->user()->name.' has created a new color '.(!empty($getName) ? $getName : '-'),
                        'read_by' => auth()->user()->id,
                        'created_by' => auth()->user()->id,
                        'company_id' => '0',
                        'store_id' => '0',
                        'product_id' => '0',
                        'category_id' => '0',
                        'color_id' => $color->id,
                        'notification_page' => $page,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                }
                Notifications::insert($notifications);
    	}
        }

    public function updated(Colors $color)
    {
        $activities = array_diff($color->getAttributes(), $color->getOriginal());
        $notifications = [];

        $track_events = [
            'color_name_en',
            'color_name_ch',
            'color_name_ge',
            'color_name_fr',
            'color_name_it',
            'color_name_sp',
            'color_name_ru',
            'color_name_jp',
            'color_unique_id',
            'color_image',
        ];

        $fields_page_mapping = [
            'color_name_en' => ['color_details'],
            'color_name_ch' => ['color_details'],
            'color_name_ge' => ['color_details'],
            'color_name_fr' => ['color_details'],
            'color_name_it' => ['color_details'],
            'color_name_sp' => ['color_details'],
            'color_name_ru' => ['color_details'],
            'color_name_jp' => ['color_details'],
            'color_unique_id' => ['color_details'],
            'color_image' => ['color_details'],
        ];

        foreach ($activities as $key => $activity)
        {
            if(in_array($key, $track_events))
            {
                $old = $color->getOriginal($key);
                $new = $color->getAttribute($key);

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
                            'color_id' => $color->id,
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

    public function deleted(Colors $color)
    {
        $pages = ['color_details'];
        if(!empty($pages))
        {
            $notifications = [];
            foreach($pages as $page)
            {
                if(isset($color) && !empty($color))
                {
                    $getName = Helpers::getMultipleLanguageName($color, 'color');
                }
                else
                {
                    $getName = '';
                }
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has deleted a color of '.(!empty($getName) ? $getName : '-'),
                    'read_by' => auth()->user()->id,
                    'created_by' => auth()->user()->id,
                    'company_id' => '0',
                    'store_id' => '0',
                    'product_id' => '0',
                    'category_id' => '0',
                    'color_id' => $color->id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            Notifications::insert($notifications);
        }
    }
}
