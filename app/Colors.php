<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Colors extends Model
{
  use Notifiable;

  use SoftDeletes;

   /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'colors';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = ['color_name_en', 'color_name_ch', 'color_name_ge', 'color_name_fr', 'color_name_it', 'color_name_sp', 'color_name_ru', 'color_name_jp', 'color_unique_id', 'color_image'];

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
      $getData = Colors::find($data['id']);
      $getData->update($data);
      return Colors::find($data['id']);
    } else {
      return Colors::create($data);
    }
  }

}
