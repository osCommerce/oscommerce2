<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_address_book_new {
    public static function execute(app $app) {
      global $messageStack, $breadcrumb;

      $app->setContentFile('address_book_process.php');

      if (tep_count_customer_address_book_entries() >= MAX_ADDRESS_BOOK_ENTRIES) {
        $messageStack->add_session('addressbook', ERROR_ADDRESS_BOOK_FULL);

        tep_redirect(tep_href_link('account', 'address_book', 'SSL'));
      }

      $breadcrumb->add(NAVBAR_TITLE_ADDRESS_BOOK_NEW, tep_href_link('account', 'address_book&new', 'SSL'));
    }
  }
?>
