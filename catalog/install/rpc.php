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

  $result = [
    'status' => '-100',
    'message' => 'noActionError'
  ];

  if (isset($_GET['action']) && !empty($_GET['action'])) {
    switch ($_GET['action']) {
      case 'dbCheck':
        try {
          $OSCOM_Db = Db::initialize($_POST['server'], $_POST['username'], $_POST['password'], $_POST['name']);

          $result['status'] = '1';
          $result['message'] = 'success';
        } catch (\Exception $e) {
          $result['status'] = $e->getCode();
          $result['message'] = $e->getMessage();

          if (($e->getCode() == '1049') && isset($_GET['createDb']) && ($_GET['createDb'] == 'true')) {
            try {
              $OSCOM_Db = Db::initialize($_POST['server'], $_POST['username'], $_POST['password'], '');

              $OSCOM_Db->exec('create database ' . Db::prepareIdentifier($_POST['name']) . ' character set utf8 collate utf8_unicode_ci');

              $result['status'] = '1';
              $result['message'] = 'success';
            } catch (\Exception $e2) {
              $result['status'] = $e2->getCode();
              $result['message'] = $e2->getMessage();
            }
          }
        }

        break;

      case 'dbImport':
        try {
          $OSCOM_Db = Db::initialize($_POST['server'], $_POST['username'], $_POST['password'], $_POST['name']);
          $OSCOM_Db->importSQL($dir_fs_www_root . '/oscommerce.sql');

          $result['status'] = '1';
          $result['message'] = 'success';
        } catch (\Exception $e) {
          $result['status'] = $e->getCode();
          $result['message'] = $e->getMessage();
        }

        break;
    }
  }

  echo json_encode($result);
?>
