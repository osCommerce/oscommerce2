<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class ht_index_manufacturer_title {
    function parse() {
      global $HTTP_GET_VARS, $oscTemplate, $manufacturers, $languages_id;

      if (isset($HTTP_GET_VARS['manufacturers_id']) && is_numeric($HTTP_GET_VARS['manufacturers_id'])) {
// $manufacturers is set in application_top.php to add the manufacturer to the breadcrumb
        if (isset($manufacturers) && (sizeof($manufacturers) == 1) && isset($manufacturers['manufacturers_name'])) {
          $oscTemplate->setTitle($manufacturers['manufacturers_name'] . ', ' . $oscTemplate->getTitle());
        } else {
// $manufacturers is not set so a database query is needed
          $manufacturers_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$HTTP_GET_VARS['manufacturers_id'] . "'");
          if (tep_db_num_rows($manufacturers_query)) {
            $manufacturers = tep_db_fetch_array($manufacturers_query);

            $oscTemplate->setTitle($manufacturers['manufacturers_name'] . ', ' . $oscTemplate->getTitle());
          }
        }
      }
    }
  }
?>
