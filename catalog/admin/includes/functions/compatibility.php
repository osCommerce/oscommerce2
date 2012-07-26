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
 * http_build_query() natively supported from PHP 5.0
 * From Pear::PHP_Compat
 */

  if ( !function_exists('http_build_query') && (PHP_VERSION >= 4)) {
    function http_build_query($formdata, $numeric_prefix = null, $arg_separator = null) {
// If $formdata is an object, convert it to an array
      if ( is_object($formdata) ) {
        $formdata = get_object_vars($formdata);
      }

// Check we have an array to work with
      if ( !is_array($formdata) || !empty($formdata) ) {
        return false;
      }

// Argument seperator
      if ( empty($arg_separator) ) {
        $arg_separator = ini_get('arg_separator.output');

        if ( empty($arg_separator) ) {
          $arg_separator = '&';
        }
      }

// Start building the query
      $tmp = array();

      foreach ( $formdata as $key => $val ) {
        if ( is_null($val) ) {
          continue;
        }

        if ( is_integer($key) && ( $numeric_prefix != null ) ) {
          $key = $numeric_prefix . $key;
        }

        if ( is_scalar($val) ) {
          array_push($tmp, urlencode($key) . '=' . urlencode($val));
          continue;
        }

// If the value is an array, recursively parse it
        if ( is_array($val) || is_object($val) ) {
          array_push($tmp, http_build_query_helper($val, urlencode($key), $arg_separator));
          continue;
        }

// The value is a resource
        return null;
      }

      return implode($arg_separator, $tmp);
    }

// Helper function
    function http_build_query_helper($array, $name, $arg_separator) {
      $tmp = array();

      foreach ( $array as $key => $value ) {
        if ( is_array($value) ) {
          array_push($tmp, http_build_query_helper($value, sprintf('%s[%s]', $name, $key), $arg_separator));
        } elseif ( is_scalar($value) ) {
          array_push($tmp, sprintf('%s[%s]=%s', $name, urlencode($key), urlencode($value)));
        } elseif ( is_object($value) ) {
          array_push($tmp, http_build_query_helper(get_object_vars($value), sprintf('%s[%s]', $name, $key), $arg_separator));
        }
      }

      return implode($arg_separator, $tmp);
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
