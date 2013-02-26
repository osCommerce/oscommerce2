<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_notifications {
    public static function execute(app $app) {
      global $OSCOM_PDO, $global, $breadcrumb;

      if ( !isset($_SESSION['customer_id']) ) {
        $_SESSION['navigation']->set_snapshot();

        osc_redirect(osc_href_link('account', 'login', 'SSL'));
      }

      $app->setContentFile('notifications.php');

      $Qglobal = $OSCOM_PDO->prepare('select global_product_notifications from :table_customers_info where customers_info_id = :customers_info_id');
      $Qglobal->bindInt(':customers_info_id', $_SESSION['customer_id']);
      $Qglobal->execute();

      $global = $Qglobal->fetch();

      $breadcrumb->add(NAVBAR_TITLE_NOTIFICATIONS, osc_href_link('account', 'notifications', 'SSL'));
    }
  }
?>
