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

  class ht_google_analytics {
    var $code = 'ht_google_analytics';
    var $group = 'header_tags';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
      $this->title = OSCOM::getDef('module_header_tags_google_analytics_title');
      $this->description = OSCOM::getDef('module_header_tags_google_analytics_description');

      if ( defined('MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_STATUS == 'True');
      }
    }

    function execute() {
      global $PHP_SELF, $oscTemplate;

      $OSCOM_Db = Registry::get('Db');

      if (tep_not_null(MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_ID)) {
        if (MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_JS_PLACEMENT != 'Header') {
          $this->group = 'footer_scripts';
        }

        $header = '<script>
  var _gaq = _gaq || [];
  _gaq.push([\'_setAccount\', \'' . HTML::output(MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_ID) . '\']);
  _gaq.push([\'_trackPageview\']);' . "\n";

        if ( (MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_EC_TRACKING == 'True') && (basename($PHP_SELF) == 'checkout_success.php') && isset($_SESSION['customer_id']) ) {
          $Qorder = $OSCOM_Db->get('orders', ['orders_id', 'billing_city', 'billing_state', 'billing_country'], ['customers_id' => $_SESSION['customer_id']], 'date_purchased desc', 1);

          if ($Qorder->fetch() !== false) {
            $totals = array();

            $Qtotals = $OSCOM_Db->get('orders_total', ['value', 'class'], ['orders_id' => $Qorder->valueInt('orders_id')]);

            while ($Qtotals->fetch()) {
              $totals[$Qtotals->value('class')] = $Qtotals->value('value');
            }

            $header .= '  _gaq.push([\'_addTrans\',
    \'' . $Qorder->valueInt('orders_id') . '\', // order ID - required
    \'' . HTML::output(STORE_NAME) . '\', // store name
    \'' . (isset($totals['ot_total']) ? $this->format_raw($totals['ot_total'], DEFAULT_CURRENCY) : 0) . '\', // total - required
    \'' . (isset($totals['ot_tax']) ? $this->format_raw($totals['ot_tax'], DEFAULT_CURRENCY) : 0) . '\', // tax
    \'' . (isset($totals['ot_shipping']) ? $this->format_raw($totals['ot_shipping'], DEFAULT_CURRENCY) : 0) . '\', // shipping
    \'' . $Qorder->valueProtected('billing_city') . '\', // city
    \'' . $Qorder->valueProtected('billing_state') . '\', // state or province
    \'' . $Qorder->valueProtected('billing_country') . '\' // country
  ]);' . "\n";

            $Qproducts = $OSCOM_Db->prepare('select op.products_id, pd.products_name, op.final_price, op.products_quantity from :table_orders_products op, :table_products_description pd, :table_languages l where op.orders_id = :orders_id and op.products_id = pd.products_id and pd.language_id = l.languages_id and l.code = :code');
            $Qproducts->bindInt(':orders_id', $Qorder->valueInt('orders_id'));
            $Qproducts->bindValue(':code', DEFAULT_LANGUAGE);
            $Qproducts->execute();

            while ($Qproducts->fetch()) {
              $Qcategory = $OSCOM_Db->prepare('select cd.categories_name from :table_categories_description cd, :table_products_to_categories p2c, :table_languages l where p2c.products_id = :products_id and p2c.categories_id = cd.categories_id and cd.language_id = l.languages_id and l.code = :code');
              $Qcategory->bindInt(':products_id', $Qproducts->valueInt('products_id'));
              $Qcategory->bindValue(':code', DEFAULT_LANGUAGE);
              $Qcategory->execute();

              $header .= '  _gaq.push([\'_addItem\',
    \'' . $Qorder->valueInt('orders_id') . '\', // order ID - required
    \'' . $Qproducts->valueInt('products_id') . '\', // SKU/code - required
    \'' . $Qproducts->valueProtected('products_name') . '\', // product name
    \'' . $Qcategory->valueProtected('categories_name') . '\', // category
    \'' . $this->format_raw($Qproducts->value('final_price')) . '\', // unit price - required
    \'' . $Qproducts->valueInt('products_quantity') . '\' // quantity - required
  ]);' . "\n";
            }

            $header .= '  _gaq.push([\'_trackTrans\']); //submits transaction to the Analytics servers' . "\n";
          }
        }

        $header .= '  (function() {
    var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
    ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>' . "\n";

        $oscTemplate->addBlock($header, $this->group);
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
      return defined('MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Google Analytics Module',
        'configuration_key' => 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to add Google Analytics to your shop?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Google Analytics ID',
        'configuration_key' => 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_ID',
        'configuration_value' => '',
        'configuration_description' => 'The Google Analytics profile ID to track.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'E-Commerce Tracking',
        'configuration_key' => 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_EC_TRACKING',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to enable e-commerce tracking? (E-Commerce tracking must also be enabled in your Google Analytics profile settings)',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Javascript Placement',
        'configuration_key' => 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_JS_PLACEMENT',
        'configuration_value' => 'Header',
        'configuration_description' => 'Should the Google Analytics javascript be loaded in the header or footer?',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'Header\', \'Footer\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_SORT_ORDER',
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
      return array('MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_STATUS', 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_ID', 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_EC_TRACKING', 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_JS_PLACEMENT', 'MODULE_HEADER_TAGS_GOOGLE_ANALYTICS_SORT_ORDER');
    }
  }
?>
