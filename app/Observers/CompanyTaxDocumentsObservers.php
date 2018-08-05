<?php
/**
 * Created by Apurv Prajapati.
 * Date: 10-04-2018
 * Time: 5:26 PM IST
 */

namespace App\Observers;

use DB;
use Auth;
use Helpers;
use Storage;
use Config;
use App\Company;
use App\CompanyTaxDocuments;
use App\Notifications;
use JWTAuth;
use JWTAuthException;

class CompanyTaxDocumentsObservers
{
    public function __construct()
    {
        
    }    
    
    public function created(CompanyTaxDocuments $taxDocuments) 
    {
        $pages = ['company_tax_edit_partial'];
        if(!empty($pages)) {
            $notifications = [];
            foreach($pages as $page) {
                $notifications[] = [
//                  'notification_text' => $taxDocuments->company_tax_doc_name.' Tax Document Created By '.auth()->user()->name,
                    'notification_text' => auth()->user()->name.' has created a tax document '.$taxDocuments->company_tax_doc_name,
                    'read_by' => Auth::id(),
                    'created_by' => Auth::id(),
                    'company_id' => $taxDocuments->company_id,
                    'store_id' => '0',
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }

            Notifications::insert($notifications);
        }
    }

    public function updated(CompanyTaxDocuments $taxDocuments)
    {
        $activities = array_diff($taxDocuments->getAttributes(), $taxDocuments->getOriginal());
        $notifications = [];

        $track_events = [
            'company_tax_doc_name', 
            'company_doc_file_name', 
            'random_number'
        ];
        
        $fields_page_mapping = [
            'company_tax_doc_name' => ['company_tax_edit_partial'],
            'company_doc_file_name' => ['company_tax_edit_partial'],
            'random_number' => ['company_tax_edit_partial']
        ];

        foreach ($activities as $key => $activity) 
        {
            if(in_array($key, $track_events)) 
            {
                $old = $taxDocuments->getOriginal($key);
                $new = $taxDocuments->getAttribute($key);

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
                            'read_by' => Auth::id(),
                            'created_by' => Auth::id(),
                            'company_id' => $taxDocuments->company_id,
                            'store_id' => '0',
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

    public function deleted(CompanyTaxDocuments $taxDocuments) 
    {
        $pages = ['company_tax_edit_partial'];
        if(!empty($pages)) 
        {
            $notifications = [];
            foreach($pages as $page) {
                $notifications[] = [
//                  'notification_text' => $taxDocuments->company_tax_doc_name.' Tax Document Deleted By '.auth()->user()->name,
                    'notification_text' => auth()->user()->name.' has deleted tax document of '.$taxDocuments->company_tax_doc_name,
                    'read_by' => Auth::id(),
                    'created_by' => Auth::id(),
                    'company_id' => $taxDocuments->company_id,
                    'store_id' => '0',
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            Notifications::insert($notifications);
        }
    }
    
}