<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class hook_admin_orders_paypal {
    function listen_orderAction() {
      if ( !class_exists('paypal_hook_admin_orders_action') ) {
        include(DIR_FS_CATALOG . 'includes/apps/paypal/hooks/admin/orders/action.php');
      }

      $hook = new paypal_hook_admin_orders_action();

      return $hook->execute();
    }

    function listen_orderTab() {
      if ( !class_exists('paypal_hook_admin_orders_tab') ) {
        include(DIR_FS_CATALOG . 'includes/apps/paypal/hooks/admin/orders/tab.php');
      }

      $hook = new paypal_hook_admin_orders_tab();

      return $hook->execute();
    }
  }
?>
