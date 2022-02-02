<?php

namespace App\Models;

use ArrayAccess;

class OrderItemImage extends Model
{
  public int $id;

  public int $mediaId;

  public string $mediaUrl;

  public string $createdAt;
}
