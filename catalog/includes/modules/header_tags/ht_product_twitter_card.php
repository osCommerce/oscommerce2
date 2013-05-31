<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
  
  For more great code, get yourself over to
  www.clubosc.com
  
*/

  class ht_product_twitter_card {
    var $code = 'ht_product_twitter_card';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_product_twitter_card() {
      $this->title = MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_TITLE;
      $this->description = MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate, $_GET, $languages_id;
      global $currencies;

      if (basename($PHP_SELF) == FILENAME_PRODUCT_INFO) {
        $twitter_card = NULL;
        if (isset($_GET['products_id'])) {
          $product_info_query = tep_db_query("select p.products_id, pd.products_name, SUBSTRING_INDEX(pd.products_description, ' ', 50) as products_description, p.products_image, p.products_price, p.products_quantity from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$_GET['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
          $product_info = tep_db_fetch_array($product_info_query);

          $twitter_card = '<meta name="twitter:card" content="product">' . "\n";
          if (strlen(MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_SITE_ID) > 0) {
            $twitter_card .= '<meta name="twitter:site" content="' . MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_SITE_ID . '">' . "\n";
          }
          if (strlen(MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_USER_ID) > 0) {
            $twitter_card .= '<meta name="twitter:creator" content="' . MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_USER_ID . '">' . "\n";
          }
          
          $product_twitter_description = strip_tags(substr($product_info['products_description'], 0, 195));
          
          $twitter_card .= '<meta name="twitter:title" content="' . tep_output_string_protected($product_info['products_name']) . '">' . "\n" .
                           '<meta name="twitter:description" content="' . tep_output_string_protected($product_twitter_description) . '...">' . "\n" .
                           '<meta name="twitter:image:src" content="' . HTTP_SERVER . DIR_WS_HTTP_CATALOG . DIR_WS_IMAGES . $product_info['products_image'] . '">' . "\n" .
                           '<meta name="twitter:data1" content="' . MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_TEXT_PRICE . '">' . "\n" .
                           '<meta name="twitter:label1" content="' . $currencies->format(tep_output_string_protected($product_info['products_price'])) . '">' . "\n" .
                           '<meta name="twitter:data2" content="' . MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_TEXT_STOCK . '">' . "\n" .
                           '<meta name="twitter:label2" content="' . tep_output_string_protected($product_info['products_quantity']) . '">' . "\n";

          $oscTemplate->addBlock($twitter_card, $this->group);
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Product Twitter Card Module', 'MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_STATUS', 'True', 'Do you want to allow product Twitter Card Tags to be added to your page?  Note that your product thumbnails MUST be at least 160px by 160px.', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Twitter Author @username', 'MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_USER_ID', '', 'Your @username at Twitter', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Twitter Site @username', 'MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_SITE_ID', '', 'Your shops @username at Twitter (or leave blank if it is the same as your @username above).', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_STATUS', 'MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_USER_ID', 'MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_SITE_ID', 'MODULE_HEADER_TAGS_PRODUCT_TWITTER_CARD_SORT_ORDER');
    }
  }
?>
