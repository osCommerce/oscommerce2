<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

/**
 * Class currencies
 * 
 * Class for currencies calculation
 */
  class currencies {
    var $currencies;

/**
 * Class constructor
 */
    function currencies() {
      $this->currencies = array();
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

/** 
 * Formats the price string
 *
 * @param integer $number
 * @param boolean $calculate_currency_value
 * @param string $currency_type
 * @param string $currency_value
 * @return string
 */
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
  
/**
 * Calculate price
 *
 * @param float $products_price
 * @param string $products_tax
 * @param int $quantity
 * @return float
 */
    function calculate_price($products_price, $products_tax, $quantity = 1) {
      return tep_round(tep_add_tax($products_price, $products_tax), $this->currencies[$_SESSION['currency']]['decimal_places']) * $quantity;
    }

/**
 * Checks if currencies code is set
 *
 * @param string $code
 * @return boolean
 */
    function is_set($code) {
      if (isset($this->currencies[$code]) && tep_not_null($this->currencies[$code])) {
        return true;
      } else {
        return false;
      }
    }
  
/**
 * Gets the currency code value
 *
 * @param string $code
 * @return string
 */
    function get_value($code) {
      return $this->currencies[$code]['value'];
    }
  
/**
 * Gets the currency decimal places
 * @param string $code
 * @return string
 */
    function get_decimal_places($code) {
      return $this->currencies[$code]['decimal_places'];
    }
    
/**
 * Shows the product price
 *
 * @param string $products_price
 * @param string $products_tax
 * @param string $quantity
 * @return string
 */
    function display_price($products_price, $products_tax, $quantity = 1) {
      return $this->format($this->calculate_price($products_price, $products_tax, $quantity));
    }
  }
?>
