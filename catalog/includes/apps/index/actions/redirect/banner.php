<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_index_action_redirect_banner {
    public static function execute(app $app) {
      global $OSCOM_PDO;

      if ( is_numeric($_GET['banner']) ) {
        $Qbanner = $OSCOM_PDO->prepare('select banners_url from :table_banners where banners_id = :banners_id');
        $Qbanner->bindInt(':banners_id', $_GET['banner']);
        $Qbanner->execute();

        if ( $Qbanner->fetch() !== false ) {
          tep_update_banner_click_count($_GET['banner']);

          tep_redirect($Qbanner->value('banners_url'));
        }
      }
    }
  }
?>
