<?php
/**
 * Created by Apurv Prajapati.
 * Date: 08-06-2018
 * Time: 10:29 AM IST
 */

namespace App\Observers;


use App\Company;
use App\Notifications;
use App\Orders;
use App\OrderBoutiqueAttributes;
use App\OrderBoutiqueItems;
use App\OrderBoutiqueDocuments;

class OrderBoutiqueAttributesObservers
{
    public function created(OrderBoutiqueAttributes $orderBoutiqueAttributes) {

    }

    public function updated(OrderBoutiqueAttributes $orderBoutiqueAttributes)
    {
        $activities = array_diff($orderBoutiqueAttributes->getAttributes(),$orderBoutiqueAttributes->getOriginal());
        $notifications = [];

        $track_events = [
            'order_id',
            'boutique_id',
            'boutique_unique_code',
            'confirmed_items',
            'package_weight',
            'package_box_name',
            'package_size',
            'package_volumetric_weight',
        ];

        $fields_page_mapping = [
            'order_id' => ['order_details', 'superadmin_order_details'],
            'boutique_id' => ['order_details', 'superadmin_order_details'],
            'boutique_unique_code' => ['order_details', 'superadmin_order_details'],
            'confirmed_items' => ['order_details', 'superadmin_order_details'],
            'package_weight' => ['order_details', 'superadmin_order_details'],
            'package_box_name' => ['order_details', 'superadmin_order_details'],
            'package_size' => ['order_details', 'superadmin_order_details'],
            'package_volumetric_weight' => ['order_details', 'superadmin_order_details']
        ];

        foreach ($activities as $key => $activity)
        {
            if(in_array($key,$track_events))
            {
                $old = $orderBoutiqueAttributes->getOriginal($key);
                $new = $orderBoutiqueAttributes->getAttribute($key);

                $field = ucwords(str_replace("_"," ",$key));

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
                            'company_id' => $orderBoutiqueAttributes->boutique_id,
                            'order_id' => $orderBoutiqueAttributes->order_id,
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

    public function deleted(OrderBoutiqueAttributes $orderBoutiqueAttributes)
    {

    }
}
