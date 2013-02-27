<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_checkout_action_shipping_address {
    public static function execute(app $app) {
      global $OSCOM_Breadcrumb, $addresses_count, $process;

      $app->setContentFile('shipping_address.php');

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE_SHIPPING_ADDRESS, osc_href_link('checkout', 'shipping&address', 'SSL'));

      $addresses_count = osc_count_customer_address_book_entries();

      $process = false;
    }
  }
?>
