<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_orders {
    public static function execute(app $app) {
      global $breadcrumb;

      $app->setContentFile('orders.php');

      $breadcrumb->add(NAVBAR_TITLE_ORDERS, tep_href_link('account', 'orders', 'SSL'));
    }
  }
?>
