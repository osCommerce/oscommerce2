<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_info_action_ssl_check {
    public static function execute(app $app) {
      global $OSCOM_Breadcrumb;

      $app->setContentFile('ssl_check.php');

      $OSCOM_Breadcrumb->add(NAVBAR_TITLE_SSL_CHECK, osc_href_link('info', 'ssl_check'));
    }
  }
?>
