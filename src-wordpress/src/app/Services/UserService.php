<?php

namespace App\Services;

class UserService
{
  public static function getCashier(int $id)
  {
    $user = get_user_by('id', $id);
    if ($user) {
      return sprintf('%s (%d)', $user->display_name, $user->user_login);
    }
    return '';
  }
}
