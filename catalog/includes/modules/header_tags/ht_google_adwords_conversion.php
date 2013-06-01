<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class ht_google_adwords_conversion {
    var $code = 'ht_google_adwords_conversion';
    var $group = 'footer_scripts';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_google_adwords_conversion() {
      $this->title = MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_TITLE;
      $this->description = MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate, $customer_id, $lng, $languages_id;

      if (MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_JS_PLACEMENT != 'Footer') {
        $this->group = 'header_tags';
      }

      if ( ($PHP_SELF == FILENAME_CHECKOUT_SUCCESS) && tep_session_is_registered('customer_id') ) {
        $order_query = tep_db_query("select orders_id, currency, currency_value from " . TABLE_ORDERS . " where customers_id = '" . (int)$customer_id . "' order by date_purchased desc limit 1");

        if (tep_db_num_rows($order_query) == 1) {
          $order = tep_db_fetch_array($order_query);

          $order_subtotal_query = tep_db_query("select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order['orders_id'] . "' and class='ot_subtotal'");
          $order_subtotal = tep_db_fetch_array($order_subtotal_query);

          if (!isset($lng) || (isset($lng) && !is_object($lng))) {
            include(DIR_WS_CLASSES . 'language.php');
            $lng = new language;
          }

          $language_code = 'en';

          foreach ($lng->catalog_languages as $lkey => $lvalue) {
            if ($lvalue['id'] == $languages_id) {
              $language_code = $lkey;
              break;
            }
          }

          $conversion_id = (int)MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_ID;
          $conversion_language = tep_output_string_protected($language_code);
          $conversion_format = (int)MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_FORMAT;
          $conversion_color = tep_output_string_protected(MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_COLOR);
          $conversion_label = tep_output_string_protected(MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_LABEL);
          $conversion_value = $this->format_raw($order_subtotal['value'], $order['currency'], $order['currency_value']);

          $output = <<<EOD
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = {$conversion_id};
var google_conversion_language = "{$conversion_language}";
var google_conversion_format = "{$conversion_format}";
var google_conversion_color = "{$conversion_color}";
var google_conversion_label = "{$conversion_label}";
var google_conversion_value = {$conversion_value};
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/{$conversion_id}/?value={$conversion_value}&amp;label={$conversion_label}&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
EOD;

          $oscTemplate->addBlock($output, $this->group);
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
      return defined('MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Google AdWords Conversion Module', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_STATUS', 'True', 'Do you want to allow the Google AdWords Conversion Module on your checkout success page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Conversion ID', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_ID', '', 'The Google AdWords Conversion ID', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Tracking Notification Layout', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_FORMAT', '1', 'A small message will appear on your site telling customers that their visits on your site are being tracked. We recommend you use it.', '6', '0', 'tep_cfg_google_adwords_conversion_set_format(', 'tep_cfg_google_adwords_conversion_get_format', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Page Background Color', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_COLOR', 'ffffff', 'Enter a HTML color to match the color of your website background page.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Conversion Label', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_LABEL', '', 'The alphanumeric code generated by Google for your AdWords Conversion', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Javascript Placement', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_JS_PLACEMENT', 'Footer', 'Should the Google AdWords Conversion javascript be loaded in the header or footer?', '6', '1', 'tep_cfg_select_option(array(\'Header\', \'Footer\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_STATUS', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_ID', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_FORMAT', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_COLOR', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_LABEL', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_JS_PLACEMENT', 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_SORT_ORDER');
    }
  }

  function tep_cfg_google_adwords_conversion_set_format($key_value, $field_key) {
    $format = array('1' => 'Single Line', '2' => 'Two Lines', '3' => 'No Indicator');

    $string = '';

    foreach ( $format as $key => $value ) {
      $string .= '<br /><input type="radio" name="configuration[' . $field_key . ']" value="' . $key . '"';

      if ($key_value == $key) $string .= ' checked="checked"';

      $string .= ' /> ' . $value;
    }

    return $string;
  }

  function tep_cfg_google_adwords_conversion_get_format($value) {
    $format = array('1' => 'Single Line', '2' => 'Two Lines', '3' => 'No Indicator');

    return $format[$value];
  }
?>
