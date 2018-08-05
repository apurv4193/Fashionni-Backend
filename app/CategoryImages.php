<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryImages extends Model
{
  use Notifiable;

  use SoftDeletes;

   /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'category_images';

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
      $getData = CategoryImages::find($data['id']);
      $getData->update($data);
      return CategoryImages::find($data['id']);
    } else {
      return CategoryImages::create($data);
    }
  }

  public function categroy()
  {
    return $this->belongsTo(Categories::class, 'category_id');
  }

}
