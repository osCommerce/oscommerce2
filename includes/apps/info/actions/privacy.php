<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_info_action_privacy {
    public static function execute(app $app) {
      global $OSCOM_Breadcrumb;

      $app->setContentFile('privacy.php');

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE_PRIVACY, osc_href_link('info', 'privacy'));
    }
  }
?>
