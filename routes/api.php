<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization, mimetype, Platform');

Route::post('auth/token', 'Api\AuthController@getToken');
Route::post('saveAudio', 'Api\ProductController@saveAudio');

Route::group(['middleware' => 'jwt.auth'], function ()
{
    Route::post('logout', 'Api\AuthController@logout');

    //  Super Company Controller Routes
    Route::post('superCompanyRegister', 'Api\SuperCompanyController@superCompanyRegister');
    Route::post('superCompanyDelete', 'Api\SuperCompanyController@superCompanyDelete');

    Route::post('superCompanyList', 'Api\SuperCompanyController@getSuperCompanyList');
    Route::post('getSuperCompanyDetail', 'Api\SuperCompanyController@getSuperCompanyDetailById');
    
    // Super get super sub admin users
    Route::post('getSuperSubAdminUsers', 'Api\SuperCompanyController@getSuperSubAdminUsers');
    Route::post('saveSuperSubAdminUser', 'Api\SuperCompanyController@saveSuperSubAdminUser');

    //Boutique Company Controller Routes
    Route::post('companyUserList', 'Api\BoutiqueCompanyController@getCompanyUserList');
    Route::post('getCompanyUserDetail', 'Api\BoutiqueCompanyController@getCompanyUserDetailById');
    Route::post('getUserPermission', 'Api\BoutiqueCompanyController@getUserDetailById');
    Route::post('getCompanyProfileDetail', 'Api\BoutiqueCompanyController@getCompanyProfileDetail');
    Route::post('saveCompanyProfileDetail', 'Api\BoutiqueCompanyController@saveCompanyProfileDetail');
//  Route::post('getCompanyDetail', 'Api\BoutiqueCompanyController@getCompanyDetail');

    Route::post('saveCompanyUserDetail', 'Api\BoutiqueCompanyController@saveCompanyUserDetail');
    Route::post('deleteCompanyUserDetail', 'Api\BoutiqueCompanyController@deleteCompanyUserDetail');

    //store routes
    Route::post('delete-store', 'Api\StoreController@deleteStore');
    Route::post('getStoreDetails', 'Api\StoreController@getStoreDetails');
    Route::post('saveStoreDetails', 'Api\StoreController@saveStoreDetails');
    Route::post('getStoreListing', 'Api\StoreController@getStoreListing');

    //  bank routes
    Route::post('getCompanyBankDetails', 'Api\BankController@getBankList');
    Route::post('saveCompanyBankDetails', 'Api\BankController@saveCompanyBankDetails');
    Route::post('deleteCompanyBankDetails', 'Api\BankController@deleteCompanyBankDetails');

    //  Company Details Routes
    Route::post('getCompanyRegisterDetails', 'Api\BoutiqueCompanyController@getCompanyRegisterDetails');
    Route::post('getCompanyTaxDetails', 'Api\BoutiqueCompanyController@getCompanyTaxDetails');
    Route::post('saveCompanyRegisterDetails', 'Api\BoutiqueCompanyController@saveCompanyRegisterDetails');
    Route::post('saveCompanyTaxDetails', 'Api\BoutiqueCompanyController@saveCompanyTaxDetails');
    Route::post('getCompanyCustomsDetails', 'Api\BoutiqueCompanyController@getCompanyCustomsDetails');
    Route::post('saveCompanyCustomsDetails', 'Api\BoutiqueCompanyController@saveCompanyCustomsDetails');
    Route::post('getCompanyAllPermission', 'Api\BoutiqueCompanyController@getCompanyAllPermission');
    Route::post('getCompanyAllPermissionWithRoles', 'Api\BoutiqueCompanyController@getCompanyAllPermissionWithRoles');
    Route::post('setReadNotification', 'Api\BoutiqueCompanyController@setReadNotification');
    Route::post('getReadNotification', 'Api\BoutiqueCompanyController@getReadNotification');
    Route::post('getUnReadNotification', 'Api\BoutiqueCompanyController@getUnReadNotification');

    //  Type Wise Logo and Document Update routes Common Controller
    Route::post('typeWiseLogoUpdate', 'Api\CommonController@typeWiseLogoUpdate');
    Route::post('typeWiseDocumentUpdate', 'Api\CommonController@typeWiseDocumentUpdate');
    Route::post('typeWiseProductImageUpdate', 'Api\CommonController@typeWiseProductImageUpdate');
    Route::post('typeWiseProductImageDelete', 'Api\CommonController@typeWiseProductImageDelete');

    //  Fashionni Phase II

    //  Brands
    Route::apiResource('brands', 'Api\BrandsController');
    Route::post('brands/deleteBrandImage', 'Api\BrandsController@deleteBrandImage');
    Route::get('brands/getBoutiqueBrands/{id}', 'Api\BrandsController@getBoutiqueBrands');
    Route::post('brands/deleteMultipleBrands', 'Api\BrandsController@deleteMultipleBrands');

    //  Colors
    Route::apiResource('colors', 'Api\ColorsController');
    Route::post('colors/deleteColorImage', 'Api\ColorsController@deleteColorImage');
    Route::post('colors/deleteMultipleColors', 'Api\ColorsController@deleteMultipleColors');

    //Product Routes
    Route::post('getProductsList', 'Api\ProductController@getProductsList');
    Route::post('getProductsDetail', 'Api\ProductController@getProductsDetail');
    Route::post('saveProductsDetails', 'Api\ProductController@saveProductsDetails');
    Route::post('deleteProduct', 'Api\ProductController@deleteProduct');
    Route::post('deleteProductCodeImage', 'Api\ProductController@deleteProductCodeImage');

    //  Materials
    Route::apiResource('materials', 'Api\MaterialsController');
    Route::post('materials/deleteMaterialImage', 'Api\MaterialsController@deleteMaterialImage');
    Route::post('materials/deleteMultipleMaterials', 'Api\MaterialsController@deleteMultipleMaterials');

    //  Categories
    Route::post('categories', 'Api\CategoriesController@categories');
    Route::post('saveCategory', 'Api\CategoriesController@saveCategory');
    Route::post('getMainCategories', 'Api\CategoriesController@getMainCategories');
    Route::post('getCategoryDetails', 'Api\CategoriesController@getCategoryDetails');
    Route::post('deleteCategory', 'Api\CategoriesController@deleteCategory');
    Route::post('deleteCategoryImage', 'Api\CategoriesController@deleteCategoryImage');
    Route::post('addCategoryImage', 'Api\CategoriesController@addCategoryImage');

    // Product Inventory
    Route::post('getProductInventory', 'Api\ProductController@getProductInventory');
    Route::post('saveProductInventory', 'Api\ProductController@saveProductInventory');
    Route::post('deleteProductInventory', 'Api\ProductController@deleteProductInventory');

    // User Chat
    Route::post('chatBoutiqueUsers', 'Api\ChatController@chatBoutiqueUsers');
    Route::post('chatSuperAdminUsers', 'Api\ChatController@chatSuperAdminUsers');
    Route::post('addChatUser', 'Api\ChatController@addChatUser');
    Route::post('searchChatUsers', 'Api\ChatController@searchChatUsers');

    // Order Module
    Route::post('getOrderBoutiqueListing', 'Api\OrdersController@getOrderBoutiqueListing');
    Route::post('getOrderList', 'Api\OrdersController@getOrderList');
    Route::post('getOrderPackageBoxes', 'Api\OrdersController@getOrderPackageBoxes');
    Route::post('getOrderDetails', 'Api\OrdersController@getOrderDetails');
    Route::post('saveOrderDetails', 'Api\OrdersController@saveOrderDetails');
    Route::post('saveBoutiqueDocuments', 'Api\OrdersController@saveBoutiqueDocuments');
    Route::post('deleteBoutiqueDocument', 'Api\OrdersController@deleteBoutiqueDocument');
    Route::post('changeOrderStatus', 'Api\OrdersController@changeOrderStatus');

    Route::post('saveOrder', 'Api\OrdersController@saveOrder');

});
