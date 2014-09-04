<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  if ( file_exists(DIR_FS_CATALOG . 'includes/modules/payment/' . $OSCOM_PayPal->_map[$current_module]['code'] . '.php') ) {
    if ( !class_exists($OSCOM_PayPal->_map[$current_module]['code']) ) {
      include(DIR_FS_CATALOG . 'includes/modules/payment/' . $OSCOM_PayPal->_map[$current_module]['code'] . '.php');
    }

    $class = $OSCOM_PayPal->_map[$current_module]['code'];
    $module = new $class();

    $cut = 'OSCOM_APP_PAYPAL_' . $current_module . '_';
    $cut_length = strlen($cut);

    foreach ( array_merge($module->keys(true), $module->keys(false)) as $key ) {
      $p = strtolower(substr($key, $cut_length));

      $cfg_class = 'OSCOM_PayPal_' . $current_module . '_Cfg_' . $p;

      if ( !class_exists($cfg_class) ) {
        include(DIR_FS_CATALOG . 'includes/apps/paypal/modules/' . $current_module . '/cfg_params/' . $p . '.php');
      }

      $cfg = new $cfg_class();

      $OSCOM_PayPal->saveParameter($key, $cfg->default, isset($cfg->title) ? $cfg->title : null, isset($cfg->description) ? $cfg->description : null);
    }

    $installed = explode(';', MODULE_PAYMENT_INSTALLED);
    $installed[] = $OSCOM_PayPal->_map[$current_module]['code'] . '.php';

    $OSCOM_PayPal->saveParameter('MODULE_PAYMENT_INSTALLED', implode(';', $installed));

    $OSCOM_PayPal->addAlert('Module has been successfully installed.', 'success');

    tep_redirect(tep_href_link('paypal.php', 'action=configure&module=' . $current_module));
  }
?>
