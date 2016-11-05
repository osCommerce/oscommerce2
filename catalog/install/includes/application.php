<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;

// set the level of error reporting
  error_reporting(E_ALL & ~E_DEPRECATED);

  define('OSCOM_BASE_DIR', realpath(__DIR__ . '/../../includes/') . '/OSC/');

  require(OSCOM_BASE_DIR . 'OM/OSCOM.php');
  spl_autoload_register('OSC\OM\OSCOM::autoload');

  OSCOM::initialize();
?>
