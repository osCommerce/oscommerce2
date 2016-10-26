<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
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

  require('includes/filenames.php');
  require('includes/functions/general.php');
  require('includes/classes/logger.php');
  require('includes/classes/shopping_cart.php');
  require('includes/classes/language.php');
  require('includes/functions/validations.php');
  require('includes/classes/table_block.php');
  require('includes/classes/box.php');
  require('includes/classes/object_info.php');
  require('includes/classes/mime.php');
  require('includes/classes/email.php');
  require('includes/classes/upload.php');
  require('includes/classes/action_recorder.php');
  require('includes/classes/cfg_modules.php');

  require(OSCOM::getConfig('dir_root', 'Shop') . 'includes/classes/osc_template.php');

  OSCOM::loadSite('Admin');

  if (($request_type === 'NONSSL') && ($_SERVER['REQUEST_METHOD'] === 'GET') && (parse_url(OSCOM::getConfig('http_server'), PHP_URL_SCHEME) == 'https')) {
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

// the following cache blocks are used in the Tools->Cache section
// ('language' in the filename is automatically replaced by available languages)
  $cache_blocks = array(array('title' => TEXT_CACHE_CATEGORIES, 'code' => 'categories', 'file' => 'categories_box-language.cache', 'multiple' => true),
                        array('title' => TEXT_CACHE_MANUFACTURERS, 'code' => 'manufacturers', 'file' => 'manufacturers_box-language.cache', 'multiple' => true),
                        array('title' => TEXT_CACHE_ALSO_PURCHASED, 'code' => 'also_purchased', 'file' => 'also_purchased-language.cache', 'multiple' => true)
                       );

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
        if ( is_file(OSCOM::getConfig('dir_root') . 'includes/languages/' . $_SESSION['language'] . '/modules/boxes/' . $file) ) {
          include(OSCOM::getConfig('dir_root') . 'includes/languages/' . $_SESSION['language'] . '/modules/boxes/' . $file);
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
