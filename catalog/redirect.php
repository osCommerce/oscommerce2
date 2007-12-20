<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  switch ($HTTP_GET_VARS['action']) {
    case 'banner':
      $banner_query = tep_db_query("select banners_url from " . TABLE_BANNERS . " where banners_id = '" . (int)$HTTP_GET_VARS['goto'] . "'");
      if (tep_db_num_rows($banner_query)) {
        $banner = tep_db_fetch_array($banner_query);
        tep_update_banner_click_count($HTTP_GET_VARS['goto']);

        tep_redirect($banner['banners_url']);
      }
      break;

    case 'url':
      if (isset($HTTP_GET_VARS['goto']) && tep_not_null($HTTP_GET_VARS['goto'])) {
        $check_query = tep_db_query("select products_url from " . TABLE_PRODUCTS_DESCRIPTION . " where products_url = '" . tep_db_input($HTTP_GET_VARS['goto']) . "' limit 1");
        if (tep_db_num_rows($check_query)) {
          tep_redirect('http://' . $HTTP_GET_VARS['goto']);
        }
      }
      break;

    case 'manufacturer':
      if (isset($HTTP_GET_VARS['manufacturers_id']) && tep_not_null($HTTP_GET_VARS['manufacturers_id'])) {
        $manufacturer_query = tep_db_query("select manufacturers_url from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' and languages_id = '" . (int)$languages_id . "'");
        if (tep_db_num_rows($manufacturer_query)) {
// url exists in selected language
          $manufacturer = tep_db_fetch_array($manufacturer_query);

          if (tep_not_null($manufacturer['manufacturers_url'])) {
            tep_db_query("update " . TABLE_MANUFACTURERS_INFO . " set url_clicked = url_clicked+1, date_last_click = now() where manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' and languages_id = '" . (int)$languages_id . "'");

            tep_redirect($manufacturer['manufacturers_url']);
          }
        } else {
// no url exists for the selected language, lets use the default language then
          $manufacturer_query = tep_db_query("select mi.languages_id, mi.manufacturers_url from " . TABLE_MANUFACTURERS_INFO . " mi, " . TABLE_LANGUAGES . " l where mi.manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' and mi.languages_id = l.languages_id and l.code = '" . DEFAULT_LANGUAGE . "'");
          if (tep_db_num_rows($manufacturer_query)) {
            $manufacturer = tep_db_fetch_array($manufacturer_query);

            if (tep_not_null($manufacturer['manufacturers_url'])) {
              tep_db_query("update " . TABLE_MANUFACTURERS_INFO . " set url_clicked = url_clicked+1, date_last_click = now() where manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "' and languages_id = '" . (int)$manufacturer['languages_id'] . "'");

              tep_redirect($manufacturer['manufacturers_url']);
            }
          }
        }
      }
      break;
  }

  tep_redirect(tep_href_link(FILENAME_DEFAULT));
?>
