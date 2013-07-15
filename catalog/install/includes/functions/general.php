<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

////
// Get the installed version number
  function osc_get_version() {
    static $v;

    if (!isset($v)) {
      $v = trim(implode('', file('../includes/version.php')));
    }

    return $v;
  }

////
// Sets timeout for the current script.
// Cant be used in safe mode.
  function osc_set_time_limit($limit) {
    if (!get_cfg_var('safe_mode')) {
      set_time_limit($limit);
    }
  }

  function osc_realpath($directory) {
    return str_replace('\\', '/', realpath($directory));
  }

////
// This function encrypts a phpass password from a plaintext
// password.
  function osc_encrypt_password($plain) {
    if (!class_exists('PasswordHash')) {
      include('../includes/classes/passwordhash.php');
    }

    $hasher = new PasswordHash(10, true);

    return $hasher->HashPassword($plain);
  }

////
// Wrapper function for is_writable() for Windows compatibility
  function osc_is_writable($file) {
    if (strtolower(substr(PHP_OS, 0, 3)) === 'win') {
      if (file_exists($file)) {
        $file = realpath($file);
        if (is_dir($file)) {
          $result = @tempnam($file, 'osc');
          if (is_string($result) && file_exists($result)) {
            unlink($result);
            return (strpos($result, $file) === 0) ? true : false;
          }
        } else {
          $handle = @fopen($file, 'r+');
          if (is_resource($handle)) {
            fclose($handle);
            return true;
          }
        }
      } else{
        $dir = dirname($file);
        if (file_exists($dir) && is_dir($dir) && osc_is_writable($dir)) {
          return true;
        }
      }
      return false;
    } else {
      return is_writable($file);
    }
  }

////
// Parse the data used in the html tags to ensure the tags will not break
  function osc_parse_input_field_data($data, $parse) {
    return strtr(trim($data), $parse);
  }

  function osc_output_string($string, $translate = false, $protected = false) {
    if ($protected == true) {
      return htmlspecialchars($string);
    } else {
      if ($translate == false) {
        return osc_parse_input_field_data($string, array('"' => '&quot;'));
      } else {
        return osc_parse_input_field_data($string, $translate);
      }
    }
  }
?>