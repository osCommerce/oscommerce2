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

  class ht_google_adwords_conversion {
    var $code = 'ht_google_adwords_conversion';
    var $group = 'footer_scripts';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_google_adwords_conversion() {
      $this->title = MODULE_HEADER_TAGS_GOOGLE_ADWORDS_TITLE;
      $this->description = MODULE_HEADER_TAGS_GOOGLE_ADWORDS_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_GOOGLE_ADWORDS_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_GOOGLE_ADWORDS_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_GOOGLE_ADWORDS_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate;
      global $customer_id;
      
      if (MODULE_HEADER_TAGS_GOOGLE_ADWORDS_JS_PLACEMENT != 'Footer') {
        $this->group = 'header_tags';
      }

      if (basename($PHP_SELF) == FILENAME_CHECKOUT_SUCCESS) {
        $adwords_display = NULL;
        
        if ( tep_session_is_registered('customer_id') ) {
          $order_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where customers_id = '" . (int)$customer_id . "' order by date_purchased desc limit 1");

          if (tep_db_num_rows($order_query) == 1) {
            $order = tep_db_fetch_array($order_query);

            $order_subtotal_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order['orders_id'] . "' and class='ot_subtotal'");
            $order_subtotal = tep_db_fetch_array($order_subtotal_query);

            $subtotal_value = $this->format_raw($order_subtotal['value'], DEFAULT_CURRENCY);

            $adwords_display .= '<script type="text/javascript">' . "\n";
            $adwords_display .= '/* <![CDATA[ */' . "\n";
            $adwords_display .= 'var google_conversion_id = ' . MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_ID . ';' . "\n";
            $adwords_display .= 'var google_conversion_language = "' . MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_LANGUAGE . '";' . "\n";
            $adwords_display .= 'var google_conversion_format = "' . MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_FORMAT . '";' . "\n";
            $adwords_display .= 'var google_conversion_color = "' . MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_COLOUR . '";' . "\n";
            $adwords_display .= 'var google_conversion_label = "' . MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_LABEL . '";' . "\n";
            $adwords_display .= 'var google_conversion_value = "' . $subtotal_value . '";' . "\n";
            $adwords_display .= '/* ]]> */' . "\n";
            $adwords_display .= '</script>' . "\n";
            $adwords_display .= '<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script>' . "\n";
            $adwords_display .= '<noscript>' . "\n";
            $adwords_display .= '<div style="display:inline;">' . "\n";
            $adwords_display .= '<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/' . MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_ID . '/?value=' . $subtotal_value . '&amp;label=' . MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_LABEL . '&amp;guid=ON&amp;script=0"/>' . "\n";
            $adwords_display .= '</div>' . "\n";
            $adwords_display .= '</noscript>' . "\n";

            $oscTemplate->addBlock($adwords_display, $this->group);
          }
        }
      }
    }

    function format_raw($number, $currency_code = '', $currency_value = '') {
      global $currencies, $currency;

      if (empty($currency_code) || !$currencies->is_set($currency_code)) {
        $currency_code = $currency;
      }

      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(tep_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }
    
    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_GOOGLE_ADWORDS_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Adwords Conversion Module', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_STATUS', 'True', 'Do you want to allow the Google Adwords Conversion Module on your checkout success page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Conversion ID', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_ID', '', 'Your Conversion ID', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Conversion Language', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_LANGUAGE', 'en_GB', 'Conversion Language', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Conversion Format', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_FORMAT', '1', 'Conversion Format', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Conversion Colour', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_COLOUR', 'ffffff', 'Conversion Colour', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Conversion Label', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_LABEL', '', 'Conversion Label', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Javascript Placement', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_JS_PLACEMENT', 'Footer', 'Should the Google Adwords javascript be loaded in the header or footer?', '6', '1', 'tep_cfg_select_option(array(\'Header\', \'Footer\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_GOOGLE_ADWORDS_STATUS', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_ID', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_LANGUAGE', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_FORMAT', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_COLOUR', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_LABEL', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_JS_PLACEMENT', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_SORT_ORDER');
    }
  }
?>
