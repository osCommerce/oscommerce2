<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  function app_paypal_get_admin_box_links() {
    $paypal_menu = array(
      array('code' => 'paypal.php',
            'title' => MODULES_ADMIN_MENU_PAYPAL_START,
            'link' => tep_href_link('paypal.php'))
    );

    $paypal_menu_check = array('OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL',
                               'OSCOM_APP_PAYPAL_LIVE_API_USERNAME',
                               'OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL',
                               'OSCOM_APP_PAYPAL_SANDBOX_API_USERNAME',
                               'OSCOM_APP_PAYPAL_PF_LIVE_VENDOR',
                               'OSCOM_APP_PAYPAL_PF_SANDBOX_VENDOR');

    foreach ( $paypal_menu_check as $value ) {
      if ( defined($value) && tep_not_null(constant($value)) ) {
        $paypal_menu = array(
          array('code' => 'paypal.php',
                'title' => MODULES_ADMIN_MENU_PAYPAL_BALANCE,
                'link' => tep_href_link('paypal.php', 'action=balance')),
          array('code' => 'paypal.php',
                'title' => MODULES_ADMIN_MENU_PAYPAL_CONFIGURE,
                'link' => tep_href_link('paypal.php', 'action=configure')),
          array('code' => 'paypal.php',
                'title' => MODULES_ADMIN_MENU_PAYPAL_MANAGE_CREDENTIALS,
                'link' => tep_href_link('paypal.php', 'action=credentials')),
          array('code' => 'paypal.php',
                'title' => MODULES_ADMIN_MENU_PAYPAL_LOG,
                'link' => tep_href_link('paypal.php', 'action=log'))
        );

        break;
      }
    }

    return $paypal_menu;
  }
?>
