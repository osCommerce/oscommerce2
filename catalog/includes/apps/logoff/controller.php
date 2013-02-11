<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_logoff extends app {
    public function __construct() {
      global $breadcrumb;

      $breadcrumb->add(NAVBAR_TITLE);

      unset($_SESSION['customer_id']);
      unset($_SESSION['customer_default_address_id']);
      unset($_SESSION['customer_first_name']);
      unset($_SESSION['customer_country_id']);
      unset($_SESSION['customer_zone_id']);
      unset($_SESSION['sendto']);
      unset($_SESSION['billto']);
      unset($_SESSION['shipping']);
      unset($_SESSION['payment']);
      unset($_SESSION['comments']);

      $_SESSION['cart']->reset();

      if ( SESSION_RECREATE == 'True' ) {
        tep_session_recreate();
      }
    }
  }
?>
