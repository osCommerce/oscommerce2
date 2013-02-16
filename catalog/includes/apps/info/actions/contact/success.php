<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_info_action_contact_success {
    public static function execute(app $app) {
      $app->setContentFile('contact_success.php');
    }
  }
?>
