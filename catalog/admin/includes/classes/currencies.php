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
    function currencies() {
      $OSCOM_Db = Registry::get('Db');

      $this->currencies = array();
      $Qcurrencies = $OSCOM_Db->get('currencies', [
        'code',
        'title',
        'symbol_left',
        'symbol_right',
        'decimal_point',
        'thousands_point',
        'decimal_places',
        'value'
      ]);

      while ($Qcurrencies->fetch()) {
        $this->currencies[$Qcurrencies->value('code')] = [
          'title' => $Qcurrencies->value('title'),
          'symbol_left' => $Qcurrencies->value('symbol_left'),
          'symbol_right' => $Qcurrencies->value('symbol_right'),
          'decimal_point' => $Qcurrencies->value('decimal_point'),
          'thousands_point' => $Qcurrencies->value('thousands_point'),
          'decimal_places' => $Qcurrencies->value('decimal_places'),
          'value' => $Qcurrencies->value('value')
        ];
      }
    }

// class methods
    function format($number, $calculate_currency_value = true, $currency_type = DEFAULT_CURRENCY, $currency_value = '') {
      if ($calculate_currency_value) {
        $rate = ($currency_value) ? $currency_value : $this->currencies[$currency_type]['value'];
        $format_string = $this->currencies[$currency_type]['symbol_left'] . number_format($number * $rate, $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) . $this->currencies[$currency_type]['symbol_right'];
// if the selected currency is in the european euro-conversion and the default currency is euro,
// the currency will displayed in the national currency and euro currency
        if ( (DEFAULT_CURRENCY == 'EUR') && ($currency_type == 'DEM' || $currency_type == 'BEF' || $currency_type == 'LUF' || $currency_type == 'ESP' || $currency_type == 'FRF' || $currency_type == 'IEP' || $currency_type == 'ITL' || $currency_type == 'NLG' || $currency_type == 'ATS' || $currency_type == 'PTE' || $currency_type == 'FIM' || $currency_type == 'GRD') ) {
          $format_string .= ' <small>[' . $this->format($number, true, 'EUR') . ']</small>';
        }
      } else {
        $format_string = $this->currencies[$currency_type]['symbol_left'] . number_format($number, $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) . $this->currencies[$currency_type]['symbol_right'];
      }

      return $format_string;
    }

    function get_value($code) {
      return $this->currencies[$code]['value'];
    }

    function display_price($products_price, $products_tax, $quantity = 1, $currency_type = DEFAULT_CURRENCY) {
      return $this->format(tep_round(tep_add_tax($products_price, $products_tax), $this->currencies[$currency_type]['decimal_places']) * $quantity);
    }
  }
?>
