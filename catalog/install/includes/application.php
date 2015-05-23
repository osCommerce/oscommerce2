<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

// set the level of error reporting
  error_reporting(E_ALL | E_STRICT);
  ini_set('display_errors', true); // TODO remove on release

// set default timezone if none exists (PHP 5.3 throws an E_WARNING)
  date_default_timezone_set(date_default_timezone_get());

  require('../includes/autoload.php');

  require('includes/functions/general.php');
?>
