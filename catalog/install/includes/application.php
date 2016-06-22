<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

// set the level of error reporting
  error_reporting(E_ALL | E_STRICT);
  ini_set('display_errors', true); // TODO remove on release

  define('OSCOM_BASE_DIR', realpath(__DIR__ . '/../../includes/') . '/');

  require(OSCOM_BASE_DIR . 'OSC/OM/OSCOM.php');
  spl_autoload_register('OSC\OM\OSCOM::autoload');
?>
