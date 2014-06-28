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

    $module->remove(true);

    $installed = explode(';', MODULE_PAYMENT_INSTALLED);
    $installed_pos = array_search($OSCOM_PayPal->_map[$current_module]['code'] . '.php', $installed);

    if ( $installed_pos !== false ) {
      unset($installed[$installed_pos]);

      $OSCOM_PayPal->saveParameter('MODULE_PAYMENT_INSTALLED', implode(';', $installed));

      $messageStack->add_session('Module successfully uninstalled.', 'success');
    }

    tep_redirect(tep_href_link('paypal.php', 'action=configure&module=' . $current_module));
  }
?>
