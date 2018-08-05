<?php
namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;
use Config;

class Company extends Model
{
    use Notifiable;

    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'company';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'register_number',
        'register_date',
        'company_name',
        'register_company_name',
        'company_slug',
        'court_name',
        'legal_person',
        'general_manager',
        'company_image',
        'address',
        'postal_code',
        'city',
        'state',
        'country',
        'company_email',
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
        'main_custom_office',
        'EORI',
        'country_code',
        'custom_company_name',
        'custom_country',
        'status',
        'company_unique_id',
        'randon_number'

    ];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];


    /**
     * Insert and Update Company
     */
    public function insertUpdate($data)
    {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0)
        {
            $company = Company::find($data['id']);
            $company->update($data);
            return Company::find($data['id']);
        } else {
            return Company::create($data);
        }
    }

    public function companyDocuments()
    {
        return $this->hasMany('App\CompanyDocuments', 'company_id');
    }

    public function companyTaxDocuments()
    {
        return $this->hasMany('App\CompanyTaxDocuments', 'company_id');
    }

    public function companyCustomDocuments()
    {
        return $this->hasMany('App\CompanyCustomDocuments', 'company_id');
    }

    public function getCompanyBySlug($slug)
    {
        return Company::where('company_slug',$slug)->first();
    }

    public function getCompanyDetail($companyId)
    {
        return Company::with('store')->where('id',$companyId)->first();
    }

    public function getCompanyAllDetail($filters = array(), $paginate = false)
    {
        $getData = Company::whereNull('deleted_at');

        if(isset($filters) && !empty($filters))
        {
            if(isset($filters['country']) && !empty($filters['country']))
            {
                $getData->where('country', $filters['country']);
            }

            if(isset($filters['boutique_id']) && !empty($filters['boutique_id']))
            {
                $getData->where('company_unique_id', $filters['boutique_id']);
            }

            if(isset($filters['boutique_alphabet']) && !empty($filters['boutique_alphabet']))
            {
                $getData->where('company_name', 'like', $filters['boutique_alphabet'].'%');
            }

            if(isset($filters['search_key']) && !empty($filters['search_key']))
            {
                $getData->where('company_name', 'like', '%'.$filters['search_key'].'%');
            }
        }
        if(isset($paginate) && $paginate == true)
        {
            return $getData->paginate(Config::get('constant.ADMIN_RECORD_PER_PAGE'));
        }
        else
        {
            return $getData->get(['id', 'company_unique_id', 'company_name', 'company_image', 'country']);
        }
        // return Company::get(['id', 'company_unique_id', 'company_name', 'company_image']);
    }

    public function store()
    {
        return $this->hasMany('App\Store', 'company_id');
    }

    public function orderAttributes()
    {
        return $this->hasManyThrough('App\Orders', 'App\OrderBoutiqueAttributes', 'boutique_id', 'id', 'id', 'order_id');
    }

    public function permission()
    {
        return $this->hasMany('App\Permissions', 'company_id');
    }

    public function getCompanyProfileDetails($companyId)
    {
        return Company::where('id', $companyId)->first();
    }

    public function getProducts()
    {
        return $this->hasMany('App\Products', 'company_id');
    }

    public function brands()
    {
        return $this->belongsToMany(Brands::class, 'products', 'company_id', 'brand_id');
    }

    public function companyChatUsers() {
        return $this->belongsToMany(User::class, 'users_chat', 'company_id', 'user_id');
    }
}
