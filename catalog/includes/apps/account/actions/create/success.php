<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_create_success {
    public static function execute(app $app) {
      global $breadcrumb, $origin_href;

      $app->setContentFile('create_success.php');

      $breadcrumb->add(NAVBAR_TITLE_CREATE_SUCCESS);

      if ( sizeof($_SESSION['navigation']->snapshot) > 0 ) {
        $origin_href = osc_href_link($_SESSION['navigation']->snapshot['page'], osc_array_to_string($_SESSION['navigation']->snapshot['get'], array(session_name())), $_SESSION['navigation']->snapshot['mode']);
        $_SESSION['navigation']->clear_snapshot();
      } else {
        $origin_href = osc_href_link();
      }
    }
  }
?>
