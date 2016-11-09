<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  class hook_admin_orders_braintree {
    function listen_orderAction() {
      if ( !class_exists('braintree_hook_admin_orders_action') ) {
        include(DIR_FS_CATALOG . 'includes/apps/braintree/hooks/admin/orders/action.php');
      }

      $hook = new braintree_hook_admin_orders_action();

      return $hook->execute();
    }

    function listen_orderTab() {
      if ( !class_exists('braintree_hook_admin_orders_tab') ) {
        include(DIR_FS_CATALOG . 'includes/apps/braintree/hooks/admin/orders/tab.php');
      }

      $hook = new braintree_hook_admin_orders_tab();

      return $hook->execute();
    }
  }
?>
