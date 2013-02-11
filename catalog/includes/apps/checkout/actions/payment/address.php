<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_checkout_action_payment_address {
    public static function execute(app $app) {
      global $breadcrumb, $addresses_count, $process;

      $app->setContentFile('payment_address.php');

      $breadcrumb->add(NAVBAR_TITLE_PAYMENT_ADDRESS, tep_href_link('checkout', 'payment&address', 'SSL'));

      $addresses_count = tep_count_customer_address_book_entries();

      $process = false;
    }
  }
?>
