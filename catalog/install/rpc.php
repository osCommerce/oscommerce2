<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  header('Cache-Control: no-cache, must-revalidate');
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

  require('includes/application.php');

  $dir_fs_www_root = dirname(__FILE__);

  if (isset($_GET['action']) && !empty($_GET['action'])) {
    switch ($_GET['action']) {
      case 'dbCheck':
        $db = array('DB_SERVER' => trim(rawurldecode($_GET['server'])),
                    'DB_SERVER_USERNAME' => trim(rawurldecode($_GET['username'])),
                    'DB_SERVER_PASSWORD' => trim(rawurldecode($_GET['password'])),
                    'DB_DATABASE' => trim(rawurldecode($_GET['name']))
                   );

        $db_error = false;
        osc_db_connect($db['DB_SERVER'], $db['DB_SERVER_USERNAME'], $db['DB_SERVER_PASSWORD']);

        if ($db_error == false) {
          osc_db_select_db($db['DB_DATABASE']);
        }

        if ($db_error != false) {
          echo '[[0|' . $db_error . ']]';
        } else {
          echo '[[1]]';
        }

        exit;
        break;

      case 'dbImport':
        $db = array('DB_SERVER' => trim(rawurldecode($_GET['server'])),
                    'DB_SERVER_USERNAME' => trim(rawurldecode($_GET['username'])),
                    'DB_SERVER_PASSWORD' => trim(rawurldecode($_GET['password'])),
                    'DB_DATABASE' => trim(rawurldecode($_GET['name'])),
                   );

        osc_db_connect($db['DB_SERVER'], $db['DB_SERVER_USERNAME'], $db['DB_SERVER_PASSWORD']);

        $db_error = false;
        $sql_file = $dir_fs_www_root . '/oscommerce.sql';

        osc_set_time_limit(0);
        osc_db_install($db['DB_DATABASE'], $sql_file);

        if ($db_error != false) {
          echo '[[0|' . $db_error . ']]';
        } else {
          echo '[[1]]';
        }

        exit;
        break;
    }
  }

  echo '[[-100|noActionError]]';
?>
