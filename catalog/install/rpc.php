<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Db;
  use OSC\OM\HTTP;
  use OSC\OM\OSCOM;

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
      case 'httpsCheck':
        if (isset($_GET['subaction']) && ($_GET['subaction'] == 'do')) {
          if ((isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on')) || (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443))) {
            $result['status'] = '1';
            $result['message'] = 'success';
          }
        } else {
          $url = 'https://' . $_SERVER['HTTP_HOST'];

          if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
            $url .= $_SERVER['REQUEST_URI'];
          } else {
            $url .= $_SERVER['SCRIPT_FILENAME'];
          }

          $url .= '&subaction=do';

          // errors are silenced to not log failed connection checks
          $response = @HTTP::getResponse([
            'url' => $url,
            'verify_ssl' => false
          ]);

          if (!empty($response)) {
            $response = json_decode($response, true);

            if (is_array($response) && isset($response['status']) && ($response['status'] == '1')) {
              $result['status'] = '1';
              $result['message'] = 'success';
            }
          }
        }

        break;

      case 'dbCheck':
        try {
          $OSCOM_Db = Db::initialize(isset($_POST['server']) ? $_POST['server'] : '', isset($_POST['username']) ? $_POST['username'] : '', isset($_POST['password']) ? $_POST['password'] : '', isset($_POST['name']) ? $_POST['name'] : '');

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
          $OSCOM_Db = Db::initialize(isset($_POST['server']) ? $_POST['server'] : '', isset($_POST['username']) ? $_POST['username'] : '', isset($_POST['password']) ? $_POST['password'] : '', isset($_POST['name']) ? $_POST['name'] : '');
          $OSCOM_Db->setTablePrefix('');

          $OSCOM_Db->exec('SET FOREIGN_KEY_CHECKS = 0');

          foreach (glob(OSCOM::BASE_DIR . 'Schema/*.txt') as $f) {
              $schema = $OSCOM_Db->getSchemaFromFile($f);

              $sql = $OSCOM_Db->getSqlFromSchema($schema, $_POST['prefix']);

              $OSCOM_Db->exec('DROP TABLE IF EXISTS ' . $_POST['prefix'] . basename($f, '.txt'));

              $OSCOM_Db->exec($sql);
          }

          $OSCOM_Db->importSQL($dir_fs_www_root . '/oscommerce.sql', $_POST['prefix']);

          $OSCOM_Db->exec('SET FOREIGN_KEY_CHECKS = 1');

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
