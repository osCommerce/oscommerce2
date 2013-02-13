<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_address_book_edit {
    public static function execute(app $app) {
      global $OSCOM_PDO, $entry, $messageStack, $breadcrumb;

      $exists = false;

      if ( isset($_GET['id']) && is_numeric($_GET['id']) ) {
        $Qab = $OSCOM_PDO->prepare('select entry_gender, entry_company, entry_firstname, entry_lastname, entry_street_address, entry_suburb, entry_postcode, entry_city, entry_state, entry_zone_id, entry_country_id from :table_address_book where address_book_id = :address_book_id and customers_id = :customers_id');
        $Qab->bindInt(':address_book_id', $_GET['id']);
        $Qab->bindInt(':customers_id', $_SESSION['customer_id']);
        $Qab->execute();

        $entry = $Qab->fetch();

        if ( $entry !== false ) {
          $exists = true;
        }
      }

      if ( $exists === false ) {
        $messageStack->add_session('addressbook', ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY);

        tep_redirect(tep_href_link('account', 'address_book', 'SSL'));
      }

      $app->setContentFile('address_book_process.php');

      $breadcrumb->add(NAVBAR_TITLE_ADDRESS_BOOK_EDIT, tep_href_link('account', 'address_book&edit&id=' . $_GET['id'], 'SSL'));
    }
  }
?>
