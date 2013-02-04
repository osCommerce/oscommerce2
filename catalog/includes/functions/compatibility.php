<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

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