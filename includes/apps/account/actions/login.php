<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

 class app_account_action_login {
   public static function execute(app $app) {
      global $session_started, $breadcrumb;

// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled (or the session has not started)
      if ( $session_started === false ) {
        osc_redirect(osc_href_link('info', 'cookie_usage'));
      }

      $app->setContentFile('login.php');

      $breadcrumb->add(NAVBAR_TITLE_LOGIN, osc_href_link('account', 'login', 'SSL'));
    }
  }
?>
