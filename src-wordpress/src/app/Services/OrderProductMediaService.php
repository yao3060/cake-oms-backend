<?php

namespace App\Services;

class OrderProductMediaService
{

  private $db = null;
  private $dbPrefix = '';

  public function __construct()
  {
    $this->db = \WeDevs\ORM\Eloquent\Database::instance();
    $this->dbPrefix = $this->db->db->prefix;
  }

  public function addFeaturedImage(int $productId, int $mediaId, string $url): bool
  {
    return $this->db->table('order_items')
      ->where('id', $productId)
      ->update([
        'media_id' => $mediaId,
        'media_url' => $url
      ]);
  }

  public function addGalleryImage(int $productId, int $mediaId, string $url): bool
  {
    return $this->db->table('order_item_gallery')->insert([
      'item_id' => $productId,
      'media_id' => $mediaId,
      'media_url' => $url,
      'created_at' => date('Y-m-d H:i:s')
    ]);
  }
}
