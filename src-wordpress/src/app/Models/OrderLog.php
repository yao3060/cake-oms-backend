<?php

namespace App\Models;

use WeDevs\ORM\Eloquent\Model;

class OrderLog extends Model
{
  /**
   * Name for table without prefix
   *
   * @var string
   */
  protected $table = 'order_logs';

  /**
   * Columns that can be edited - IE not primary key or timestamps if being used
   */
  protected $fillable = [
    'order_id',
    'user_id',
    'username',
    'user_roles',
    'ip',
    'event',
    'message',
    'created_at'
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'user_roles' => 'array',
  ];

  /**
   * Disable created_at and update_at columns, unless you have those.
   */
  public $timestamps = false;

  /**
   * Overide parent method to make sure prefixing is correct.
   *
   * @return string
   */
  public function getTable()
  {
    // In this example, it's set, but this is better in an abstract class
    if (isset($this->table)) {
      $prefix =  $this->getConnection()->db->prefix;

      return $prefix . $this->table;
    }

    return parent::getTable();
  }
}
