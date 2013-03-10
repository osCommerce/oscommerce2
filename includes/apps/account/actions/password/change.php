<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_password_change {
    public static function execute(app $app) {
      global $OSCOM_Customer, $OSCOM_NavigationHistory, $OSCOM_Breadcrumb;

      if ( !$OSCOM_Customer->isLoggedOn() ) {
        $OSCOM_NavigationHistory->setSnapshot();

        osc_redirect(osc_href_link('account', 'login', 'SSL'));
      }

      $app->setContentFile('password_change.php');

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE_PASSWORD, osc_href_link('account', 'password&change', 'SSL'));
    }
  }
?>
