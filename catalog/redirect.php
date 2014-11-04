<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  switch ($_GET['action']) {
    case 'banner':
      $banner_query = osc_db_query("select banners_url from " . TABLE_BANNERS . " where banners_id = '" . (int)$_GET['goto'] . "'");
      if (osc_db_num_rows($banner_query)) {
        $banner = osc_db_fetch_array($banner_query);
        osc_update_banner_click_count($_GET['goto']);

        osc_redirect($banner['banners_url']);
      }
      break;

    case 'url':
      if (isset($_GET['goto']) && osc_not_null($_GET['goto'])) {
        $check_query = osc_db_query("select products_url from " . TABLE_PRODUCTS_DESCRIPTION . " where products_url = '" . osc_db_input($_GET['goto']) . "' limit 1");
        if (osc_db_num_rows($check_query)) {
          osc_redirect('http://' . $_GET['goto']);
        }
      }
      break;

    case 'manufacturer':
      if (isset($_GET['manufacturers_id']) && osc_not_null($_GET['manufacturers_id'])) {
        $manufacturer_query = osc_db_query("select manufacturers_url from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "' and languages_id = '" . (int)$_SESSION['languages_id'] . "'");
        if (osc_db_num_rows($manufacturer_query)) {
// url exists in selected language
          $manufacturer = osc_db_fetch_array($manufacturer_query);

          if (osc_not_null($manufacturer['manufacturers_url'])) {
            osc_db_query("update " . TABLE_MANUFACTURERS_INFO . " set url_clicked = url_clicked+1, date_last_click = now() where manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "' and languages_id = '" . (int)$_SESSION['languages_id'] . "'");

            osc_redirect($manufacturer['manufacturers_url']);
          }
        } else {
// no url exists for the selected language, lets use the default language then
          $manufacturer_query = osc_db_query("select mi.languages_id, mi.manufacturers_url from " . TABLE_MANUFACTURERS_INFO . " mi, " . TABLE_LANGUAGES . " l where mi.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "' and mi.languages_id = l.languages_id and l.code = '" . DEFAULT_LANGUAGE . "'");
          if (osc_db_num_rows($manufacturer_query)) {
            $manufacturer = osc_db_fetch_array($manufacturer_query);

            if (osc_not_null($manufacturer['manufacturers_url'])) {
              osc_db_query("update " . TABLE_MANUFACTURERS_INFO . " set url_clicked = url_clicked+1, date_last_click = now() where manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "' and languages_id = '" . (int)$manufacturer['languages_id'] . "'");

              osc_redirect($manufacturer['manufacturers_url']);
            }
          }
        }
      }
      break;
  }

  osc_redirect(tep_href_link(FILENAME_DEFAULT));
?>
