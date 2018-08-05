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

class OrderBoutiqueItemsObservers
{
    public function created(OrderBoutiqueItems $orderBoutiqueItems)
    {

    }

    public function updated(OrderBoutiqueItems $orderBoutiqueItems)
    {
        $activities = array_diff($orderBoutiqueItems->getAttributes(),$orderBoutiqueItems->getOriginal());
        $notifications = [];

        $track_events = [
            'order_id',
            'boutique_id',
            'boutique_unique_code',
            'product_id',
            'product_unique_code',
            'item_size',
            'item_color',
            'item_material',
            'item_warehouse_name',
            'item_confirmed_status',
            'item_shipped_status',
            'item_returned_status',
            'item_refunded_status',
            'delivery_country',
            'import_rate',
            'prepaid_tax',
            'item_wise_subtotal'
        ];

        $fields_page_mapping = [
            'order_id' => ['order_details', 'superadmin_order_details'],
            'boutique_id' => ['order_details', 'superadmin_order_details'],
            'boutique_unique_code' => ['order_details', 'superadmin_order_details'],
            'product_id' => ['order_details', 'superadmin_order_details'],
            'product_unique_code' => ['order_details', 'superadmin_order_details'],
            'item_size' => ['order_details', 'superadmin_order_details'],
            'item_color' => ['order_details', 'superadmin_order_details'],
            'item_material' => ['order_details', 'superadmin_order_details'],
            'item_warehouse_name' => ['order_details', 'superadmin_order_details'],
            'item_confirmed_status' => ['order_details', 'superadmin_order_details'],
            'item_shipped_status' => ['order_details', 'superadmin_order_details'],
            'item_returned_status' => ['order_details', 'superadmin_order_details'],
            'item_refunded_status' => ['order_details', 'superadmin_order_details'],
            'delivery_country' => ['order_details', 'superadmin_order_details'],
            'import_rate' => ['order_details', 'superadmin_order_details'],
            'prepaid_tax' => ['order_details', 'superadmin_order_details'],
            'item_wise_subtotal' => ['order_details', 'superadmin_order_details'],
        ];

        foreach ($activities as $key => $activity)
        {
            if(in_array($key,$track_events))
            {
                $old = $orderBoutiqueItems->getOriginal($key);
                $new = $orderBoutiqueItems->getAttribute($key);

                $field = ucwords(str_replace("_"," ",$key));

                $oldName = '';
                $newName = '';

                if($key && $key == 'item_confirmed_status')
                {
                    if($old == 1)
                    {
                        $oldName = 'In stock';
                    }
                    elseif($old == 2)
                    {
                        $oldName = 'Out of stock';
                    }

                    if($new == 1)
                    {
                        $newName = 'In stock';
                    }
                    elseif($new == 2)
                    {
                        $newName = 'Out of stock';
                    }
                }
                if($key && $key == 'item_shipped_status')
                {
                    if($old == 0)
                    {
                        $oldName = 'No';
                    }
                    elseif($old == 1)
                    {
                        $oldName = 'Yes';
                    }

                    if($new == 1)
                    {
                        $newName = 'No';
                    }
                    elseif($new == 2)
                    {
                        $newName = 'Yes';
                    }
                }
                if($key && $key == 'item_returned_status')
                {
                    if($old == 0)
                    {
                        $oldName = 'No';
                    }
                    elseif($old == 1)
                    {
                        $oldName = 'Yes';
                    }

                    if($new == 1)
                    {
                        $newName = 'No';
                    }
                    elseif($new == 2)
                    {
                        $newName = 'Yes';
                    }
                }
                if($key && $key == 'item_refunded_status')
                {
                    if($old == 0)
                    {
                        $oldName = 'No';
                    }
                    elseif($old == 1)
                    {
                        $oldName = 'Yes';
                    }

                    if($new == 1)
                    {
                        $newName = 'No';
                    }
                    elseif($new == 2)
                    {
                        $newName = 'Yes';
                    }
                }

                $notification_text = auth()->user()->name.' has updated '.$field.' from "'.(!empty($oldName) ? $oldName : '-').'" to "'.$newName.'"';
                $pages = ( isset($fields_page_mapping[$key])) ? $fields_page_mapping[$key] : array();

                if(!empty($pages))
                {
                    foreach ($pages as $page)
                    {
                        $notifications[] = [
                            'notification_text' => $notification_text,
                            'read_by' => auth()->user()->id,
                            'created_by' => auth()->user()->id,
                            'company_id' => $orderBoutiqueItems->boutique_id,
                            'order_id' => $orderBoutiqueItems->order_id,
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

    public function deleted(OrderBoutiqueItems $orderBoutiqueItems)
    {

    }
}
