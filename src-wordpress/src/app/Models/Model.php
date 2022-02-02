<?php

namespace App\Models;

class Model
{
  /**
   * Private internal struct attributes
   * @var array
   */
  private $attributes = [];

  /**
   * Set a value
   * @param string $key
   * @param mixed $value
   */
  public function __set($key, $value)
  {
    $this->attributes[$key] = $value;
  }

  /**
   * Get a value
   * @param string $key
   * @return mixed
   */
  public function __get($key)
  {
    return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
  }

  /**
   * Check if a key is set
   * @param string $key
   * @return boolean
   */
  public function __isset($key)
  {
    return isset($this->attributes[$key]) ? true : false;
  }
}
