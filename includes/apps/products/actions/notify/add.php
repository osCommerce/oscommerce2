<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_products_action_notify_add {
    public static function execute(app $app) {
      global $OSCOM_Customer, $OSCOM_PDO;

      $Qcheck = $OSCOM_PDO->prepare('select products_id from :table_products_notifications where customers_id = :customers_id and products_id = :products_id');
      $Qcheck->bindInt(':customers_id', $OSCOM_Customer->getID());
      $Qcheck->bindInt(':products_id', osc_get_prid($_GET['id']));
      $Qcheck->execute();

      if ( $Qcheck->fetch() === false ) {
        $OSCOM_PDO->perform('products_notifications', array('products_id' => osc_get_prid($_GET['id']), 'customers_id' => $OSCOM_Customer->getID(), 'date_added' => 'now()'));
      }

      osc_redirect(osc_href_link('products', 'id=' . $_GET['id']));
    }
  }
?>
