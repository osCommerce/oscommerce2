<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_products_action_notify {
    public static function execute(app $app) {
      global $OSCOM_Customer, $OSCOM_NavigationHistory, $product_exists;

      if ( $product_exists === false ) {
        osc_redirect(osc_href_link());
      }

// if the customer is not logged on, redirect them to the login page
      if ( !$OSCOM_Customer->isLoggedOn() ) {
        $OSCOM_NavigationHistory->setSnapshot();

        osc_redirect(osc_href_link('account', 'login', 'SSL'));
      }
    }
  }
?>
