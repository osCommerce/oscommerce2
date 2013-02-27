<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_address_book {
    public static function execute(app $app) {
      global $OSCOM_NavigationHistory, $OSCOM_Breadcrumb;

      if ( !isset($_SESSION['customer_id']) ) {
        $OSCOM_NavigationHistory->setSnapshot();

        osc_redirect(osc_href_link('account', 'login', 'SSL'));
      }

      $app->setContentFile('address_book.php');

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE_ADDRESS_BOOK, osc_href_link('account', 'address_book', 'SSL'));
    }
  }
?>
