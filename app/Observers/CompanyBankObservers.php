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
use App\CompanyBankDetail;
use App\Notifications;
use JWTAuth;
use JWTAuthException;

class CompanyBankObservers
{
    public function __construct()
    {
        
    }    
    
    public function created(CompanyBankDetail $bankDetail) 
    {
        $pages = ['bank_edit_partial'];
        if(!empty($pages)) 
        {
            $notifications = [];
            foreach($pages as $page) 
            {
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has created a new bank '.$bankDetail->bank_name,
                    'read_by' => Auth::id(),
                    'created_by' => Auth::id(),
                    'company_id' => $bankDetail->company_id,
                    'store_id' => '0',
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            Notifications::insert($notifications);
        }
    }

    public function updated(CompanyBankDetail $bankDetail) 
    {
        $activities = array_diff($bankDetail->getAttributes(), $bankDetail->getOriginal());
        $notifications = [];

        $track_events = [
            'company_name', 
            'company_address', 
            'bank_name', 
            'bank_address', 
            'IBAN_account_no', 
            'SWIFT_BIC', 
            'bank_image',
        ];
        
        $fields_page_mapping = [
            'company_name' => ['bank_edit_partial'],
            'company_address' => ['bank_edit_partial'],
            'bank_name' => ['bank_edit_partial'],
            'bank_address' => ['bank_edit_partial'],
            'IBAN_account_no' => ['bank_edit_partial'],
            'SWIFT_BIC' => ['bank_edit_partial'],
            'bank_image' => ['bank_edit_partial']
        ];

        foreach ($activities as $key => $activity) 
        {
            if(in_array($key, $track_events)) 
            {
                $old = $bankDetail->getOriginal($key);
                $new = $bankDetail->getAttribute($key);

                $field = ucwords(str_replace("_"," ",$key));                
//              $notification_text = auth()->user()->name." has updated {$field} from {$old} to {$new}";
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
                            'company_id' => $bankDetail->company_id,
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

    public function deleted(CompanyBankDetail $bankDetail) 
    {
        $pages = ['bank_edit_partial'];
        if(!empty($pages)) 
        {
            $notifications = [];
            foreach($pages as $page) 
            {
                $notifications[] = [
//                  'notification_text' => $bankDetail->bank_name.' Bank Deleted By'.auth()->user()->name,
                    'notification_text' => auth()->user()->name.' has deleted a bank of '.$bankDetail->bank_name,
                    'read_by' => Auth::id(),
                    'created_by' => Auth::id(),
                    'company_id' => $bankDetail->company_id,
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