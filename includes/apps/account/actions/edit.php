<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_edit {
    public static function execute(app $app) {
      global $OSCOM_NavigationHistory, $OSCOM_PDO, $account, $OSCOM_Breadcrumb;

      if ( !isset($_SESSION['customer_id']) ) {
        $OSCOM_NavigationHistory->setSnapshot();

        osc_redirect(osc_href_link('account', 'login', 'SSL'));
      }

      $app->setContentFile('edit.php');

      $Qaccount = $OSCOM_PDO->prepare('select customers_gender, customers_firstname, customers_lastname, customers_dob, customers_email_address, customers_telephone, customers_fax from :table_customers where customers_id = :customers_id');
      $Qaccount->bindInt(':customers_id', $_SESSION['customer_id']);
      $Qaccount->execute();

      $account = $Qaccount->fetch();

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE_EDIT, osc_href_link('account', 'edit', 'SSL'));
    }
  }
?>
