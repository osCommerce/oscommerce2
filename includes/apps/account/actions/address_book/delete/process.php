<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_address_book_delete_process {
    public static function execute(app $app) {
      global $OSCOM_PDO, $messageStack;

      if ( isset($_GET['formid']) && ($_GET['formid'] == md5($_SESSION['sessiontoken'])) ) {
        $Qdelete = $OSCOM_PDO->prepare('delete from :table_address_book where address_book_id = :address_book_id and customers_id = :customers_id');
        $Qdelete->bindInt(':address_book_id', $_GET['id']);
        $Qdelete->bindInt(':customers_id', $_SESSION['customer_id']);
        $Qdelete->execute();

        $messageStack->add_session('addressbook', SUCCESS_ADDRESS_BOOK_ENTRY_DELETED, 'success');

        osc_redirect(osc_href_link('account', 'address_book', 'SSL'));
      }
    }
  }
?>
