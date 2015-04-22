<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\Db;

  header('Cache-Control: no-cache, must-revalidate');
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

  require('includes/application.php');

  $dir_fs_www_root = dirname(__FILE__);

  $result = false;

  if (isset($_GET['action']) && !empty($_GET['action'])) {
    switch ($_GET['action']) {
      case 'dbCheck':
        try {
          $OSCOM_Db = Db::initialize($_GET['server'], $_GET['username'], $_GET['password'], $_GET['name']);
        } catch (\Exception $e) {
          $result = $e->getCode() . '|' . $e->getMessage();
        }

        if ($result === false) {
          $result = true;
        } else {
          $error = explode('|', $result, 2);

          if (($error[0] == '1049') && isset($_GET['createDb']) && ($_GET['createDb'] == 'true')) {
            $result = false;

            try {
              $OSCOM_Db = Db::initialize($_GET['server'], $_GET['username'], $_GET['password'], '');

              $OSCOM_Db->exec('create database ' . Db::prepareIdentifier($_GET['name']) . ' character set utf8 collate utf8_unicode_ci');
            } catch (\Exception $e) {
              $result = $e->getCode() . '|' . $e->getMessage();
            }

            if ($result === false) {
              $result = true;
            }
          }
        }

        break;

      case 'dbImport':
        try {
          $OSCOM_Db = Db::initialize($_GET['server'], $_GET['username'], $_GET['password'], $_GET['name']);
          $OSCOM_Db->importSQL($dir_fs_www_root . '/oscommerce.sql');
        } catch (\Exception $e) {
          $result = $e->getCode() . '|' . $e->getMessage();
        }

        if ($result === false) {
          $result = true;
        }

        break;
    }
  }

  if ($result === true) {
    echo '[[1|success]]';
  } else {
    $error_no = '-100';
    $error_msg = 'noActionError';

    if ($result !== false) {
      $error = explode('|', $result, 2);

      if (count($error) === 2) {
        $error_no = $error[0];
        $error_msg = $error[1];
      } else {
        $error_code = 0;
        $error_msg = $error[0];
      }
    }

    echo '[[' . $error_no . '|' . $error_msg . ']]';
  }
?>
