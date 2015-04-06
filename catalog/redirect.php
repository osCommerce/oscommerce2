<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;

  require('includes/application_top.php');

  switch ($_GET['action']) {
    case 'banner':
      $Qbanner = $OSCOM_Db->prepare('select banners_url from :table_banners where banners_id = :goto');
      $Qbanner->bindInt(':goto', $_GET['goto']);
      $Qbanner->execute();
      if ( $Qbanner->rowCount() > 0 ) {
        tep_update_banner_click_count($_GET['goto']);

        tep_redirect($Qbanner->value('banners_url'));
      }
      break;

    case 'url':
      if (isset($_GET['goto']) && tep_not_null($_GET['goto'])) {
        $Qcheck = $OSCOM_Db->prepare('select products_url from :table_products_description where products_url = :goto limit 1');
        $Qcheck->bindValue(':goto', HTML::sanitize($_GET['goto']));
        $Qcheck->execute();
        if ( $Qcheck->rowCount() > 0 ) {
          tep_redirect('http://' . $_GET['goto']);
        }
      }
      break;

    case 'manufacturer':
      if (isset($_GET['manufacturers_id']) && tep_not_null($_GET['manufacturers_id'])) {
        $Qmanufacturer = $OSCOM_Db->prepare('select manufacturers_url from :table_manufacturers_info where manufacturers_id = :manufacturers_id and languages_id = :languages_id');
        $Qmanufacturer->bindInt(':manufacturers_id', $_GET['manufacturers_id']);
        $Qmanufacturer->bindInt(':languages_id', $_SESSION['languages_id']);
        $Qmanufacturer->execute();
        
        if ( $Qmanufacturer->rowCount() > 0 ) {
// url exists in selected language

          if ( $Qmanufacturer->value('manufacturers_url') ) {
            $OSCOM_Db->save(':table_manufacturers_info', array('url_clicked' => 'url_clicked'+1, 'date_last_click' => 'now()'), array('manufacturers_id' => (int)$_GET['manufacturers_id'], 'languages_id' => (int)$_SESSION['languages_id']));

            tep_redirect($Qmanufacturer->value('manufacturers_url'));
          }
        } else {
// no url exists for the selected language, lets use the default language then
          $Qmanufacturer = $OSCOM_Db->prepare('select mi.languages_id, mi.manufacturers_url from manufacturers_info mi, languages l where mi.manufacturers_id = :manufacturers_id and mi.languages_id = l.languages_id and l.code = :default_language');
          $Qmanufacturer->bindInt(':manufacturers_id', $_GET['manufacturers_id']);
          $Qmanufacturer->bindValue(':default_language', DEFAULT_LANGUAGE);
          $Qmanufacturer->execute();

          if ( $Qmanufacturer->rowCount() > 0 ) {
            $manufacturer = tep_db_fetch_array($manufacturer_query);

            if ( $Qmanufacturer->value('manufacturers_url') ) {
              $OSCOM_Db->save(':table_manufacturers_info', array('url_clicked' => 'url_clicked'+1, 'date_last_click' => 'now()'), array('manufacturers_id' => (int)$_GET['manufacturers_id'], 'languages_id' => (int)$manufacturer['languages_id']));

              tep_redirect($Qmanufacturer->value('manufacturers_url'));
            }
          }
        }
      }
      break;
  }

  tep_redirect(tep_href_link('index.php'));
?>
