<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  namespace osCommerce\OM\classes;

  class currencies {
    protected $currencies = array();

    public function __construct() {
      $currencies_query = tep_db_query("select code, title, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, value from " . TABLE_CURRENCIES);
      while ($currencies = tep_db_fetch_array($currencies_query)) {
        $this->currencies[$currencies['code']] = array('title' => $currencies['title'],
                                                       'symbol_left' => $currencies['symbol_left'],
                                                       'symbol_right' => $currencies['symbol_right'],
                                                       'decimal_point' => $currencies['decimal_point'],
                                                       'thousands_point' => $currencies['thousands_point'],
                                                       'decimal_places' => (int)$currencies['decimal_places'],
                                                       'value' => $currencies['value']);
      }
    }

    public function getAll() {
      return $this->currencies;
    }

    public function format($number, $calculate_currency_value = true, $currency_type = null, $currency_value = null) {
      if ( !isset($currency_type) ) {
        $currency_type = $_SESSION['currency'];
      }

      $rate = 1;

      if ( $calculate_currency_value === true ) {
        $rate = (isset($currency_value)) ? $currency_value : $this->currencies[$currency_type]['value'];
      }

      return $this->currencies[$currency_type]['symbol_left'] . number_format(tep_round($number * $rate, $this->currencies[$currency_type]['decimal_places']), $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) . $this->currencies[$currency_type]['symbol_right'];
    }

    public function calculate_price($products_price, $products_tax, $quantity = 1) {
      return tep_round(tep_add_tax($products_price, $products_tax), $this->currencies[$_SESSION['currency']]['decimal_places']) * $quantity;
    }

    public function is_set($code) {
      return isset($this->currencies[$code]) && tep_not_null($this->currencies[$code]);
    }

    public function get_value($code) {
      return $this->currencies[$code]['value'];
    }

    public function get_decimal_places($code) {
      return $this->currencies[$code]['decimal_places'];
    }

    public function display_price($products_price, $products_tax, $quantity = 1) {
      return $this->format($this->calculate_price($products_price, $products_tax, $quantity));
    }
  }
?>
