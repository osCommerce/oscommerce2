<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_password_change {
    public static function execute(app $app) {
      global $OSCOM_NavigationHistory, $OSCOM_Breadcrumb;

      if ( !isset($_SESSION['customer_id']) ) {
        $OSCOM_NavigationHistory->setSnapshot();

        osc_redirect(osc_href_link('account', 'login', 'SSL'));
      }

      $app->setContentFile('password_change.php');

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE_PASSWORD, osc_href_link('account', 'password&change', 'SSL'));
    }
  }
?>
