<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_products_action_notify_delete {
    public static function execute(app $app) {
      global $OSCOM_PDO;

      $Qcheck = $OSCOM_PDO->prepare('select products_id from :table_products_notifications where customers_id = :customers_id and products_id = :products_id');
      $Qcheck->bindInt(':customers_id', $_SESSION['customer_id']);
      $Qcheck->bindInt(':products_id', osc_get_prid($_GET['id']));
      $Qcheck->execute();

      if ( $Qcheck->fetch() !== false ) {
        $OSCOM_PDO->delete('products_notifications', array('customers_id' => $_SESSION['customer_id'], 'products_id' => osc_get_prid($_GET['id'])));
      }

      osc_redirect(osc_href_link('products', 'id=' . $_GET['id']));
    }
  }
?>
