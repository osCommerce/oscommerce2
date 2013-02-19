<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_password {
    public static function execute(app $app) {
      global $breadcrumb;

      if ( !isset($_SESSION['customer_id']) ) {
        $_SESSION['navigation']->set_snapshot();

        tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
      }

      $app->setContentFile('password.php');

      $breadcrumb->add(NAVBAR_TITLE_PASSWORD, tep_href_link('account', 'password', 'SSL'));
    }
  }
?>
