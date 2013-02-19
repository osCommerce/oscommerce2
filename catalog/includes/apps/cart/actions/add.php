<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_cart_action_add {
    public static function execute(app $app) {
      global $cPath;

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        if ( isset($_POST['products_id']) && is_numeric($_POST['products_id']) ) {
          $attributes = isset($_POST['id']) ? $_POST['id'] : '';

          $_SESSION['cart']->add_cart($_POST['products_id'], $_SESSION['cart']->get_quantity(tep_get_uprid($_POST['products_id'], $attributes))+1, $attributes);
        }
      }

      if ( DISPLAY_CART == 'true' ) {
        $goto =  'cart';
        $params = null;
      } else {
        $goto = 'products';
        $params = 'cPath=' . $cPath . '&id=' . $_POST['products_id'];
      }

      tep_redirect(tep_href_link($goto, $params));
    }
  }
?>
