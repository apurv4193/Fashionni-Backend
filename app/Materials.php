<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Materials extends Model
{
  use Notifiable;

  use SoftDeletes;

   /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'materials';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $guarded = [];
  /**
   * The attributes that are dates
   *
   * @var array
   */
  protected $dates = ['deleted_at'];


  /**
   * Insert and Update Colors
   */
  public function insertUpdate($data)
  {
    if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0)
    {
      $getData = Materials::find($data['id']);
      $getData->update($data);
      return Materials::find($data['id']);
    } else {
      return Materials::create($data);
    }
  }
}
