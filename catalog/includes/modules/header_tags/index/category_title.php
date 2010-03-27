<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class ht_index_category_title {
    function parse() {
      global $oscTemplate, $categories, $current_category_id, $languages_id;

// $categories is set in application_top.php to add the category to the breadcrumb
      if (isset($categories) && (sizeof($categories) == 1) && isset($categories['categories_name'])) {
        $oscTemplate->setTitle($categories['categories_name'] . ', ' . $oscTemplate->getTitle());
      } else {
// $categories is not set so a database query is needed
        if ($current_category_id > 0) {
          $categories_query = tep_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$current_category_id . "' and language_id = '" . (int)$languages_id . "' limit 1");
          if (tep_db_num_rows($categories_query) > 0) {
            $categories = tep_db_fetch_array($categories_query);

            $oscTemplate->setTitle($categories['categories_name'] . ', ' . $oscTemplate->getTitle());
          }
        }
      }
    }
  }
?>
