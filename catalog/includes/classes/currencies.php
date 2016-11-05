<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Registry;

////
// Class to handle currencies
// TABLES: currencies
  class currencies {
    var $currencies;

// class constructor
    function __construct() {
      $OSCOM_Db = Registry::get('Db');

      $this->currencies = array();

      $Qcurrencies = $OSCOM_Db->query('select code, title, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, value from :table_currencies');

      while ($Qcurrencies->fetch()) {
        $this->currencies[$Qcurrencies->value('code')] = array('title' => $Qcurrencies->value('title'),
                                                               'symbol_left' => $Qcurrencies->value('symbol_left'),
                                                               'symbol_right' => $Qcurrencies->value('symbol_right'),
                                                               'decimal_point' => $Qcurrencies->value('decimal_point'),
                                                               'thousands_point' => $Qcurrencies->value('thousands_point'),
                                                               'decimal_places' => $Qcurrencies->valueInt('decimal_places'),
                                                               'value' => $Qcurrencies->valueDecimal('value'));
      }
    }

// class methods
    function format($number, $calculate_currency_value = true, $currency_type = '', $currency_value = '') {
      if (empty($currency_type)) $currency_type = $_SESSION['currency'];

      if ($calculate_currency_value == true) {
        $rate = (tep_not_null($currency_value)) ? $currency_value : $this->currencies[$currency_type]['value'];
        $format_string = $this->currencies[$currency_type]['symbol_left'] . number_format(tep_round($number * $rate, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) . $this->currencies[$currency_type]['symbol_right'];
      } else {
        $format_string = $this->currencies[$currency_type]['symbol_left'] . number_format(tep_round($number, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) . $this->currencies[$currency_type]['symbol_right'];
      }

      return $format_string;
    }

    function calculate_price($products_price, $products_tax, $quantity = 1) {
      return tep_round(tep_add_tax($products_price, $products_tax), $this->currencies[$_SESSION['currency']]['decimal_places']) * $quantity;
    }

    function is_set($code) {
      if (isset($this->currencies[$code]) && tep_not_null($this->currencies[$code])) {
        return true;
      } else {
        return false;
      }
    }

    function get_value($code) {
      return $this->currencies[$code]['value'];
    }

    function get_decimal_places($code) {
      return $this->currencies[$code]['decimal_places'];
    }

    function display_price($products_price, $products_tax, $quantity = 1) {
      return $this->format($this->calculate_price($products_price, $products_tax, $quantity));
    }

    function format_raw($number, $calculate_currency_value = true, $currency_type = '', $currency_value = '') {
      if (empty($currency_type)) $currency_type = $_SESSION['currency'];

      if ($calculate_currency_value == true) {
        $rate = (tep_not_null($currency_value)) ? $currency_value : $this->currencies[$currency_type]['value'];
        $format_string = number_format(tep_round($number * $rate, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], '.', '');
      } else {
        $format_string = number_format(tep_round($number, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], '.', '');
      }

      return $format_string;
    }

    function display_raw($products_price, $products_tax, $quantity = 1) {
      return $this->format_raw($this->calculate_price($products_price, $products_tax, $quantity));
    }
  }
?>
