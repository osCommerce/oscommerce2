<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $OSCOM_PayPal->uninstall($current_module);

  $installed = explode(';', MODULE_PAYMENT_INSTALLED);
  $installed_pos = array_search($OSCOM_PayPal->getModuleInfo($current_module, 'pm_code') . '.php', $installed);

  if ( $installed_pos !== false ) {
    unset($installed[$installed_pos]);

    $OSCOM_PayPal->saveParameter('MODULE_PAYMENT_INSTALLED', implode(';', $installed));
  }

  $OSCOM_PayPal->addAlert('Module has been successfully uninstalled.', 'success');

  tep_redirect(tep_href_link('paypal.php', 'action=configure&module=' . $current_module));
?>
