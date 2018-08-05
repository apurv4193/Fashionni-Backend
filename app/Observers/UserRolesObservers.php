<?php
/**
 * Created by Krutik Patel.
 * Date: 10-04-2018
 * Time: 10:26 AM IST
 */

namespace App\Observers;

use DB;
use Auth;
use Helpers;
use Storage;
use Config;
use App\Company;
use App\User;
use App\UserRoles;
use App\CompanyUser;
use App\Notifications;
use JWTAuth;
use JWTAuthException;

class UserRolesObservers
{
    public function created(UserRoles $userRole)
    {
        $getCompanyUserData = CompanyUser::where('user_id', $userRole->user_id)->first();
       
        $pages = ['company_user_edit'];
        if(!empty($pages)) 
        {
            $notifications = [];
            foreach($pages as $page) 
            {
                $notifications[] = [
                    'notification_text' => auth()->user()->name.' has assign role for user '.(isset($userRole->user->name) && !empty($userRole->user->name) ? $userRole->user->name : '-').' as '.(isset($userRole->roles->name) && !empty($userRole->roles->name) ? $userRole->roles->name : '-'),
                    'read_by' => Auth::id(),
                    'created_by' => Auth::id(),
                    'company_id' => (!empty($getCompanyUserData->company_id) && !empty($getCompanyUserData->company_id)) ? $getCompanyUserData->company_id : '0',
                    'store_id' => '0',
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            Notifications::insert($notifications);
        }
    }

    public function updated(UserRoles $userRole)
    {
        $activities = array_diff($userRole->getAttributes(), $userRole->getOriginal());
        $notifications = [];

        $track_events = [
            'user_id',
            'role_id'
        ];

        $fields_page_mapping = [
            'user_id' => ['company_user_edit'],
            'role_id' => ['company_user_edit']
        ];

        foreach ($activities as $key => $activity)
        {
            if(in_array($key, $track_events))
            {
                $old = $userRole->getOriginal($key);
                $new = $userRole->getAttribute($key);

                $field = ucwords(str_replace("_"," ",$key));

//              $notification_text = auth()->user()->name." Updated {$field} from {$old} to {$new}";
                $notification_text = auth()->user()->name.' has updated user role to '.(isset($userRole->user->name) && !empty($userRole->user->name) ? $userRole->user->name : '-').' as '.(isset($userRole->roles->name) && !empty($userRole->roles->name) ? $userRole->roles->name : '-');
                
                $pages = (isset($fields_page_mapping[$key]))?$fields_page_mapping[$key]:array();

                if(!empty($pages))
                {
                    foreach ($pages as $page)
                    {
                        $notifications[] = [
                            'notification_text' => $notification_text,
                            'read_by' => Auth::id(),
                            'created_by' => Auth::id(),
                            'company_id' => (isset($userRole->companyUser->company_id) && !empty($userRole->companyUser->company_id)) ? $userRole->companyUser->company_id : '0',
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
}
