<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_create_account_action_success {
    public static function execute(app $app) {
      global $breadcrumb, $origin_href;

      $app->setContentFile('success.php');

      $breadcrumb->add(SUCCESS_NAVBAR_TITLE_1);
      $breadcrumb->add(SUCCESS_NAVBAR_TITLE_2);

      if ( sizeof($_SESSION['navigation']->snapshot) > 0 ) {
        $origin_href = tep_href_link($_SESSION['navigation']->snapshot['page'], tep_array_to_string($_SESSION['navigation']->snapshot['get'], array(session_name())), $_SESSION['navigation']->snapshot['mode']);
        $_SESSION['navigation']->clear_snapshot();
      } else {
        $origin_href = tep_href_link(FILENAME_DEFAULT);
      }
    }
  }
?>
