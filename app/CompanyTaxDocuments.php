<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CompanyTaxDocuments extends Model
{
    use Notifiable;
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'company_tax_documents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'company_tax_doc_name', 'company_doc_file_name', 'random_number','deleted_at'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Insert and Update CompanyTaxDocuments
     */
    public function insertUpdate($data)
    {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0) 
        {
            $getData = CompanyTaxDocuments::find($data['id']);
            $getData->update($data);
            return CompanyTaxDocuments::find($data['id']);
        } else {
            return CompanyTaxDocuments::create($data);
        }
    }
    
    public function getCompanyTaxDocumentByCompanyIdAndFileName($companyId, $fileName) {
        return CompanyTaxDocuments::where('company_id', $companyId)->where('company_doc_file_name', $fileName)->first();
    }

    public function getDocumentCountByCompanyId($companyId) {
        return CompanyTaxDocuments::where('company_id', $companyId)->count();
    }

}
