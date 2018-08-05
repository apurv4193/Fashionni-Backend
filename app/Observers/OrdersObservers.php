<?php
/**
 * Created by Apurv Prajapati.
 * Date: 08-06-2018
 * Time: 10:26 AM IST
 */

namespace App\Observers;


use App\Company;
use App\Notifications;
use App\Orders;
use App\OrderBoutiqueAttributes;
use App\OrderBoutiqueItems;
use App\OrderBoutiqueDocuments;

class OrdersObservers
{
    public function created(Orders $orders)
    {

    }

    public function updated(Orders $orders)
    {
        $activities = array_diff($orders->getAttributes(), $orders->getOriginal());
        $notifications = [];

        $track_events = [
            'order_number',
            'order_date',
            'purchased_from',
            'payment_by',
            'order_status',
            'billing_address',
            'delivery_address',
            'boutique_count',
            'items_count',
            'shipping_type',
            'shipping_price',
            'group_name',
            'eu_countires'
        ];

        $fields_page_mapping = [
            'order_number' => ['order_details', 'superadmin_order_details'],
            'order_date' => ['order_details', 'superadmin_order_details'],
            'payment_by' => ['order_details', 'superadmin_order_details'],
            'order_status' => ['order_details', 'superadmin_order_details'],
            'billing_address' => ['order_details', 'superadmin_order_details'],
            'delivery_address' => ['order_details', 'superadmin_order_details'],
            'boutique_count' => ['order_details', 'superadmin_order_details'],
            'items_count' => ['order_details', 'superadmin_order_details'],
            'shipping_type' => ['order_details', 'superadmin_order_details'],
            'shipping_price' => ['order_details', 'superadmin_order_details'],
            'group_name' => ['order_details', 'superadmin_order_details'],
            'eu_countires' => ['order_details', 'superadmin_order_details'],
        ];

        foreach ($activities as $key => $activity)
        {
            if(in_array($key, $track_events))
            {
                $old = $orders->getOriginal($key);
                $new = $orders->getAttribute($key);

                $field = ucwords(str_replace("_", " ", $key));

                $notification_text = '';
                $oldName = '';
                $newName = '';
                if($key && $key == 'order_status')
                {
                    if($old == 1)
                    {
                        $oldName = 'Ordered';
                    }
                    elseif($old == 2)
                    {
                        $oldName = 'Shipped';
                    }
                    elseif($old == 3)
                    {
                        $oldName = 'Arrived';
                    }
                    elseif($old == 4)
                    {
                        $oldName = 'Retured';
                    }
                    elseif($old == 5)
                    {
                        $oldName = 'Closed';
                    }

                    if($new == 1)
                    {
                        $newName = 'Ordered';
                    }
                    elseif($new == 2)
                    {
                        $newName = 'Shipped';
                    }
                    elseif($new == 3)
                    {
                        $newName = 'Arrived';
                    }
                    elseif($new == 4)
                    {
                        $newName = 'Returned';
                    }
                    elseif($new == 5)
                    {
                        $newName = 'Closed';
                    }

                    $notification_text = auth()->user()->name.' has updated '.$field.' from "'.(!empty($oldName) ? $oldName : '-').'" to "'.$newName.'"';
                }

                $pages = (isset($fields_page_mapping[$key])) ? $fields_page_mapping[$key] : array();

                if(!empty($pages))
                {
                    foreach ($pages as $page)
                    {
                        if($page == 'order_details')
                        {
                            if($orders && !empty($orders) && !empty($orders->orderBoutiqueAttributes) && $orders->orderBoutiqueAttributes)
                            {
                                foreach($orders->orderBoutiqueAttributes as $attributeKey => $attributeValue)
                                {
                                    $notifications[] = [
                                        'notification_text' => $notification_text,
                                        'read_by' => auth()->user()->id,
                                        'created_by' => auth()->user()->id,
                                        'company_id' => $attributeValue->boutique_id,
                                        'order_id' => $orders->id,
                                        'notification_page' => $page,
                                        'created_at' => date('Y-m-d H:i:s'),
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ];
                                }
                            }
                        }
                        else
                        {
                            $notifications[] = [
                                'notification_text' => $notification_text,
                                'read_by' => auth()->user()->id,
                                'created_by' => auth()->user()->id,
                                'company_id' => 0,
                                'order_id' => $orders->id,
                                'notification_page' => $page,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ];
                        }
                    }
                }
            }
        }

        // if($orders && !empty($orders) && !empty($orders->orderBoutiqueAttributes) && $orders->orderBoutiqueAttributes)
        // {
        //     foreach($orders->orderBoutiqueAttributes as $attributeKey => $attributeValue)
        //     {
        //         if($notifications && !empty($notifications) && count($notifications) > 0)
        //         {
        //             foreach ($notifications as $noteKey => $noteValue)
        //             {
        //                 $noteValue['company_id'] = $attributeValue->boutique_id;
        //                 $notifications[$attributeKey] = $noteValue;
        //             }
        //         }
        //     }
        // }

        Notifications::insert($notifications);
    }

    public function deleted(Orders $orders)
    {

    }
}
