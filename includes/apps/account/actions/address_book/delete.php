<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_address_book_delete {
    public static function execute(app $app) {
      global $OSCOM_PDO, $messageStack, $OSCOM_Breadcrumb;

      $exists = false;

      if ( isset($_GET['id']) && is_numeric($_GET['id']) ) {
        if ( $_GET['id'] == $_SESSION['customer_default_address_id'] ) {
          $messageStack->add_session('addressbook', WARNING_PRIMARY_ADDRESS_DELETION, 'warning');

          osc_redirect(osc_href_link('account', 'address_book', 'SSL'));
        }

        $Qcheck = $OSCOM_PDO->prepare('select address_book_id from :table_address_book where address_book_id = :address_book_id and customers_id = :customers_id');
        $Qcheck->bindInt(':address_book_id', $_GET['id']);
        $Qcheck->bindInt(':customers_id', $_SESSION['customer_id']);
        $Qcheck->execute();

        if ( $Qcheck->fetch() !== false ) {
          $exists = true;
        }
      }

      if ( $exists === false ) {
        $messageStack->add_session('addressbook', ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY);

        osc_redirect(osc_href_link('account', 'address_book', 'SSL'));
      }

      $app->setContentFile('address_book_delete.php');

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE_ADDRESS_BOOK_DELETE, osc_href_link('account', 'address_book&delete&id=' . $_GET['id'], 'SSL'));
    }
  }
?>
