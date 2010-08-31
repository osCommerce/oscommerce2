<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class ht_productInfo_title {
    function parse() {
      global $oscTemplate, $HTTP_GET_VARS, $languages_id, $product_check;

      if ($product_check['total'] > 0) {
        $product_info_query = tep_db_query("select pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
        $product_info = tep_db_fetch_array($product_info_query);

        $oscTemplate->setTitle($product_info['products_name'] . ', ' . $oscTemplate->getTitle());
      }
    }
  }
?>
