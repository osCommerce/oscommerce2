<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_products_action_reviews_new {
    public static function execute(app $app) {
      global $OSCOM_Customer, $OSCOM_NavigationHistory, $OSCOM_PDO, $Qcustomer;

      if ( !$OSCOM_Customer->isLoggedOn() ) {
        $OSCOM_NavigationHistory->setSnapshot();

        osc_redirect(osc_href_link('account', 'login', 'SSL'));
      }

      $app->setContentFile('reviews_new.php');

      $Qcustomer = $OSCOM_PDO->prepare('select customers_firstname, customers_lastname from :table_customers where customers_id = :customers_id');
      $Qcustomer->bindInt(':customers_id', $OSCOM_Customer->getID());
      $Qcustomer->execute();
    }
  }
?>
