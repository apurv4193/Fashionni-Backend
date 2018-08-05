<?php
/**
 * Created by Apurv Prajapati.
 * Date: 12-04-2018
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
use App\CompanyUser;
use App\Notifications;
use JWTAuth;
use JWTAuthException;

class CompanyUserObservers
{
    public function created(CompanyUser $user)
    {
       $pages = ['company_user_edit'];
        if(!empty($pages))
        {
            $notifications = [];
            foreach($pages as $page)
            {
                $notifications[] = [
//                  'notification_text' => $user->getUser->name.' User Created By '.auth()->user()->name,
                    'notification_text' => auth()->user()->name.' has created a new user '.$user->getUser->name,

                    'read_by' => Auth::id(),
                    'created_by' => Auth::id(),
                    'company_id' => (isset($user->company_id) && !empty($user->company_id)) ? $user->company_id : 0,
                    'store_id' => '0',
                    'notification_page' => $page,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            Notifications::insert($notifications);
        }
    }

    public function updated(CompanyUser $user)
    {

    }

    public function deleted(CompanyUser $user)
    {

    }

}
