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
use App\OrderBoutiqueItems;
use App\OrderBoutiqueAttributes;
use App\OrderBoutiqueDocuments;

class OrderBoutiqueDocumentsObservers
{
    public function created(OrderBoutiqueDocuments $orderBoutiqueDocuments)
    {

    }

    public function updated(OrderBoutiqueDocuments $orderBoutiqueDocuments)
    {
        $activities = array_diff($orderBoutiqueDocuments->getAttributes(),$orderBoutiqueDocuments->getOriginal());
        $notifications = [];

        $track_events = [
            'order_id',
            'boutique_id',
            'boutique_unique_code',
            'invoice_doc',
            'parcel_doc',
            'export_doc',
            'others_doc'
        ];

        $fields_page_mapping = [
            'order_id' => ['order_details', 'superadmin_order_details'],
            'boutique_id' => ['order_details', 'superadmin_order_details'],
            'boutique_unique_code' => ['order_details', 'superadmin_order_details'],
            'invoice_doc' => ['order_details', 'superadmin_order_details'],
            'parcel_doc' => ['order_details', 'superadmin_order_details'],
            'export_doc' => ['order_details', 'superadmin_order_details'],
            'others_doc' => ['order_details', 'superadmin_order_details']
        ];

        foreach ($activities as $key => $activity)
        {
            if(in_array($key, $track_events))
            {
                $old = $orderBoutiqueDocuments->getOriginal($key);
                $new = $orderBoutiqueDocuments->getAttribute($key);

                $field = ucwords(str_replace("_"," ",$key));

                $notification_text = '';

                if($key && $key == 'invoice_doc')
                {
                    if($new && !empty($new))
                    {
                        $notification_text = auth()->user()->name.' has updated '.$field.' from "'.(!empty($old) ? $old : '-').'" to "'.$new.'"';
                    }
                    else
                    {
                        $notification_text = auth()->user()->name.' has deleted a invoice document';
                    }

                }
                if($key && $key == 'parcel_doc')
                {
                    if($new && !empty($new))
                    {
                        $notification_text = auth()->user()->name.' has updated '.$field.' from "'.(!empty($old) ? $old : '-').'" to "'.$new.'"';
                    }
                    else
                    {
                        $notification_text = auth()->user()->name.' has deleted a parcel document';
                    }
                }
                if($key && $key == 'export_doc')
                {
                    if($new && !empty($new))
                    {
                        $notification_text = auth()->user()->name.' has updated '.$field.' from "'.(!empty($old) ? $old : '-').'" to "'.$new.'"';
                    }
                    else
                    {
                        $notification_text = auth()->user()->name.' has deleted a export document';
                    }
                }

                if($key && $key == 'others_doc')
                {
                    if($new && !empty($new))
                    {
                        $notification_text = auth()->user()->name.' has updated '.$field.' from "'.(!empty($old) ? $old : '-').'" to "'.$new.'"';
                    }
                    else
                    {
                        $notification_text = auth()->user()->name.' has deleted a other document';
                    }
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
                            'company_id' => $orderBoutiqueDocuments->boutique_id,
                            'order_id' => $orderBoutiqueDocuments->order_id,
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

    public function deleted(OrderBoutiqueDocuments $orderBoutiqueDocuments)
    {

    }
}
