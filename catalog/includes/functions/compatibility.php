<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

////
// Recursively handle magic_quotes_gpc turned off.
// This is due to the possibility of have an array in
// $HTTP_xxx_VARS
// Ie, products attributes
  function do_magic_quotes_gpc(&$ar) {
    if (!is_array($ar)) return false;

    reset($ar);
    while (list($key, $value) = each($ar)) {
      if (is_array($ar[$key])) {
        do_magic_quotes_gpc($ar[$key]);
      } else {
        $ar[$key] = addslashes($value);
      }
    }
    reset($ar);
  }

  if (PHP_VERSION >= 4.1) {
    $HTTP_GET_VARS =& $_GET;
    $HTTP_POST_VARS =& $_POST;
    $HTTP_COOKIE_VARS =& $_COOKIE;
    $HTTP_SESSION_VARS =& $_SESSION;
    $HTTP_POST_FILES =& $_FILES;
    $HTTP_SERVER_VARS =& $_SERVER;
  } else {
    if (!is_array($HTTP_GET_VARS)) $HTTP_GET_VARS = array();
    if (!is_array($HTTP_POST_VARS)) $HTTP_POST_VARS = array();
    if (!is_array($HTTP_COOKIE_VARS)) $HTTP_COOKIE_VARS = array();
  }

// handle magic_quotes_gpc turned off.
  if (!get_magic_quotes_gpc()) {
    do_magic_quotes_gpc($HTTP_GET_VARS);
    do_magic_quotes_gpc($HTTP_POST_VARS);
    do_magic_quotes_gpc($HTTP_COOKIE_VARS);
  }

// set default timezone if none exists (PHP 5.3 throws an E_WARNING)
  if (PHP_VERSION >= '5.2') {
    date_default_timezone_set(defined('CFG_TIME_ZONE') ? CFG_TIME_ZONE : date_default_timezone_get());
  }

  if (!function_exists('checkdnsrr')) {
    function checkdnsrr($host, $type) {
      if(tep_not_null($host) && tep_not_null($type)) {
        @exec("nslookup -type=" . escapeshellarg($type) . " " . escapeshellarg($host), $output);
        while(list($k, $line) = each($output)) {
          if(preg_match("/^$host/i", $line)) {
            return true;
          }
        }
      }
      return false;
    }
  }

/*
 * stripos() natively supported from PHP 5.0
 * From Pear::PHP_Compat
 */

  if (!function_exists('stripos')) {
    function stripos($haystack, $needle, $offset = null) {
      $fix = 0;

      if (!is_null($offset)) {
        if ($offset > 0) {
          $haystack = substr($haystack, $offset, strlen($haystack) - $offset);
          $fix = $offset;
        }
      }

      $segments = explode(strtolower($needle), strtolower($haystack), 2);

// Check there was a match
      if (count($segments) == 1) {
        return false;
      }

      $position = strlen($segments[0]) + $fix;

      return $position;
    }
  }
?>