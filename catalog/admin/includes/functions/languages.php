<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  function osc_get_languages_directory($code) {
    global $languages_id;

    $language_query = osc_db_query("select languages_id, directory from " . TABLE_LANGUAGES . " where code = '" . osc_db_input($code) . "'");
    if (osc_db_num_rows($language_query)) {
      $language = osc_db_fetch_array($language_query);
      $languages_id = $language['languages_id'];
      return $language['directory'];
    } else {
      return false;
    }
  }
?>