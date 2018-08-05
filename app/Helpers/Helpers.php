<?php

namespace App\Helpers;
use DB;
use Redirect;
use Carbon\Carbon;
use Crypt;
use Mail;
use Session;
use Config;
use Storage;
use Image;
use File;
use App\UserRoles;
use App\Permissions;
use App\UserPermission;

Class Helpers 
{
    public static function createSlug($str, $delimiter = '-')
    {
        $slug = strtolower(trim(preg_replace('/[\s-]+/', $delimiter, preg_replace('/[^A-Za-z0-9-]+/', $delimiter, preg_replace('/[&]/', 'and', preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), $delimiter));
        return $slug;

    }

    public static function addFileToStorage($fileName, $folderName = "", $file)
    {
        $url = "";
        if($file && $fileName != "")
        {
            $folderName = ($folderName != "") ? $folderName : "/";

            if(Storage::put($folderName.$fileName, file_get_contents($file), 'public'))
            {
                //$url = url(Storage::url($folderName.$fileName));
                $url = $fileName;
            }
        }
        return $url;
    }

    public static function deleteFileToStorage($fileName, $folderName = "")
    {
        $return = false;
        if ($fileName != "") {
            $folderName = ($folderName != "") ? $folderName : "/";
            if (Storage::exists($folderName . $fileName)) {
                if (Storage::delete($folderName . $fileName)) {
                    $return = true;
                }
            }
        }
        return $return;
    }

    public static function createUpdateImage( $file, $originalImageUploadPath, $thumbImageUploadPath, $thumbImageHeight, $data, $oldFileName )
    {
        if (!file_exists(public_path($originalImageUploadPath)))
        {
            File::makeDirectory(public_path($originalImageUploadPath), 0777, true, true);
        }
        if (!file_exists(public_path($thumbImageUploadPath)))
        {
            File::makeDirectory(public_path($thumbImageUploadPath), 0777, true, true);
        }

        $fileName = str_random(20) . '.' . $file->getClientOriginalExtension();

        $pathOriginal = public_path($originalImageUploadPath . $fileName);

        $pathThumb = public_path($thumbImageUploadPath . $fileName);

        // created instance
        $img = Image::make($file->getRealPath());

        // resize the image to a height
        if ($img->height() < $thumbImageHeight) {
            $thumbImageHeight = $img->height();
        }

        $img->save($pathOriginal);
        $img->resize(null, $thumbImageHeight, function ($constraint) {
            $constraint->aspectRatio();
        })->save($pathThumb);

        if (isset($data['id']) && $data['id'] > 0)
        {
            Helpers::deleteFileToStorage($oldFileName, $originalImageUploadPath);
            Helpers::deleteFileToStorage($oldFileName, $thumbImageUploadPath);
        }

        Helpers::addFileToStorage($fileName, $originalImageUploadPath, $pathOriginal);
        Helpers::addFileToStorage($fileName, $thumbImageUploadPath, $pathThumb);

        File::delete($originalImageUploadPath . $fileName);
        File::delete($thumbImageUploadPath . $fileName);

        return $fileName;
    }

    public static function createDocuments($file, $originalDocUploadPath, $prefix, $data)
    {
        $docsArray = [];
        if (!file_exists(public_path($originalDocUploadPath)))
        {
            File::makeDirectory(public_path($originalDocUploadPath), 0777, true, true);
        }
        $fileDocOriginalName = $file->getClientOriginalName();
        $fileDocName = $prefix.str_random(10). '.' . $file->getClientOriginalExtension();

        $pathOriginal = public_path($originalDocUploadPath . $fileDocName);

        //created instance
        $docMoveData = $file->move($originalDocUploadPath, $fileDocName);

        Helpers::addFileToStorage($fileDocName, $originalDocUploadPath, $pathOriginal);

        File::delete($originalDocUploadPath.$fileDocName);

        $docsArray['doc_name'] = $fileDocName;
        $docsArray['doc_original_name'] = $fileDocOriginalName;

        return $docsArray;
    }

    public static function checkUserAuthorization($userId, $slug, $checkType)
    {
        $objUserRoles = new UserRoles();
        $objPermissions = new Permissions();
        $objUserPermission = new UserPermission();

        $unauthorizedAccess = [];
        $authorization = "1"; // success

        $userRoles = $objUserRoles->getUserRolesByUserId($userId);

        foreach ($userRoles as $key => $value)
        {
            if( $value->roles->slug == Config::get('constant.SUPER_ADMIN_SLUG'))
            {
                break;
            }
            elseif($value->roles->slug != Config::get('constant.SUPER_ADMIN_SLUG'))
            {
                $unauthorizedAccess[] = $value->roles->slug;
            }
        }

        if(count($unauthorizedAccess)>0)
        {
            $permissionsData = $objPermissions->getPermissionBySlug($slug);

            if($checkType == "view"){
                $userPermissionData = $objUserPermission->getUserPermissionForViewByPermissionIdAndUserId($permissionsData->id,$userId);
            }
            elseif($checkType == "edit"){
                $userPermissionData = $objUserPermission->getUserPermissionForEditByPermissionIdAndUserId($permissionsData->id,$userId);
            }

            if(count($userPermissionData)<1)
            {
                $authorization = "0"; // Failed
            }
        }

        return $authorization;
    }

    public static function generateRandomNoString($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString . time();
    }

    public static function getUserRole($user_id)
    {
        return UserRoles::where('user_id', $user_id)->first();
    }

    public static function getMultipleLanguageName($data = NULL, $type = NULL)
    {
        $getName = '';
        if(!empty($type) && $type == 'color')
        {
            if($data && !empty($data))
            {
                if(!empty($data->color_name_en))
                {
                    $getName = $data->color_name_en;
                }
                elseif(!empty($data->color_name_ch))
                {
                    $getName = $data->color_name_ch;
                }
                elseif(!empty($data->color_name_ge))
                {
                    $getName = $data->color_name_ge;
                }
                elseif(!empty($data->color_name_fr))
                {
                    $getName = $data->color_name_fr;
                }
                elseif(!empty($data->color_name_it))
                {
                    $getName = $data->color_name_it;
                }
                elseif(!empty($data->color_name_sp))
                {
                    $getName = $data->color_name_sp;
                }
                elseif(!empty($data->color_name_ru))
                {
                    $getName = $data->color_name_sp;
                }
                elseif(!empty($data->color_name_jp))
                {
                    $getName = $data->color_name_jp;
                }
                else
                {
                    $getName = '';
                }
            }
        }
        elseif(!empty($type) && $type == 'material')
        {
            if($data && !empty($data))
            {
                if(!empty($data->material_name_en))
                {
                    $getName = $data->material_name_en;
                }
                elseif(!empty($data->material_name_ch))
                {
                    $getName = $data->material_name_ch;
                }
                elseif(!empty($data->material_name_ge))
                {
                    $getName = $data->material_name_ge;
                }
                elseif(!empty($data->material_name_fr))
                {
                    $getName = $data->material_name_fr;
                }
                elseif(!empty($data->material_name_it))
                {
                    $getName = $data->material_name_it;
                }
                elseif(!empty($data->material_name_sp))
                {
                    $getName = $data->material_name_sp;
                }
                elseif(!empty($data->material_name_ru))
                {
                    $getName = $data->material_name_sp;
                }
                elseif(!empty($data->material_name_jp))
                {
                    $getName = $data->material_name_jp;
                }
                else
                {
                    $getName = '';
                }
            }
        }
        elseif(!empty($type) && $type == 'product')
        {
            if($data && !empty($data))
            {
                if(!empty($data->product_name_en))
                {
                    $getName = $data->product_name_en;
                }
                elseif(!empty($data->product_name_ch))
                {
                    $getName = $data->product_name_ch;
                }
                elseif(!empty($data->product_name_ge))
                {
                    $getName = $data->product_name_ge;
                }
                elseif(!empty($data->product_name_fr))
                {
                    $getName = $data->product_name_fr;
                }
                elseif(!empty($data->product_name_it))
                {
                    $getName = $data->product_name_it;
                }
                elseif(!empty($data->product_name_sp))
                {
                    $getName = $data->product_name_sp;
                }
                elseif(!empty($data->product_name_ru))
                {
                    $getName = $data->product_name_sp;
                }
                elseif(!empty($data->product_name_jp))
                {
                    $getName = $data->product_name_jp;
                }
                else
                {
                    $getName = '';
                }
            }
        }
        elseif(!empty($type) && $type == 'category')
        {
            if($data && !empty($data))
            {
                if(!empty($data->category_name_en))
                {
                    $getName = $data->category_name_en;
                }
                elseif(!empty($data->category_name_ch))
                {
                    $getName = $data->category_name_ch;
                }
                elseif(!empty($data->category_name_ge))
                {
                    $getName = $data->category_name_ge;
                }
                elseif(!empty($data->category_name_fr))
                {
                    $getName = $data->category_name_fr;
                }
                elseif(!empty($data->category_name_it))
                {
                    $getName = $data->category_name_it;
                }
                elseif(!empty($data->category_name_sp))
                {
                    $getName = $data->category_name_sp;
                }
                elseif(!empty($data->category_name_ru))
                {
                    $getName = $data->category_name_sp;
                }
                elseif(!empty($data->category_name_jp))
                {
                    $getName = $data->category_name_jp;
                }
                else
                {
                    $getName = '';
                }
            }
        }
        return $getName;
    }

    public static function getDefaultPermission() 
    {
        $permissionArray = '';
        $permissionId = array();
        $getDefaultPermission = Permissions::where('is_default', 1)->get();
        if($getDefaultPermission)
        {
            foreach ($getDefaultPermission as $key => $value)
            {
                $permissionId[] = $value->id;
            }
            $permissionArray = implode(",", $permissionId);
        }
        return $permissionArray;
    }
    
    public static function getDate($dateSlug)
    {
        $getDate = '';
        if(!empty($dateSlug))
        {
            if($dateSlug == Config::get('constant.LAST_WEEK'))
            {               
               $getDate = Carbon::now()->subWeek()->toDateString();
            }
            elseif($dateSlug == Config::get('constant.LAST_15_DAYS'))
            {
                $getDate = Carbon::now()->subDays(15)->toDateString();
            }
            elseif($dateSlug == Config::get('constant.LAST_30_DAYS'))
            {
                $getDate = Carbon::now()->subMonth()->toDateString();
            }
            elseif($dateSlug == Config::get('constant.LAST_3_MONTHS'))
            {
                $getDate = Carbon::now()->subMonths(3)->toDateString();
            }
            elseif($dateSlug == Config::get('constant.LAST_6_MONTHS'))
            {
                $getDate = Carbon::now()->subMonths(6)->toDateString();
            }
           else
           {
               $getDate = '';
           }
        }
        else
        {
            $getDate = '';
        }
        return $getDate;
    }
    
}
