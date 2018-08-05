<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use App\User;
use App\UserRoles;
use App\CompanyUser;
use App\Company;
use App\Store;
use App\CompanyBankDetail;
use App\CompanyDocuments;
use App\CompanyTaxDocuments;
use App\CompanyCustomDocuments;
use App\Products;

use App\ProductImages;
use App\ProductColors;
use App\ProductMaterials;
use App\ProductInventory;

use App\Categories;
use App\CategoryImages;

use App\Colors;
use App\Brands;
use App\Materials;

use App\Orders;
use App\OrderBoutiqueAttributes;
use App\OrderBoutiqueItems;
use App\OrderBoutiqueDocuments;

use App\Observers\UserObservers;
use App\Observers\CompanyObservers;
use App\Observers\StoreObservers;

use App\Observers\UserRolesObservers;
use App\Observers\CompanyUserObservers;
use App\Observers\CompanyBankObservers;
use App\Observers\CompanyDocumentsObservers;
use App\Observers\CompanyTaxDocumentsObservers;
use App\Observers\CompanyCustomsDocumentsObservers;

use App\Observers\ProductsObservers;
use App\Observers\ProductImagesObservers;
use App\Observers\ProductColorsObservers;
use App\Observers\ProductMaterialsObservers;
use App\Observers\ProductInventoryObservers;

use App\Observers\CategoriesObservers;
use App\Observers\CategoryImagesObservers;

use App\Observers\ColorsObservers;
use App\Observers\BrandsObservers;
use App\Observers\MaterialsObservers;

use App\Observers\OrdersObservers;
use App\Observers\OrderBoutiqueAttributesObservers;
use App\Observers\OrderBoutiqueItemsObservers;
use App\Observers\OrderBoutiqueDocumentsObservers;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(UserObservers::class);
        Company::observe(CompanyObservers::class);
        Store::observe(StoreObservers::class);
        CompanyUser::observe(CompanyUserObservers::class);
        UserRoles::observe(UserRolesObservers::class);

        CompanyBankDetail::observe(CompanyBankObservers::class);
        CompanyDocuments::observe(CompanyDocumentsObservers::class);
        CompanyTaxDocuments::observe(CompanyTaxDocumentsObservers::class);
        CompanyCustomDocuments::observe(CompanyCustomsDocumentsObservers::class);

        Products::observe(ProductsObservers::class);
        ProductImages::observe(ProductImagesObservers::class);
        ProductColors::observe(ProductColorsObservers::class);
        ProductMaterials::observe(ProductMaterialsObservers::class);
        ProductInventory::observe(ProductInventoryObservers::class);

        Categories::observe(CategoriesObservers::class);
        CategoryImages::observe(CategoryImagesObservers::class);

        Colors::observe(ColorsObservers::class);
        Brands::observe(BrandsObservers::class);
        Materials::observe(MaterialsObservers::class);

        Colors::observe(ColorsObservers::class);
        Brands::observe(BrandsObservers::class);
        Materials::observe(MaterialsObservers::class);
        Materials::observe(MaterialsObservers::class);

        Orders::observe(OrdersObservers::class);
        OrderBoutiqueAttributes::observe(OrderBoutiqueAttributesObservers::class);
        OrderBoutiqueItems::observe(OrderBoutiqueItemsObservers::class);
        OrderBoutiqueDocuments::observe(OrderBoutiqueDocumentsObservers::class);

        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
