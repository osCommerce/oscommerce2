<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_orders {
    public static function execute(app $app) {
      global $OSCOM_NavigationHistory, $breadcrumb;

      if ( !isset($_SESSION['customer_id']) ) {
        $OSCOM_NavigationHistory->setSnapshot();

        osc_redirect(osc_href_link('account', 'login', 'SSL'));
      }

      $app->setContentFile('orders.php');

      $breadcrumb->add(NAVBAR_TITLE_ORDERS, osc_href_link('account', 'orders', 'SSL'));
    }
  }
?>
