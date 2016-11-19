<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Registry;

  class shoppingCart {
    var $db, $contents, $total, $weight;

    function shoppingCart() {
      $this->db = Registry::get('Db');

      $this->contents = array();
      $this->total = 0;
    }

    function add_cart($products_id, $qty = '', $attributes = '') {
      $products_id = tep_get_uprid($products_id, $attributes);

      if ($this->in_cart($products_id)) {
        $this->update_quantity($products_id, $qty, $attributes);
      } else {
        if ($qty == '') $qty = '1'; // if no quantity is supplied, then add '1' to the customers basket

        $this->contents[$products_id] = array('qty' => $qty);

        if (is_array($attributes)) {
          foreach( $attributes as $option => $value ) {
            $this->contents[$products_id]['attributes'][$option] = $value;
          }
        }
      }
      $this->cleanup();
    }

    function update_quantity($products_id, $quantity = '', $attributes = '') {
      if ($quantity == '') return true; // nothing needs to be updated if theres no quantity, so we return true..

      $this->contents[$products_id] = array('qty' => $quantity);

      if (is_array($attributes)) {
        foreach( $attributes as $option => $value ) {
          $this->contents[$products_id]['attributes'][$option] = $value;
        }
      }
    }

    function cleanup() {
      foreach( array_keys($this->contents) as $key ) {
        if ($this->contents[$key]['qty'] < 1) {
          unset($this->contents[$key]);
        }
      }
    }

    function in_cart($products_id) {
      if (isset($this->contents[$products_id])) {
        return true;
      } else {
        return false;
      }
    }

    function calculate() {
      $this->total = 0;
      $this->weight = 0;
      if (!is_array($this->contents)) return 0;

      foreach ( array_keys($this->contents) as $products_id ) {
        $qty = $this->contents[$products_id]['qty'];

// products price
        $Qproduct = $this->db->get('products', [
          'products_id',
          'products_price',
          'products_tax_class_id',
          'products_weight'
        ], [
          'products_id' => (int)tep_get_prid($products_id)
        ]);

        if ($Qproduct->fetch() !== false) {
          $prid = $Qproduct->valueInt('products_id');
          $products_tax = tep_get_tax_rate($Qproduct->valueInt('products_tax_class_id'));
          $products_price = $Qproduct->value('products_price');
          $products_weight = $Qproduct->value('products_weight');

          $Qspecials = $this->db->get('specials', 'specials_new_products_price', ['products_id' => $prid, 'status' => '1']);

          if ($Qspecials->fetch() !== false) {
            $products_price = $Qspecials->value('specials_new_products_price');
          }

          $this->total += tep_add_tax($products_price, $products_tax) * $qty;
          $this->weight += ($qty * $products_weight);

// attributes price
          if (isset($this->contents[$products_id]['attributes'])) {
            foreach ( $this->contents[$products_id]['attributes'] as $option => $value ) {
              $Qattribute = $this->db->get('products_attributes', [
                'options_values_price',
                'price_prefix'
              ], [
                'products_id' => $prid,
                'options_id' => (int)$option,
                'options_values_id' => (int)$value
              ]);

              if ($Qattribute->value('price_prefix') == '+') {
                $this->total += $qty * tep_add_tax($Qattribute->value('options_values_price'), $products_tax);
              } else {
                $this->total -= $qty * tep_add_tax($Qattribute->value('options_values_price'), $products_tax);
              }
            }
          }
        }
      }
    }

    function show_total() {
      $this->calculate();

      return $this->total;
    }
  }
?>
