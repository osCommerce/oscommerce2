<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

// to be removed
  $HTTP_GET_VARS =& $_GET;
  $HTTP_POST_VARS =& $_POST;
  $HTTP_COOKIE_VARS =& $_COOKIE;
  $HTTP_SESSION_VARS =& $_SESSION;
  $HTTP_POST_FILES =& $_FILES;
  $HTTP_SERVER_VARS =& $_SERVER;

// set default timezone if none exists (PHP 5.3 throws an E_WARNING)
  date_default_timezone_set(defined('CFG_TIME_ZONE') ? CFG_TIME_ZONE : date_default_timezone_get());
?>
