<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_products_action_reviews_new {
    public static function execute(app $app) {
      global $OSCOM_PDO, $Qcustomer;

      if ( !isset($_SESSION['customer_id']) ) {
        $_SESSION['navigation']->set_snapshot();
        tep_redirect(tep_href_link('account', 'login', 'SSL'));
      }

      $app->setContentFile('reviews_new.php');

      $Qcustomer = $OSCOM_PDO->prepare('select customers_firstname, customers_lastname from :table_customers where customers_id = :customers_id');
      $Qcustomer->bindInt(':customers_id', $_SESSION['customer_id']);
      $Qcustomer->execute();
    }
  }
?>
