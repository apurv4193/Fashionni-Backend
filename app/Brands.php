<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brands extends Model
{
  use Notifiable;

  use SoftDeletes;

   /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'brands';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = ['brand_name', 'brand_slug', 'brand_image'];

  /**
   * The attributes that are dates
   *
   * @var array
   */
  protected $dates = ['deleted_at'];


  /**
   * Insert and Update Brands
   */
  public function insertUpdate($data)
  {
    if (isset($data['id']) && $data['id'] != '' && $data['id'] > 0)
    {
      $getData = Brands::find($data['id']);
      $getData->update($data);
      return Brands::find($data['id']);
    } else {
      return Brands::create($data);
    }
  }

}
