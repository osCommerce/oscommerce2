<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_info_action_shipping {
    public static function execute(app $app) {
      global $OSCOM_Breadcrumb;

      $app->setContentFile('shipping.php');

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE_SHIPPING, osc_href_link('info', 'shipping'));
    }
  }
?>
