<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Apps;
  use OSC\OM\HTTP;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

// Start the clock for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());
  define('OSCOM_BASE_DIR', realpath(__DIR__ . '/../../includes/OSC/') . '/');

// Set the level of error reporting
  error_reporting(E_ALL & ~E_DEPRECATED);

  require(OSCOM_BASE_DIR . 'OM/OSCOM.php');
  spl_autoload_register('OSC\OM\OSCOM::autoload');

  OSCOM::initialize();

  if (PHP_VERSION_ID < 70000) {
    include(OSCOM::getConfig('dir_root', 'Shop') . 'includes/third_party/random_compat/random.php');
  }

  require('includes/filenames.php');
  require('includes/functions/general.php');
  require('includes/classes/logger.php');
  require('includes/classes/shopping_cart.php');
  require('includes/classes/table_block.php');
  require('includes/classes/box.php');
  require('includes/classes/object_info.php');
  require('includes/classes/upload.php');
  require('includes/classes/action_recorder.php');
  require('includes/classes/cfg_modules.php');

  require(OSCOM::getConfig('dir_root', 'Shop') . 'includes/classes/osc_template.php');

  OSCOM::loadSite('Admin');

  if ((HTTP::getRequestType() === 'NONSSL') && ($_SERVER['REQUEST_METHOD'] === 'GET') && (parse_url(OSCOM::getConfig('http_server'), PHP_URL_SCHEME) == 'https')) {
    $url_req = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    HTTP::redirect($url_req, 301);
  }

  $OSCOM_Db = Registry::get('Db');
  $OSCOM_Hooks = Registry::get('Hooks');
  $OSCOM_Language = Registry::get('Language');
  $OSCOM_MessageStack = Registry::get('MessageStack');

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
    $cPath_array = [];
    $current_category_id = 0;
  }

  $admin_menu = [];
  $cl_box_groups = array();
  $cl_apps_groups = array();

  if (isset($_SESSION['admin'])) {
    if ($dir = @dir(OSCOM::getConfig('dir_root') . 'includes/boxes')) {
      $files = array();

      while ($file = $dir->read()) {
        if (!is_dir($dir->path . '/' . $file)) {
          if (substr($file, strrpos($file, '.')) == '.php') {
            $files[] = $file;
          }
        }
      }

      $dir->close();

      natcasesort($files);

      foreach ( $files as $file ) {
        if ($OSCOM_Language->definitionsExist('modules/boxes/' . pathinfo($file, PATHINFO_FILENAME))) {
          $OSCOM_Language->loadDefinitions('modules/boxes/'. pathinfo($file, PATHINFO_FILENAME));
        }

        include($dir->path . '/' . $file);
      }
    }

    foreach (Apps::getModules('AdminMenu') as $m) {
      $appmenu = call_user_func([$m, 'execute']);

      if (is_array($appmenu) && !empty($appmenu)) {
        $cl_apps_groups[] = $appmenu;
      }
    }
  }

  usort($cl_box_groups, function ($a, $b) {
    return strcasecmp($a['heading'], $b['heading']);
  });

  foreach ( $cl_box_groups as &$group ) {
    usort($group['apps'], function ($a, $b) {
      return strcasecmp($a['title'], $b['title']);
    });
  }

  unset($group); // unset reference variable
?>
