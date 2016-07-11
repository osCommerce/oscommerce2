<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

// Start the clock for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());
  define('OSCOM_BASE_DIR', realpath(__DIR__ . '/../../includes/') . '/');

// Set the level of error reporting
  error_reporting(E_ALL & ~E_DEPRECATED);

// load server configuration parameters
  if (file_exists('includes/local/configure.php')) { // for developers
    include('includes/local/configure.php');
  } else {
    include('includes/configure.php');
  }

  require(OSCOM_BASE_DIR . 'OSC/OM/OSCOM.php');
  spl_autoload_register('OSC\OM\OSCOM::autoload');

  require(DIR_WS_INCLUDES . 'filenames.php');
  require(DIR_WS_INCLUDES . 'database_tables.php');
  require(DIR_WS_FUNCTIONS . 'database.php');
  require(DIR_WS_FUNCTIONS . 'general.php');
  require(DIR_WS_CLASSES . 'logger.php');
  require(DIR_WS_CLASSES . 'shopping_cart.php');
  require(DIR_WS_CLASSES . 'language.php');
  require(DIR_WS_FUNCTIONS . 'localization.php');
  require(DIR_WS_FUNCTIONS . 'validations.php');
  require(DIR_WS_CLASSES . 'table_block.php');
  require(DIR_WS_CLASSES . 'box.php');
  require(DIR_WS_CLASSES . 'message_stack.php');
  require(DIR_WS_CLASSES . 'split_page_results.php');
  require(DIR_WS_CLASSES . 'object_info.php');
  require(DIR_WS_CLASSES . 'mime.php');
  require(DIR_WS_CLASSES . 'email.php');
  require(DIR_WS_CLASSES . 'upload.php');
  require(DIR_WS_CLASSES . 'action_recorder.php');
  require(DIR_WS_CLASSES . 'cfg_modules.php');

  OSCOM::initialize('Admin');

// calculate category path
  if (isset($_GET['cPath'])) {
    $cPath = $_GET['cPath'];
  } else {
    $cPath = '';
  }

  if (tep_not_null($cPath)) {
    $cPath_array = tep_parse_category_path($cPath);
    $cPath = implode('_', $cPath_array);
    $current_category_id = $cPath_array[(sizeof($cPath_array)-1)];
  } else {
    $current_category_id = 0;
  }

// the following cache blocks are used in the Tools->Cache section
// ('language' in the filename is automatically replaced by available languages)
  $cache_blocks = array(array('title' => TEXT_CACHE_CATEGORIES, 'code' => 'categories', 'file' => 'categories_box-language.cache', 'multiple' => true),
                        array('title' => TEXT_CACHE_MANUFACTURERS, 'code' => 'manufacturers', 'file' => 'manufacturers_box-language.cache', 'multiple' => true),
                        array('title' => TEXT_CACHE_ALSO_PURCHASED, 'code' => 'also_purchased', 'file' => 'also_purchased-language.cache', 'multiple' => true)
                       );
?>
