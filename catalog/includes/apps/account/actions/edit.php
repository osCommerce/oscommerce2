<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_edit {
    public static function execute(app $app) {
      global $OSCOM_PDO, $account, $breadcrumb;

      if ( !isset($_SESSION['customer_id']) ) {
        $_SESSION['navigation']->set_snapshot();

        tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
      }

      $app->setContentFile('edit.php');

      $Qaccount = $OSCOM_PDO->prepare('select customers_gender, customers_firstname, customers_lastname, customers_dob, customers_email_address, customers_telephone, customers_fax from :table_customers where customers_id = :customers_id');
      $Qaccount->bindInt(':customers_id', $_SESSION['customer_id']);
      $Qaccount->execute();

      $account = $Qaccount->fetch();

      $breadcrumb->add(NAVBAR_TITLE_EDIT, tep_href_link('account', 'edit', 'SSL'));
    }
  }
?>
