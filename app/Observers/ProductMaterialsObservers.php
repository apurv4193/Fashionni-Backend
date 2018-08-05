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
use App\ProductMaterials;
use App\Notifications;

class ProductMaterialsObservers
{
    public function created(ProductMaterials $productMaterials)
    {
        $pages = ['product_details'];
        if(!empty($pages)) 
        {
            $notifications = [];
            foreach($pages as $page) 
            {
                if(isset($productMaterials->material) && !empty($productMaterials->material))
                {
                    $getName = Helpers::getMultipleLanguageName($productMaterials->material, 'material');
                }
                else
                {
                    $getName = '';
                }
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has created a new product material '.(!empty($getName) ? $getName : '-'),
                    'read_by' => Auth::id(),
                    'created_by' => Auth::id(),
                    'company_id' => (isset($productMaterials->product) && !empty($productMaterials->product) && !empty($productMaterials->product->company_id)) ? $productMaterials->product->company_id : 0,
                    'store_id' => '0',
                    'product_id' => $productMaterials->product_id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            Notifications::insert($notifications);
        }
    }

    public function updated(ProductMaterials $productMaterials)
    {
        $activities = array_diff($productMaterials->getAttributes(), $productMaterials->getOriginal());
        $notifications = [];

        $track_events = [
            'product_id',
            'material_id',
        ];

        $fields_page_mapping = [
            'product_id' => ['product_details'],
            'material_id' => ['product_details'],
        ];

        foreach ($activities as $key => $activity)
        {
            if(in_array($key, $track_events))
            {
                $old = $productMaterials->getOriginal($key);
                $new = $productMaterials->getAttribute($key);

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
                            'company_id' => (isset($productMaterials->product) && !empty($productMaterials->product) && !empty($productMaterials->product->company_id)) ? $productMaterials->product->company_id : 0,
                            'store_id' => '0',
                            'product_id' => $productMaterials->product_id,
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

    public function deleted(ProductMaterials $productMaterials)
    {
        $pages = ['product_details'];
        if(!empty($pages))
        {
            $notifications = [];
            foreach($pages as $page)
            {
                if(isset($productMaterials->material) && !empty($productMaterials->material))
                {
                    $getName = Helpers::getMultipleLanguageName($productMaterials->material, 'material');
                }
                else
                {
                    $getName = '';
                }
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has deleted a product material of '.(!empty($getName) ? $getName : '-'),
                    'read_by' => Auth::id(),
                    'created_by' => Auth::id(),
                    'company_id' => (isset($productMaterials->product) && !empty($productMaterials->product) && !empty($productMaterials->product->company_id)) ? $productMaterials->product->company_id : 0,
                    'store_id' => '0',
                    'product_id' => $productMaterials->product_id,
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            Notifications::insert($notifications);
        }
    }
}
