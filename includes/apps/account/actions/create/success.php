<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_create_success {
    public static function execute(app $app) {
      global $OSCOM_NavigationHistory, $breadcrumb, $origin_href;

      $app->setContentFile('create_success.php');

      $breadcrumb->add(NAVBAR_TITLE_CREATE_SUCCESS);

      if ( $OSCOM_NavigationHistory->hasSnapshot() ) {
        $origin_href = $OSCOM_NavigationHistory->getSnapshotURL(true);

        $OSCOM_NavigationHistory->resetSnapshot();
      } else {
        $origin_href = osc_href_link();
      }
    }
  }
?>
