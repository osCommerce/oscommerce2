<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $OSCOM_PayPal->install($current_module);

  $OSCOM_PayPal->addAlert($OSCOM_PayPal->getDef('alert_module_install_success'), 'success');

  tep_redirect(tep_href_link('paypal.php', 'action=configure&module=' . $current_module));
?>
