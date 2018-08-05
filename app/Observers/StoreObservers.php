<?php
/**
 * Created by Krutik Patel.
 * Date: 10-04-2018
 * Time: 10:26 AM IST
 */

namespace App\Observers;

use Auth;
use Helpers;
use Storage;
use App\Store;
use App\Notifications;

class StoreObservers
{
    public function created(Store $store) 
    {
        $pages = ['superadmin_company_edit_partial'];
        if(!empty($pages)) 
        {
            $notifications = [];
            foreach($pages as $page) 
            {
                $notifications[] = [
//                  'notification_text' => $store->store_name.' Store Created By '.auth()->user()->name,
                    'notification_text' => auth()->user()->name.' has created a new store '.$store->store_name,
                    
                    'read_by' => auth()->user()->id,
                    'created_by' => auth()->user()->id,
                    'company_id' => $store->company_id,
                    'store_id' => $store->id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }

            Notifications::insert($notifications);
        }
    }

    public function updated(Store $store) {
        $activities = array_diff($store->getAttributes(),$store->getOriginal());
        $notifications = [];

        $track_events = [
            'store_name',
            'store_image',
            'short_name',
            'address',
            'postal_code',
            'city',
            'state',
            'country',
            'store_contact_person_name',
            'store_contact_person_email',
            'store_contact_person_image',
            'store_contact_person_telephone',
            'store_contact_person_position',
        ];

        $fields_page_mapping = [
            'store_name' => ['admin_store_edit', 'superadmin_company_edit_partial'],
            'store_image' => ['admin_store_edit', 'superadmin_company_edit_partial'],
            'short_name' => ['admin_store_edit', 'superadmin_company_edit_partial'],
            'address' => ['admin_store_edit', 'superadmin_company_edit_partial'],
            'postal_code' => ['admin_store_edit', 'superadmin_company_edit_partial'],
            'city' => ['admin_store_edit', 'superadmin_company_edit_partial'],
            'state' => ['admin_store_edit', 'superadmin_company_edit_partial'],
            'country' => ['admin_store_edit', 'superadmin_company_edit_partial'],
            'store_contact_person_name' => ['admin_store_edit'],
            'store_contact_person_email' => ['admin_store_edit'],
            'store_contact_person_image' => ['admin_store_edit'],
            'store_contact_person_telephone' => ['admin_store_edit'],
            'store_contact_person_position' => ['admin_store_edit'],
        ];

        foreach ($activities as $key => $activity) 
        {
            if(in_array($key,$track_events)) 
            {
                $old = $store->getOriginal($key);
                $new = $store->getAttribute($key);

                $field = ucwords(str_replace("_"," ",$key));
                
//              $notification_text = auth()->user()->name." Updated {$field} from {$old} to {$new}";
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
                            'company_id' => $store->company_id,
                            'store_id' => $store->id,
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

    public function deleted(Store $store) 
    {
        $pages = ['superadmin_company_edit_partial'];
        if(!empty($pages)) 
        {
            $notifications = [];
            foreach($pages as $page) 
            {
                $notifications[] = [                    
//                  'notification_text' => auth()->user()->name.' Deleted Store ' . $store->store_name,
                    'notification_text' => auth()->user()->name.' has deleted a store of '.$store->store_name,
                    
                    'read_by' => auth()->user()->id,
                    'created_by' => auth()->user()->id,
                    'company_id' => $store->company_id,
                    'store_id' => $store->id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            Notifications::insert($notifications);
        }
    }
}