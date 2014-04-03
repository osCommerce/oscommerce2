<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

  require('../includes/languages/' . $language . '/modules/content/login/cm_paypal_login.php');
  require('../includes/modules/content/login/cm_paypal_login.php');

  if (defined('MODULE_CONTENT_PAYPAL_LOGIN_STATUS')) {
    $paypal_login = new cm_paypal_login();

    $params = array('code' => 'oscom2_conn_test');

    $response = $paypal_login->getToken($params);

    if ( is_array($response) && isset($response['error']) ) {
      echo '<h1 id="ppctresult">' . MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_SUCCESS_TITLE . '</h1>';

      if (MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE == 'Live') {
        echo '<p>' . MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_SUCCESS_TEXT_LIVE . '</p>';
      } else {
        echo '<p>' . MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_SUCCESS_TEXT_TEST . '</p>';
      }
    } else {
      echo '<h1 id="ppctresult">' . MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_ERROR_TITLE . '</h1>';

      if (MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE == 'Live') {
        echo '<p>' . MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_ERROR_TEXT_LIVE . '</p>';
      } else {
        echo '<p>' . MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_ERROR_TEXT_TEST . '</p>';
      }
    }
  } else {
    echo '<h1 id="ppctresult">' . MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_TITLE_ERROR . '</h1>' .
         '<p>' . MODULE_CONTENT_PAYPAL_LOGIN_DIALOG_CONNECTION_NOT_INSTALLED . '</p>';
  }
?>
