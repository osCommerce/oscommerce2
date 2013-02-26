<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_cart extends app {
    public function __construct() {
      global $payment_modules, $breadcrumb;

      if ( $_SESSION['cart']->count_contents() > 0 ) {
        include(DIR_FS_CATALOG . DIR_WS_CLASSES . 'payment.php');
        $payment_modules = new payment();
      }

      $breadcrumb->add(NAVBAR_TITLE, osc_href_link('cart'));

      if ( $_SESSION['cart']->count_contents() < 1 ) {
        $this->_content_file = 'empty.php';
      }
    }
  }
?>
