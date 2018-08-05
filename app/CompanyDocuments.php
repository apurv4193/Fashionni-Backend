<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CompanyDocuments extends Model
{
    use Notifiable;
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'company_documents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'company_doc_name', 'company_doc_file_name', 'random_number', 'deleted_at'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
    /**
     * Insert and Update CompanyDocuments
     */
    public function insertUpdate($data)
    {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0) 
        {
            $getData = CompanyDocuments::find($data['id']);
            $getData->update($data);
            return CompanyDocuments::find($data['id']);            
        } else {
            return CompanyDocuments::create($data);
        }
    }
    
    public function getCompanyDocumentByCompanyIdAndFileName($companyId, $fileName) {
        return CompanyDocuments::where('company_id', $companyId)->where('company_doc_file_name', $fileName)->first();
    }

    public function getDocumentCountByCompanyId($companyId) {
        return CompanyDocuments::where('company_id', $companyId)->count();
    }

}
