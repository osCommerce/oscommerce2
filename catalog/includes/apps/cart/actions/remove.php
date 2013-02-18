<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_cart_action_remove {
    public static function execute(app $app) {
      if ( isset($_GET['formid']) && ($_GET['formid'] == md5($_SESSION['sessiontoken'])) ) {
        if ( isset($_GET['id']) ) {
          $_SESSION['cart']->remove($_GET['id']);
        }
      }

      tep_redirect(tep_href_link('cart'));
    }
  }
?>
