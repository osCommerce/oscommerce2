<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

 require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'order.php');

  class app_account_action_orders_info {
    public static function execute(app $app) {
      global $OSCOM_PDO, $order, $breadcrumb;

      if ( !isset($_GET['id']) || !is_numeric($_GET['id']) ) {
        osc_redirect(osc_href_link('account', 'orders', 'SSL'));
      }

      $Qcheck = $OSCOM_PDO->prepare('select o.customers_id from :table_orders o, :table_orders_status s where o.orders_id = :orders_id and o.orders_status = s.orders_status_id and s.language_id = :language_id and s.public_flag = "1"');
      $Qcheck->bindInt(':orders_id', $_GET['id']);
      $Qcheck->bindInt(':language_id', $_SESSION['languages_id']);
      $Qcheck->execute();

      if ( ($Qcheck->fetch() === false) || ($Qcheck->value('customers_id') != $_SESSION['customer_id']) ) {
        osc_redirect(osc_href_link('account', 'orders', 'SSL'));
      }

      $order = new order($_GET['id']);

      $app->setContentFile('orders_info.php');

      $breadcrumb->add(sprintf(NAVBAR_TITLE_ORDERS_INFO, $_GET['id']), osc_href_link('account', 'orders&info&id=' . $_GET['id'], 'SSL'));
    }
  }
?>
