<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_index_action_redirect_manufacturer {
    public static function execute(app $app) {
      global $OSCOM_PDO;

      if ( is_numeric($_GET['manufacturer']) ) {
        $Qmanufacturer = $OSCOM_PDO->prepare('select manufacturers_url from :table:manufacturers_info where manufacturers_id = :manufacturers_id and languages_id = :languages_id');
        $Qmanufacturer->bindInt(':manufacturers_id', $_GET['manufacturer']);
        $Qmanufacturer->bindInt(':languages_id', $_SESSION['languages_id']);
        $Qmanufacturer->execute();

        if ( $Qmanufacturer->fetch() !== false ) {
// url exists in selected language
          if (tep_not_null($Qmanufacturer->value('manufacturers_url'))) {
            $Qupdate = $OSCOM_PDO->prepare('update :table_manufactuers_info set url_clicked = url_clicked+1, date_last_click = now() where manufacturers_id = :manufacturers_id and languages_id = :languages_id');
            $Qupdate->bindInt(':manufacturers_id', $_GET['manufacturer']);
            $Qupdate->bindInt(':languages_id', $_SESSION['languages_id']);
            $Qupdate->execute();

            tep_redirect($Qmanufacturer->value('manufacturers_url'));
          }
        } else {
// no url exists for the selected language, lets use the default language then
          $Qmanufacturer = $OSCOM_PDO->prepare('select mi.languages_id, mi.manufacturers_url from :table_manufacturers_info mi, :table_languages l where mi.manufacturers_id = :manufacturers_id and mi.languages_id = l.languages_id and l.code = :code');
          $Qmanufacturer->bindInt(':manufacturers_id', $_GET['manufacturer']);
          $Qmanufacturer->bindValue(':code', DEFAULT_LANGUAGE);
          $Qmanufacturer->execute();

          if ( $Qmanufacturer->fetch() !== false ) {
            if (tep_not_null($Qmanufacturer->value('manufacturers_url'))) {
              $Qupdate = $OSCOM_PDO->prepare('update :table_manufactuers_info set url_clicked = url_clicked+1, date_last_click = now() where manufacturers_id = :manufacturers_id and languages_id = :languages_id');
              $Qupdate->bindInt(':manufacturers_id', $_GET['manufacturer']);
              $Qupdate->bindInt(':languages_id', $Qmanufacturer->valueInt(':languages_id'));
              $Qupdate->execute();

              tep_redirect($Qmanufacturer->value('manufacturers_url'));
            }
          }
        }
      }
    }
  }
?>
