<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class StoreTime extends Model
{
    use Notifiable;

use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'store_time';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['store_id', 'mon_timing',  'tue_timing', 'wed_timing', 'thu_timing', 'fri_timing', 'sat_timing', 'sun_timing'];

    /**
     * The attributes that are dates
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
    /**
     * Insert and Update Store
     */
    public function insertUpdate($data)
    {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0) 
        {
            $getData = StoreTime::find($data['id']);
            $getData->update($data);
            return StoreTime::find($data['id']);
        } else {
            return StoreTime::create($data);
        }
    }
}
