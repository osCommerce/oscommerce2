<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_account_action_create {
    public static function execute(app $app) {
      global $process, $breadcrumb;

      $process = false;

      $app->setContentFile('create.php');

      $breadcrumb->add(NAVBAR_TITLE_CREATE, osc_href_link('account', 'create', 'SSL'));
    }
  }
?>
