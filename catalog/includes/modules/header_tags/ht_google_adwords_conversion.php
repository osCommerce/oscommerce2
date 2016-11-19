<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class ht_google_adwords_conversion {
    var $code = 'ht_google_adwords_conversion';
    var $group = 'footer_scripts';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    protected $lang;

    function __construct() {
      $this->lang = Registry::get('Language');

      $this->title = OSCOM::getDef('module_header_tags_google_adwords_conversion_title');
      $this->description = OSCOM::getDef('module_header_tags_google_adwords_conversion_description');

      if ( defined('MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate;

      $OSCOM_Db = Registry::get('Db');

      if (MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_JS_PLACEMENT != 'Footer') {
        $this->group = 'header_tags';
      }

      if ( ($PHP_SELF == 'checkout_success.php') && isset($_SESSION['customer_id']) ) {
        $Qorder = $OSCOM_Db->get('orders', ['orders_id', 'currency', 'currency_value'], ['customers_id' => $_SESSION['customer_id']], 'date_purchased desc', 1);

        if ($Qorder->fetch() !== false) {
          $Qsubtotal = $OSCOM_Db->get('orders_total', 'value', ['orders_id' => $Qorder->valueInt('orders_id'), 'class' => 'ot_subtotal']);

          $conversion_id = (int)MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_ID;
          $conversion_language = HTML::outputProtected($this->lang->get('code'));
          $conversion_format = (int)MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_FORMAT;
          $conversion_color = HTML::outputProtected(MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_COLOR);
          $conversion_label = HTML::outputProtected(MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_LABEL);
          $conversion_value = $this->format_raw($Qsubtotal->value('value'), $Qorder->value('currency'), $Qorder->value('currency_value'));

          $output = <<<EOD
<script>
/* <![CDATA[ */
var google_conversion_id = {$conversion_id};
var google_conversion_language = "{$conversion_language}";
var google_conversion_format = "{$conversion_format}";
var google_conversion_color = "{$conversion_color}";
var google_conversion_label = "{$conversion_label}";
var google_conversion_value = {$conversion_value};
/* ]]> */
</script>
<script src="//www.googleadservices.com/pagead/conversion.js"></script>
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
      global $currencies;

      if (empty($currency_code) || !$currencies->is_set($currency_code)) {
        $currency_code = $_SESSION['currency'];
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
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Google AdWords Conversion Module',
        'configuration_key' => 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to allow the Google AdWords Conversion Module on your checkout success page?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Conversion ID',
        'configuration_key' => 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_ID',
        'configuration_value' => '',
        'configuration_description' => 'The Google AdWords Conversion ID',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Tracking Notification Layout',
        'configuration_key' => 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_FORMAT',
        'configuration_value' => '1',
        'configuration_description' => 'A small message will appear on your site telling customers that their visits on your site are being tracked. We recommend you use it.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_google_adwords_conversion_set_format(',
        'use_function' => 'tep_cfg_google_adwords_conversion_get_format',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Page Background Color',
        'configuration_key' => 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_COLOR',
        'configuration_value' => 'ffffff',
        'configuration_description' => 'Enter a HTML color to match the color of your website background page.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Conversion Label',
        'configuration_key' => 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_LABEL',
        'configuration_value' => '',
        'configuration_description' => 'The alphanumeric code generated by Google for your AdWords Conversion',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Javascript Placement',
        'configuration_key' => 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_JS_PLACEMENT',
        'configuration_value' => 'Footer',
        'configuration_description' => 'Should the Google AdWords Conversion javascript be loaded in the header or footer?',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'Header\', \'Footer\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_HEADER_TAGS_GOOGLE_ADWORDS_CONVERSION_SORT_ORDER',
        'configuration_value' => '0',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
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
