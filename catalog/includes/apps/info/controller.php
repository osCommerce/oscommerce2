<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_info extends app {
    public function __construct() {
      global $breadcrumb;

      $breadcrumb->add(NAVBAR_TITLE, tep_href_link('info'));
    }
  }
?>
