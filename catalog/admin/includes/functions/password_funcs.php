<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

////
// This function validates a plain text password with a
// salted or phpass password
  function tep_validate_password($plain, $encrypted) {
    if (tep_not_null($plain) && tep_not_null($encrypted)) {
      if (tep_password_type($encrypted) == 'salt') {
        return tep_validate_old_password($plain, $encrypted);
      }

      if (!class_exists('PasswordHash')) {
        include('includes/classes/passwordhash.php');
      }

      $hasher = new PasswordHash(10, true);

      return $hasher->CheckPassword($plain, $encrypted);
    }

    return false;
  }

////
// This function validates a plain text password with a
// salted password
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
// This function encrypts a phpass password from a plaintext
// password.
  function tep_encrypt_password($plain) {
    if (!class_exists('PasswordHash')) {
      include('includes/classes/passwordhash.php');
    }

    $hasher = new PasswordHash(10, true);

    return $hasher->HashPassword($plain);
  }

////
// This function encrypts a salted password from a plaintext
// password.
  function tep_encrypt_old_password($plain) {
    $password = '';

    for ($i=0; $i<10; $i++) {
      $password .= tep_rand();
    }

    $salt = substr(md5($password), 0, 2);

    $password = md5($salt . $plain) . ':' . $salt;

    return $password;
  }

////
// This function returns the type of the encrpyted password
// (phpass or salt)
  function tep_password_type($encrypted) {
    if (preg_match('/^[A-Z0-9]{32}\:[A-Z0-9]{2}$/i', $encrypted) === 1) {
      return 'salt';
    }

    return 'phpass';
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
