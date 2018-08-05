<?php
/**
 * Created by Apurv Prajapati.
 * Date: 10-04-2018
 * Time: 02:45 PM IST
 */

namespace App\Observers;

use DB;
use Auth;
use Helpers;
use Storage;
use Config;
use App\Company;
use App\User;
use App\CompanyUser;
use App\Notifications;
use JWTAuth;
use JWTAuthException;

class UserObservers
{
    public function created(User $user)
    {
    }

    public function updated(User $user)
    {
        $activities = array_diff($user->getAttributes(), $user->getOriginal());
        $notifications = [];

        $track_events = [
            'user_name',
            'user_image',
            'name',
            'email',
            'password',
            'position',
            'photo',
            'random_number',
            'custom_role_name',
            'user_unique_id',
        ];

        $fields_page_mapping = [
            'user_name' => ['company_user_edit'],
            'user_image' => ['company_user_edit'],
            'name' => ['company_user_edit'],
            'email' => ['company_user_edit'],
            'password' => ['company_user_edit'],
            'position' => ['company_user_edit'],
            'photo' => ['company_user_edit'],
            'random_number' => ['company_user_edit'],
            'custom_role_name' => ['company_user_edit'],
            'user_unique_id' => ['company_user_edit']
        ];

        foreach ($activities as $key => $activity)
        {
            if(in_array($key, $track_events))
            {
                $old = $user->getOriginal($key);
                $new = $user->getAttribute($key);

                $field = ucwords(str_replace("_"," ",$key));

//              $notification_text = auth()->user()->name." Updated {$field} from {$old} to {$new}";
                if($field == 'Password')
                {
                    $notification_text = auth()->user()->name." has updated ".$field;
                }
                else
                {
                    $notification_text = auth()->user()->name.' has updated '.$field.' from "'.(!empty($old) ? $old : '-').'" to "'.$new.'"';
                }

                $pages = (isset($fields_page_mapping[$key]))?$fields_page_mapping[$key]:array();

                if(!empty($pages))
                {
                    foreach ($pages as $page)
                    {
                        $notifications[] = [
                            'notification_text' => $notification_text,
                            'read_by' => Auth::id(),
                            'created_by' => Auth::id(),
                            'company_id' => (isset($user->userCompany->company_id) && !empty($user->userCompany->company_id)) ? $user->userCompany->company_id : 0,
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

    public function deleted(User $user)
    {
        $pages = ['company_user_edit'];
        if(!empty($pages))
        {
            $notifications = [];
            foreach($pages as $page)
            {
                $notifications[] = [
                    'notification_text' => $user->name.' Company Document Deleted By '.auth()->user()->name,
                    'read_by' => Auth::id(),
                    'created_by' => Auth::id(),
                    'company_id' => (isset($user->userCompany->company_id) && !empty($user->userCompany->company_id)) ? $user->userCompany->company_id : 0,
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
