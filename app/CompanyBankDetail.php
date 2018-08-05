<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CompanyBankDetail extends Model
{
    use Notifiable;
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'company_bank_detail';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'company_name', 'company_address', 'bank_name', 'bank_address', 'IBAN_account_no', 'SWIFT_BIC','bank_image'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];


    /**
     * Insert and Update CompanyBankDetail
     */
    public function insertUpdate($data)
    {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0) 
        {
            $companyBankDetail = CompanyBankDetail::find($data['id']);
            $companyBankDetail->update($data);
            return CompanyBankDetail::find($data['id']);
        } else {
            return CompanyBankDetail::create($data);
        }
    }
}
