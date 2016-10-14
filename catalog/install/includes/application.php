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
  error_reporting(E_ALL & ~E_DEPRECATED);

  define('OSCOM_BASE_DIR', realpath(__DIR__ . '/../../includes/') . '/OSC/');

  require(OSCOM_BASE_DIR . 'OM/OSCOM.php');
  spl_autoload_register('OSC\OM\OSCOM::autoload');

// include manually as OSC\OM\Hash relies on OSCOM::getConfig('dir_root', 'Shop') which is currently empty
  require(dirname(OSCOM_BASE_DIR) . '/classes/passwordhash.php');

  OSCOM::initialize();
?>
