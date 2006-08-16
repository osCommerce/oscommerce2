<?php
/*
  $Id: compatibility.php,v 1.19 2003/04/09 16:12:54 project3000 Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2006 osCommerce

  Released under the GNU General Public License

  Modified by Marco Canini, <m.canini@libero.it>
  - Fixed a bug with arrays in $HTTP_xxx_VARS
*/

////
// Recursively handle magic_quotes_gpc turned off.
// This is due to the possibility of have an array in
// $HTTP_xxx_VARS
// Ie, products attributes
  function do_magic_quotes_gpc(&$ar) {
    if (!is_array($ar)) return false;

    while (list($key, $value) = each($ar)) {
      if (is_array($ar[$key])) {
        do_magic_quotes_gpc($ar[$key]);
      } else {
        $ar[$key] = addslashes($value);
      }
    }
  }

// $HTTP_xxx_VARS are always set on php4
  if (!is_array($HTTP_GET_VARS)) $HTTP_GET_VARS = array();
  if (!is_array($HTTP_POST_VARS)) $HTTP_POST_VARS = array();
  if (!is_array($HTTP_COOKIE_VARS)) $HTTP_COOKIE_VARS = array();

// handle magic_quotes_gpc turned off.
  if (!get_magic_quotes_gpc()) {
    do_magic_quotes_gpc($HTTP_GET_VARS);
    do_magic_quotes_gpc($HTTP_POST_VARS);
    do_magic_quotes_gpc($HTTP_COOKIE_VARS);
  }

  if (!function_exists('array_splice')) {
    function array_splice(&$array, $maximum) {
      if (sizeof($array) >= $maximum) {
        for ($i=0; $i<$maximum; $i++) {
          $new_array[$i] = $array[$i];
        }
        $array = $new_array;
      }
    }
  }

  if (!function_exists('in_array')) {
    function in_array($lookup_value, $lookup_array) {
      reset($lookup_array);
      while (list($key, $value) = each($lookup_array)) {
        if ($value == $lookup_value) return true;
      }

      return false;
    }
  }

  if (!function_exists('array_reverse')) {
    function array_reverse($array) {
      for ($i=0, $n=sizeof($array); $i<$n; $i++) $array_reversed[$i] = $array[($n-$i-1)];

      return $array_reversed;
    }
  }

  if (!function_exists('constant')) {
    function constant($constant) {
      eval("\$temp=$constant;");

      return $temp;
    }
  }

  if (!function_exists('is_null')) {
    function is_null($value) {
      if (is_array($value)) {
        if (sizeof($value) > 0) {
          return false;
        } else {
          return true;
        }
      } else {
        if (($value != '') && ($value != 'NULL') && (strlen(trim($value)) > 0)) {
          return false;
        } else {
          return true;
        }
      }
    }
  }

  if (!function_exists('array_merge')) {
    function array_merge($array1, $array2, $array3 = '') {
      if (empty($array3) && !is_array($array3)) $array3 = array();
      while (list($key, $val) = each($array1)) $array_merged[$key] = $val;
      while (list($key, $val) = each($array2)) $array_merged[$key] = $val;
      if (sizeof($array3) > 0) while (list($key, $val) = each($array3)) $array_merged[$key] = $val;

      return (array) $array_merged;
    }
  }

  if (!function_exists('is_numeric')) {
    function is_numeric($param) {
      return ereg('^[0-9]{1,50}.?[0-9]{0,50}$', $param);
    }
  }

  if (!function_exists('array_slice')) {
    function array_slice($array, $offset, $length = 0) {
      if ($offset < 0 ) {
        $offset = sizeof($array) + $offset;
      }
      $length = ((!$length) ? sizeof($array) : (($length < 0) ? sizeof($array) - $length : $length + $offset));
      for ($i = $offset; $i<$length; $i++) {
        $tmp[] = $array[$i];
      }

      return $tmp;
    }
  }

  if (!function_exists('array_map')) {
    function array_map($callback, $array) {
      if (is_array($array)) {
        $_new_array = array();
        reset($array);
        while (list($key, $value) = each($array)) {
          $_new_array[$key] = array_map($callback, $array[$key]);
        }
        return $_new_array;
      } else {
        return $callback($array);
      }
    }
  }

  if (!function_exists('str_repeat')) {
    function str_repeat($string, $number) {
      $repeat = '';

      for ($i=0; $i<$number; $i++) {
        $repeat .= $string;
      }

      return $repeat;
    }
  }

  if (!function_exists('checkdnsrr')) {
    function checkdnsrr($host, $type) {
      if(tep_not_null($host) && tep_not_null($type)) {
        @exec("nslookup -type=$type $host", $output);
        while(list($k, $line) = each($output)) {
          if(eregi("^$host", $line)) {
            return true;
          }
        }
      }
      return false;
    }
  }
?>