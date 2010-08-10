<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2009 osCommerce

  Released under the GNU General Public License
*/

////
// This funstion validates a plain text password with an
// encrpyted password
  function tep_validate_password($plain, $encrypted, $admin_id = '') {
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
              if (strlen($new_password_hash) > 19 && (int)$admin_id > 0) {
                tep_db_query("update " . TABLE_ADMINISTRATORS . " set user_password = '" . $new_password_hash . "' where id = '" . (int)$admin_id . "'");
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
// Since the admin(s) always have access to the database and can truncate the table
// administrators when moving to a server that does the encryption differently we can go for
// the better security and not use portable hashes
            $hasher = new PasswordHash(10, false);
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
// Since the admin(s) always have access to the database and can truncate the table
// administrators when moving to a server that does the encryption differently we can go for
// the better security and not use portable hashes
      $hasher = new PasswordHash(10, false); 
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
    if (strlen($encrypted) == 20 || (strlen($encrypted) > 20 && (substr($encrypted, 0, 3) == '$P$'))  || (strlen($encrypted) == 60 && (substr($encrypted, 0, 4) == '$2a$'))) {
// phpass style starting with $P$ (portable), $2a$ (CRYPT_BLOWFISH) or length 20 (CRYPT_EXT_DES)
      return 'phpass';
    } elseif ((substr($encrypted, 0, 3) != '$P$') && strlen($encrypted) == 35 && (32 == strpos($encrypted, ':'))) {
// password hash with salt (old version)
      return 'salted';  
    } else {
      return 'unknown';
    }
  }

////
// This function produces a crypted string using the APR-MD5 algorithm
// Source: http://www.php.net/crypt
  function tep_crypt_apr_md5($password, $salt = null) {
    if (empty($salt)) {
      $salt_string = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

      $salt = '';

      for ($i = 0; $i < 8; $i++) {
        $salt .= $salt_string[rand(0, 61)];
      }
    }

    $len = strlen($password);

    $result = $password . '$apr1$' . $salt;

    $bin = pack('H32', md5($password . $salt . $password));

    for ($i=$len; $i>0; $i-=16) {
      $result .= substr($bin, 0, min(16, $i));
    }

    for ($i=$len; $i>0; $i>>= 1) {
      $result .= ($i & 1) ? chr(0) : $password[0];
    }

    $bin = pack('H32', md5($result));

    for ($i=0; $i<1000; $i++) {
      $new = ($i & 1) ? $password : $bin;

      if ($i % 3) {
        $new .= $salt;
      }

      if ($i % 7) {
        $new .= $password;
      }

      $new .= ($i & 1) ? $bin : $password;

      $bin = pack('H32', md5($new));
    }

    for ($i=0; $i<5; $i++) {
      $k = $i + 6;
      $j = $i + 12;

      if ($j == 16) {
        $j = 5;
      }

      $tmp = $bin[$i] . $bin[$k] . $bin[$j] . $tmp;
    }

    $tmp = chr(0) . chr(0) . $bin[11] . $tmp;
    $tmp = strtr(strrev(substr(base64_encode($tmp), 2)), 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/', './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');

    return '$apr1$' . $salt . '$' . $tmp;
  }
?>
