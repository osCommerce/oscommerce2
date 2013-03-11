<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_notifications_process {
    public static function execute(app $app) {
      global $OSCOM_Customer, $OSCOM_MessageStack, $OSCOM_PDO, $global;

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        if ( isset($_POST['product_global']) && is_numeric($_POST['product_global']) ) {
          $product_global = trim($_POST['product_global']);
        } else {
          $product_global = '0';
        }

        (array)$products = $_POST['products'];

        if ( $product_global != $global['global_product_notifications'] ) {
          $product_global = (($global['global_product_notifications'] == '1') ? '0' : '1');

          $OSCOM_PDO->perform('customers_info', array('global_product_notifications' => (int)$product_global), array('customers_info_id' => (int)$OSCOM_Customer->getID()));
        } elseif ( count($products) > 0 ) {
          $products_parsed = array();

          foreach ( $products as $p ) {
            if ( is_numeric($p) ) {
              $products_parsed[] = $p;
            }
          }

          if ( count($products_parsed) > 0 ) {
            $Qcheck = $OSCOM_PDO->prepare('select products_id from :table_products_notifications where customers_id = :customers_id and products_id not in (:products_id) limit 1');
            $Qcheck->bindInt(':customers_id', $OSCOM_Customer->getID());
            $Qcheck->bindValue(':products_id', implode(', ', $products_parsed));
            $Qcheck->execute();

            if ( $Qcheck->fetch() !== false ) {
              $Qdelete = $OSCOM_PDO->prepare('delete from :table_products_notifications where customers_id = :customers_id and products_id not in (:products_id)');
              $Qdelete->bindInt(':customers_id', $OSCOM_Customer->getID());
              $Qdelete->bindValue(':products_id', implode(', ', $products_parsed));
              $Qdelete->execute();
            }
          }
        } else {
          $Qcheck = $OSCOM_PDO->prepare('select customers_id from :table_products_notifications where customers_id = :customers_id limit 1');
          $Qcheck->bindInt(':customers_id', $OSCOM_Customer->getID());
          $Qcheck->execute();

          if ( $Qcheck->fetch() !== false ) {
            $Qdelete = $OSCOM_PDO->prepare('delete from :table_products_notifications where customers_id = :customers_id');
            $Qdelete->bindInt(':customers_id', $OSCOM_Customer->getID());
            $Qdelete->execute();
          }
        }

        $OSCOM_MessageStack->addSuccess('account', SUCCESS_NOTIFICATIONS_UPDATED);

        osc_redirect(osc_href_link('account', '', 'SSL'));
      }
    }
  }
?>
