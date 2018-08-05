<?php
/**
 * Created by Krutik Patel.
 * Date: 10-04-2018
 * Time: 10:26 AM IST
 */

namespace App\Observers;


use App\Company;
use App\Notifications;

class CompanyObservers
{
    public function created(Company $company) {

    }

    public function updated(Company $company)
    {
        $activities = array_diff($company->getAttributes(),$company->getOriginal());
        $notifications = [];

        $track_events = [
            'register_number',
            'register_date',
            'company_name',
            'register_company_name',
            'court_name',
            'legal_person',
            'general_manager',
            'address',
            'postal_code',
            'city',
            'state',
            'country',
            'company_email',
            'company_image',
            'website',
            'facebook',
            'twitter',
            'whatsapp',
            'instagram',
            'wechat',
            'pinterest',
            'contact_person_first_name',
            'contact_person_last_name',
            'contact_person_gender',
            'contact_person_position',
            'contact_person_telefon',
            'contact_person_fax',
            'contact_person_mo_no',
            'contact_person_email',
            'contact_person_image',
            'tax_company_name',
            'EUTIN',
            'NTIN',
            'LTA',
            'default_vat_rate',
            'custom_company_name',
            'custom_country',
            'country_code',
            'main_custom_office',
            'EORI',
        ];

        $fields_page_mapping = [
            'register_number' => ['company_register_edit_partial'],
            'register_date' => ['company_register_edit_partial'],
            'company_name' => ['superadmin_company_edit_partial', 'admin_company_edit_full', 'admin_company_edit_partial'],
            'register_company_name' => ['company_register_edit_partial'],
            'court_name' => ['company_register_edit_partial'],
            'legal_person' => ['company_register_edit_partial'],
            'general_manager' => ['company_register_edit_partial'],
            'address' => ['superadmin_company_edit_partial', 'admin_company_edit_full'],
            'postal_code' => ['superadmin_company_edit_partial', 'admin_company_edit_full'],
            'city' => ['superadmin_company_edit_partial', 'admin_company_edit_full'],
            'state' => ['superadmin_company_edit_partial', 'admin_company_edit_full'],
            'country' => ['superadmin_company_edit_partial', 'admin_company_edit_full'],
            'company_email' => ['superadmin_company_edit_partial', 'admin_company_edit_full'],
            'company_image' => ['superadmin_company_edit_partial', 'admin_company_edit_full'],
            'website' => ['admin_company_edit_full'],
            'facebook' => ['admin_company_edit_full'],
            'twitter' => ['admin_company_edit_full'],
            'whatsapp' => ['admin_company_edit_full'],
            'instagram' => ['admin_company_edit_full'],
            'wechat' => ['admin_company_edit_full'],
            'pinterest' => ['admin_company_edit_full'],
            'contact_person_first_name' => ['admin_company_edit_full'],
            'contact_person_last_name' => ['admin_company_edit_full'],
            'contact_person_gender' => ['admin_company_edit_full'],
            'contact_person_position' => ['admin_company_edit_full'],
            'contact_person_telefon' => ['admin_company_edit_full'],
            'contact_person_fax' => ['admin_company_edit_full'],
            'contact_person_mo_no' => ['admin_company_edit_full'],
            'contact_person_email' => ['admin_company_edit_full'],
            'contact_person_image' => ['admin_company_edit_full'],

            'tax_company_name' => ['company_tax_edit_partial'],
            'EUTIN' => ['company_tax_edit_partial'],
            'NTIN' => ['company_tax_edit_partial'],
            'LTA' => ['company_tax_edit_partial'],

            'default_vat_rate' => ['company_tax_edit_partial'],

            'custom_company_name' => ['company_customs_edit_partial'],
            'custom_country' => ['company_customs_edit_partial'],
            'country_code' => ['company_customs_edit_partial'],
            'main_custom_office' => ['company_customs_edit_partial'],
            'EORI' => ['company_customs_edit_partial'],
        ];

        foreach ($activities as $key => $activity)
        {
            if(in_array($key,$track_events))
            {
                $old = $company->getOriginal($key);
                $new = $company->getAttribute($key);

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
                            'company_id' => $company->id,
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

    public function deleted(Company $company) {

    }
}
