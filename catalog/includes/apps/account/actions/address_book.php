<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_address_book {
    public static function execute(app $app) {
      global $breadcrumb;

      $app->setContentFile('address_book.php');

      $breadcrumb->add(NAVBAR_TITLE_ADDRESS_BOOK, tep_href_link('account', 'address_book', 'SSL'));
    }
  }
?>
