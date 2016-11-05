<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Hash;
  use OSC\OM\Registry;

  class shoppingCart {
    var $contents, $total, $weight, $cartID, $content_type;

    function __construct() {
      $this->reset();
    }

    function restore_contents() {
      $OSCOM_Db = Registry::get('Db');

      if (!isset($_SESSION['customer_id'])) return false;

// insert current cart contents in database
      if (is_array($this->contents)) {
        foreach ( array_keys($this->contents) as $products_id ) {
          $qty = $this->contents[$products_id]['qty'];

          $Qcheck = $OSCOM_Db->prepare('select products_id from :table_customers_basket where customers_id = :customers_id and products_id = :products_id');
          $Qcheck->bindInt(':customers_id', $_SESSION['customer_id']);
          $Qcheck->bindValue(':products_id', $products_id);
          $Qcheck->execute();

          if ($Qcheck->fetch() === false) {
            $OSCOM_Db->save('customers_basket', ['customers_id' => $_SESSION['customer_id'], 'products_id' => $products_id, 'customers_basket_quantity' => $qty, 'customers_basket_date_added' => date('Ymd')]);

            if (isset($this->contents[$products_id]['attributes'])) {
              foreach ($this->contents[$products_id]['attributes'] as $option => $value) {
                $OSCOM_Db->save('customers_basket_attributes', ['customers_id' => $_SESSION['customer_id'], 'products_id' => $products_id, 'products_options_id' => $option, 'products_options_value_id' => $value]);
              }
            }
          } else {
            $OSCOM_Db->save('customers_basket', ['customers_basket_quantity' => $qty], ['customers_id' => $_SESSION['customer_id'], 'products_id' => $products_id]);
          }
        }
      }

// reset per-session cart contents, but not the database contents
      $this->reset(false);

      $Qproducts = $OSCOM_Db->prepare('select products_id, customers_basket_quantity from :table_customers_basket where customers_id = :customers_id');
      $Qproducts->bindInt(':customers_id', $_SESSION['customer_id']);
      $Qproducts->execute();

      while ($Qproducts->fetch()) {
        $this->contents[$Qproducts->value('products_id')] = array('qty' => $Qproducts->valueInt('customers_basket_quantity'));

// attributes
        $Qattributes = $OSCOM_Db->prepare('select products_options_id, products_options_value_id from :table_customers_basket_attributes where customers_id = :customers_id and products_id = :products_id');
        $Qattributes->bindInt(':customers_id', $_SESSION['customer_id']);
        $Qattributes->bindValue(':products_id', $Qproducts->value('products_id'));
        $Qattributes->execute();

        while ($Qattributes->fetch()) {
          $this->contents[$Qproducts->value('products_id')]['attributes'][$Qattributes->valueInt('products_options_id')] = $Qattributes->valueInt('products_options_value_id');
        }
      }

      $this->cleanup();

// assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
      $this->cartID = $this->generate_cart_id();
    }

    function reset($reset_database = false) {
      $OSCOM_Db = Registry::get('Db');

      $this->contents = array();
      $this->total = 0;
      $this->weight = 0;
      $this->content_type = false;

      if (isset($_SESSION['customer_id']) && ($reset_database == true)) {
        $OSCOM_Db->delete('customers_basket', ['customers_id' => $_SESSION['customer_id']]);
        $OSCOM_Db->delete('customers_basket_attributes', ['customers_id' => $_SESSION['customer_id']]);
      }

      unset($this->cartID);
      if (isset($_SESSION['cartID'])) unset($_SESSION['cartID']);
    }

    function add_cart($products_id, $qty = '1', $attributes = '', $notify = true) {
      $OSCOM_Db = Registry::get('Db');

      $products_id_string = tep_get_uprid($products_id, $attributes);
      $products_id = tep_get_prid($products_id_string);

      if (defined('MAX_QTY_IN_CART') && (MAX_QTY_IN_CART > 0) && ((int)$qty > MAX_QTY_IN_CART)) {
        $qty = MAX_QTY_IN_CART;
      }

      $attributes_pass_check = true;

      if (is_array($attributes) && !empty($attributes)) {
        foreach ($attributes as $option => $value) {
          if (!is_numeric($option) || !is_numeric($value)) {
            $attributes_pass_check = false;
            break;
          } else {
            $Qcheck = $OSCOM_Db->prepare('select products_attributes_id from :table_products_attributes where products_id = :products_id and options_id = :options_id and options_values_id = :options_values_id limit 1');
            $Qcheck->bindInt(':products_id', $products_id);
            $Qcheck->bindInt(':options_id', $option);
            $Qcheck->bindInt(':options_values_id', $value);
            $Qcheck->execute();

            if ($Qcheck->fetch() === false) {
              $attributes_pass_check = false;
              break;
            }
          }
        }
      } elseif (tep_has_product_attributes($products_id)) {
        $attributes_pass_check = false;
      }

      if (is_numeric($products_id) && is_numeric($qty) && ($attributes_pass_check == true)) {
        $Qcheck = $OSCOM_Db->prepare('select products_id from :table_products where products_id = :products_id and products_status = 1');
        $Qcheck->bindInt(':products_id', $products_id);
        $Qcheck->execute();

        if ($Qcheck->fetch() !== false) {
          if ($notify == true) {
            $_SESSION['new_products_id_in_cart'] = $products_id;
          }

          if ($this->in_cart($products_id_string)) {
            $this->update_quantity($products_id_string, $qty, $attributes);
          } else {
            $this->contents[$products_id_string] = array('qty' => (int)$qty);

// insert into database
            if (isset($_SESSION['customer_id'])) {
              $OSCOM_Db->save('customers_basket', ['customers_id' => $_SESSION['customer_id'], 'products_id' => $products_id_string, 'customers_basket_quantity' => $qty, 'customers_basket_date_added' => date('Ymd')]);
            }

            if (is_array($attributes)) {
              foreach ($attributes as $option => $value) {
                $this->contents[$products_id_string]['attributes'][$option] = $value;

// insert into database
                if (isset($_SESSION['customer_id'])) {
                  $OSCOM_Db->save('customers_basket_attributes', ['customers_id' => $_SESSION['customer_id'], 'products_id' => $products_id_string, 'products_options_id' => (int)$option, 'products_options_value_id' => (int)$value]);
                }
              }
            }
          }

          $this->cleanup();

// assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
          $this->cartID = $this->generate_cart_id();
        }
      }
    }

    function update_quantity($products_id, $quantity = '', $attributes = '') {
      $OSCOM_Db = Registry::get('Db');

      $products_id_string = tep_get_uprid($products_id, $attributes);
      $products_id = tep_get_prid($products_id_string);

      if (defined('MAX_QTY_IN_CART') && (MAX_QTY_IN_CART > 0) && ((int)$quantity > MAX_QTY_IN_CART)) {
        $quantity = MAX_QTY_IN_CART;
      }

      $attributes_pass_check = true;

      if (is_array($attributes)) {
        foreach ($attributes as $option => $value) {
          if (!is_numeric($option) || !is_numeric($value)) {
            $attributes_pass_check = false;
            break;
          }
        }
      }

      if (is_numeric($products_id) && isset($this->contents[$products_id_string]) && is_numeric($quantity) && ($attributes_pass_check == true)) {
        $this->contents[$products_id_string] = array('qty' => (int)$quantity);

// update database
        if (isset($_SESSION['customer_id'])) {
          $OSCOM_Db->save('customers_basket', ['customers_basket_quantity' => (int)$quantity], ['customers_id' => $_SESSION['customer_id'], 'products_id' => $products_id_string]);
        }

        if (is_array($attributes)) {
          foreach ($attributes as $option => $value) {
            $this->contents[$products_id_string]['attributes'][$option] = $value;

// update database
            if (isset($_SESSION['customer_id'])) {
              $OSCOM_Db->save('customers_basket_attributes', ['products_options_value_id' => (int)$value], ['customers_id' => $_SESSION['customer_id'], 'products_id' => $products_id_string, 'products_options_id' => (int)$option]);
            }
          }
        }

// assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
        $this->cartID = $this->generate_cart_id();
      }
    }

    function cleanup() {
      $OSCOM_Db = Registry::get('Db');

      foreach ( array_keys($this->contents) as $key ) {
        if ($this->contents[$key]['qty'] < 1) {
          unset($this->contents[$key]);

// remove from database
          if (isset($_SESSION['customer_id'])) {
            $OSCOM_Db->delete('customers_basket', ['customers_id' => $_SESSION['customer_id'], 'products_id' => $key]);
            $OSCOM_Db->delete('customers_basket_attributes', ['customers_id' => $_SESSION['customer_id'], 'products_id' => $key]);
          }
        }
      }
    }

    function count_contents() {  // get total number of items in cart
      $total_items = 0;
      if (is_array($this->contents)) {
        foreach ( array_keys($this->contents) as $products_id ) {
          $total_items += $this->get_quantity($products_id);
        }
      }

      return $total_items;
    }

    function get_quantity($products_id) {
      if (isset($this->contents[$products_id])) {
        return $this->contents[$products_id]['qty'];
      } else {
        return 0;
      }
    }

    function in_cart($products_id) {
      if (isset($this->contents[$products_id])) {
        return true;
      } else {
        return false;
      }
    }

    function remove($products_id) {
      $OSCOM_Db = Registry::get('Db');

      unset($this->contents[$products_id]);

// remove from database
      if (isset($_SESSION['customer_id'])) {
        $OSCOM_Db->delete('customers_basket', ['customers_id' => $_SESSION['customer_id'], 'products_id' => $products_id]);
        $OSCOM_Db->delete('customers_basket_attributes', ['customers_id' => $_SESSION['customer_id'], 'products_id' => $products_id]);
      }

// assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
      $this->cartID = $this->generate_cart_id();
    }

    function remove_all() {
      $this->reset();
    }

    function get_product_id_list() {
      $product_id_list = '';
      if (is_array($this->contents)) {
       foreach ( array_keys($this->contents) as $products_id ) {
          $product_id_list .= ', ' . $products_id;
        }
      }

      return substr($product_id_list, 2);
    }

    function calculate() {
      global $currencies;

      $OSCOM_Db = Registry::get('Db');

      $this->total = 0;
      $this->weight = 0;
      if (!is_array($this->contents)) return 0;

      foreach ( array_keys($this->contents) as $products_id ) {
        $qty = $this->contents[$products_id]['qty'];

// products price
        $Qproduct = $OSCOM_Db->prepare('select products_id, products_price, products_tax_class_id, products_weight from :table_products where products_id = :products_id');
        $Qproduct->bindInt(':products_id', $products_id);
        $Qproduct->execute();

        if ($Qproduct->fetch() !== false) {
          $prid = $Qproduct->valueInt('products_id');
          $products_tax = tep_get_tax_rate($Qproduct->valueInt('products_tax_class_id'));
          $products_price = $Qproduct->valueDecimal('products_price');
          $products_weight = $Qproduct->valueDecimal('products_weight');

          $Qspecial = $OSCOM_Db->prepare('select specials_new_products_price from :table_specials where products_id = :products_id and status = 1');
          $Qspecial->bindInt(':products_id', $prid);
          $Qspecial->execute();

          if ($Qspecial->fetch() !== false) {
            $products_price = $Qspecial->valueDecimal('specials_new_products_price');
          }

          $this->total += $currencies->calculate_price($products_price, $products_tax, $qty);
          $this->weight += ($qty * $products_weight);

// attributes price
          if (isset($this->contents[$products_id]['attributes'])) {
            foreach ($this->contents[$products_id]['attributes'] as $option => $value) {
              $Qattributes = $OSCOM_Db->prepare('select options_values_price, price_prefix from :table_products_attributes where products_id = :products_id and options_id = :options_id and options_values_id = :options_values_id');
              $Qattributes->bindInt(':products_id', $prid);
              $Qattributes->bindInt(':options_id', $option);
              $Qattributes->bindInt(':options_values_id', $value);
              $Qattributes->execute();

              if ($Qattributes->fetch() !== false) {
                if ($Qattributes->value('price_prefix') == '+') {
                  $this->total += $currencies->calculate_price($Qattributes->valueDecimal('options_values_price'), $products_tax, $qty);
                } else {
                  $this->total -= $currencies->calculate_price($Qattributes->valueDecimal('options_values_price'), $products_tax, $qty);
                }
              }
            }
          }
        }
      }
    }

    function attributes_price($products_id) {
      $OSCOM_Db = Registry::get('Db');

      $attributes_price = 0;

      if (isset($this->contents[$products_id]['attributes'])) {
        foreach ($this->contents[$products_id]['attributes'] as $option => $value) {
          $Qattributes = $OSCOM_Db->prepare('select options_values_price, price_prefix from :table_products_attributes where products_id = :products_id and options_id = :options_id and options_values_id = :options_values_id');
          $Qattributes->bindInt(':products_id', $products_id);
          $Qattributes->bindInt(':options_id', $option);
          $Qattributes->bindInt(':options_values_id', $value);
          $Qattributes->execute();

          if ($Qattributes->fetch() !== false) {
            if ($Qattributes->value('price_prefix') == '+') {
              $attributes_price += $Qattributes->valueDecimal('options_values_price');
            } else {
              $attributes_price -= $Qattributes->valueDecimal('options_values_price');
            }
          }
        }
      }

      return $attributes_price;
    }

    function get_products() {
      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      if (!is_array($this->contents)) return false;

      $products_array = array();

      foreach ( array_keys($this->contents) as $products_id ) {
        $Qproducts = $OSCOM_Db->prepare('select p.products_id, pd.products_name, p.products_model, p.products_image, p.products_price, p.products_weight, p.products_tax_class_id from :table_products p, :table_products_description pd where p.products_id = :products_id and p.products_id = pd.products_id and pd.language_id = :language_id');
        $Qproducts->bindInt(':products_id', $products_id);
        $Qproducts->bindInt(':language_id', $OSCOM_Language->getId());
        $Qproducts->execute();

        if ($Qproducts->fetch() !== false) {
          $products_price = $Qproducts->valueDecimal('products_price');

          $Qspecial = $OSCOM_Db->prepare('select specials_new_products_price from :table_specials where products_id = :products_id and status = 1');
          $Qspecial->bindInt(':products_id', $Qproducts->valueInt('products_id'));
          $Qspecial->execute();

          if ($Qspecial->fetch() !== false) {
            $products_price = $Qspecial->valueDecimal('specials_new_products_price');
          }

          $products_array[] = array('id' => $products_id,
                                    'name' => $Qproducts->value('products_name'),
                                    'model' => $Qproducts->value('products_model'),
                                    'image' => $Qproducts->value('products_image'),
                                    'price' => $products_price,
                                    'quantity' => $this->contents[$products_id]['qty'],
                                    'weight' => $Qproducts->valueDecimal('products_weight'),
                                    'final_price' => ($products_price + $this->attributes_price($products_id)),
                                    'tax_class_id' => $Qproducts->valueInt('products_tax_class_id'),
                                    'attributes' => (isset($this->contents[$products_id]['attributes']) ? $this->contents[$products_id]['attributes'] : ''));
        }
      }

      return $products_array;
    }

    function show_total() {
      $this->calculate();

      return $this->total;
    }

    function show_weight() {
      $this->calculate();

      return $this->weight;
    }

    function generate_cart_id($length = 5) {
      return Hash::getRandomString($length, 'digits');
    }

    function get_content_type() {
      $OSCOM_Db = Registry::get('Db');

      $this->content_type = false;

      if ( (DOWNLOAD_ENABLED == 'true') && ($this->count_contents() > 0) ) {
        foreach ( array_keys($this->contents) as $products_id ) {
          if (isset($this->contents[$products_id]['attributes'])) {
            foreach ($this->contents[$products_id]['attributes'] as $value) {
              $Qcheck = $OSCOM_Db->prepare('select pa.products_attributes_id from :table_products_attributes pa, :table_products_attributes_download pad where pa.products_id = :products_id and pa.options_values_id = :options_values_id and pa.products_attributes_id = pad.products_attributes_id limit 1');
              $Qcheck->bindInt(':products_id', $products_id);
              $Qcheck->bindInt(':options_values_id', $value);
              $Qcheck->execute();

              if ($Qcheck->fetch() !== false) {
                switch ($this->content_type) {
                  case 'physical':
                    $this->content_type = 'mixed';

                    return $this->content_type;
                    break;
                  default:
                    $this->content_type = 'virtual';
                    break;
                }
              } else {
                switch ($this->content_type) {
                  case 'virtual':
                    $this->content_type = 'mixed';

                    return $this->content_type;
                    break;
                  default:
                    $this->content_type = 'physical';
                    break;
                }
              }
            }
          } else {
            switch ($this->content_type) {
              case 'virtual':
                $this->content_type = 'mixed';

                return $this->content_type;
                break;
              default:
                $this->content_type = 'physical';
                break;
            }
          }
        }
      } else {
        $this->content_type = 'physical';
      }

      return $this->content_type;
    }

    function unserialize($broken) {
      for(reset($broken);$kv=each($broken);) {
        $key=$kv['key'];
        if (gettype($this->$key)!="user function")
        $this->$key=$kv['value'];
      }
    }

  }
?>
