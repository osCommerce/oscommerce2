<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_address_book_delete {
    public static function execute(app $app) {
      global $OSCOM_Breadcrumb, $OSCOM_Customer, $OSCOM_MessageStack, $OSCOM_PDO;

      $exists = false;

      if ( isset($_GET['id']) && is_numeric($_GET['id']) ) {
        if ( $_GET['id'] == $OSCOM_Customer->get('default_address_id') ) {
          $OSCOM_MessageStack->addWarning('addressbook', WARNING_PRIMARY_ADDRESS_DELETION);

          osc_redirect(osc_href_link('account', 'address_book', 'SSL'));
        }

        $Qcheck = $OSCOM_PDO->prepare('select address_book_id from :table_address_book where address_book_id = :address_book_id and customers_id = :customers_id');
        $Qcheck->bindInt(':address_book_id', $_GET['id']);
        $Qcheck->bindInt(':customers_id', $OSCOM_Customer->getID());
        $Qcheck->execute();

        if ( $Qcheck->fetch() !== false ) {
          $exists = true;
        }
      }

      if ( $exists === false ) {
        $OSCOM_MessageStack->addError('addressbook', ERROR_NONEXISTING_ADDRESS_BOOK_ENTRY);

        osc_redirect(osc_href_link('account', 'address_book', 'SSL'));
      }

      $app->setContentFile('address_book_delete.php');

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE_ADDRESS_BOOK_DELETE, osc_href_link('account', 'address_book&delete&id=' . $_GET['id'], 'SSL'));
    }
  }
?>
