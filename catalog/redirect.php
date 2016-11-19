<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\HTTP;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  switch ($_GET['action']) {
    case 'banner':
      $Qbanner = $OSCOM_Db->get('banners', 'banners_url', ['banners_id' => $_GET['goto']]);
      if ($Qbanner->fetch() !== false) {
        tep_update_banner_click_count($_GET['goto']);

        HTTP::redirect($Qbanner->value('banners_url'));
      }
      break;

    case 'url':
      if (isset($_GET['goto']) && tep_not_null($_GET['goto'])) {
        $Qcheck = $OSCOM_Db->get('products_description', 'products_url', ['products_url' => HTML::sanitize($_GET['goto'])], null, 1);
        if ($Qcheck->fetch() !== false) {
          HTTP::redirect('http://' . $Qcheck->value('products_url'));
        }
      }
      break;

    case 'manufacturer':
      if (isset($_GET['manufacturers_id']) && is_numeric($_GET['manufacturers_id'])) {
        $Qmanufacturer = $OSCOM_Db->get('manufacturers_info', 'manufacturers_url', ['manufacturers_id' => $_GET['manufacturers_id'], 'languages_id' => $OSCOM_Language->getId()]);

        if ($Qmanufacturer->fetch() !== false) {
// url exists in selected language
          if ( !empty($Qmanufacturer->value('manufacturers_url')) ) {
            $Qupdate = $OSCOM_Db->prepare('update :table_manufacturers_info set url_clicked = url_clicked+1, date_last_click = now() where manufacturers_id = :manufacturers_id and languages_id = :languages_id');
            $Qupdate->bindInt(':manufacturers_id', $_GET['manufacturers_id']);
            $Qupdate->bindInt(':languages_id', $OSCOM_Language->getId());
            $Qupdate->execute();

            HTTP::redirect($Qmanufacturer->value('manufacturers_url'));
          }
        } else {
// no url exists for the selected language, lets use the default language then
          $Qmanufacturer = $OSCOM_Db->prepare('select mi.languages_id, mi.manufacturers_url from manufacturers_info mi, languages l where mi.manufacturers_id = :manufacturers_id and mi.languages_id = l.languages_id and l.code = :default_language');
          $Qmanufacturer->bindInt(':manufacturers_id', $_GET['manufacturers_id']);
          $Qmanufacturer->bindValue(':default_language', DEFAULT_LANGUAGE);
          $Qmanufacturer->execute();

          if ($Qmanufacturer->fetch() !== false) {
            if ( !empty($Qmanufacturer->value('manufacturers_url')) ) {
              $Qupdate = $OSCOM_Db->prepare('update :table_manufacturers_info set url_clicked = url_clicked+1, date_last_click = now() where manufacturers_id = :manufacturers_id and languages_id = :languages_id');
              $Qupdate->bindInt(':manufacturers_id', $_GET['manufacturers_id']);
              $Qupdate->bindInt(':languages_id', $Qmanufacturer->valueInt('languages_id'));
              $Qupdate->execute();

              HTTP::redirect($Qmanufacturer->value('manufacturers_url'));
            }
          }
        }
      }
      break;
  }

  OSCOM::redirect('index.php');
?>
