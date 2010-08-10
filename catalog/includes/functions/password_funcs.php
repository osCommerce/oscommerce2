<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

////
// This funstion validates a plain text password with an
// encrpyted password
  function tep_validate_password($plain, $encrypted, $customers_id = '') {
    $validated = false;
    global $hasher;
    if (tep_not_null($plain) && tep_not_null($encrypted)) {
      $password_hash_style = tep_what_password($encrypted);
      switch ($password_hash_style) {
        case 'salted':
          $check_old = tep_validate_old_password($plain, $encrypted);
            if ($check_old == true) {
              $validated = true;
// insert password hash using PasswordHash into 
              $new_password_hash = tep_encrypt_password($plain);
              if (strlen($new_password_hash) > 19 && (int)$customers_id > 0) {
                tep_db_query("update " . TABLE_CUSTOMERS . " set customers_password = '" . $new_password_hash . "' where customers_id = '" . (int)$customers_id . "'");
              } else {
// error with PasswordHash
                unset($hasher);
              }
            }
           break;
        case 'phpass':
          if (!is_object($hasher)) {
            require_once(DIR_WS_CLASSES . 'PasswordHash.php');
// hard coded: number of base-2 logarithms of the iteration count used for password stretching (10)
// and the use of portable hashes
            $hasher = new PasswordHash(10, true);
          }
          $validated = $hasher->CheckPassword($plain, $encrypted);
          break;
        case 'unknown':
          $validated = false;
          break;
        default:
          $validated = false;
          break;
       }    
    }
    return $validated;
  }
  
  function tep_validate_old_password($plain, $encrypted) {
    if (tep_not_null($plain) && tep_not_null($encrypted)) {
// split apart the hash / salt
      $stack = explode(':', $encrypted);

      if (sizeof($stack) != 2) return false;

      if (md5($stack[1] . $plain) == $stack[0]) {
        return true;
      }
    }

    return false;
  }

////
// This function makes a new password from a plaintext password. 
  function tep_encrypt_password($plain) {
    global $hasher;
    if (!is_object($hasher)) {
      require_once(DIR_WS_CLASSES . 'PasswordHash.php');
// hard coded: number of base-2 logarithms of the iteration count used for password stretching (10)
// and the use of portable hashes
      $hasher = new PasswordHash(10, true); 
    }
    $password = $hasher->HashPassword($plain);
    return $password;
  }

  function tep_encrypt_old_password($plain) {
    $password = '';

    for ($i=0; $i<10; $i++) {
      $password .= tep_rand();
    }

    $salt = substr(md5($password), 0, 2);

    $password = md5($salt . $plain) . ':' . $salt;

    return $password;
  }
  
  function tep_what_password($encrypted) {
    if (strlen($encrypted) > 20 && (substr($encrypted, 0, 3) == '$P$')) {
// phpass style starting with $P$
      return 'phpass';
    } elseif ((substr($encrypted, 0, 3) != '$P$') && strlen($encrypted) == 35 && (32 == strpos($encrypted, ':'))) {
// password hash with salt (old version)
      return 'salted';  
    } else {
      return 'unknown';
    }
  }
?>