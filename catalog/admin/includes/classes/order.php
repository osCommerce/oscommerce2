<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Registry;

  class order {
    var $info, $totals, $products, $customer, $delivery;

    function order($order_id) {
      $this->info = array();
      $this->totals = array();
      $this->products = array();
      $this->customer = array();
      $this->delivery = array();

      $this->query($order_id);
    }

    function query($order_id) {
      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      $Qorder = $OSCOM_Db->get([
        'orders o',
        'orders_status s'
      ], [
        'o.*',
        's.orders_status_name'
      ], [
        'o.orders_id' => (int)$order_id,
        'o.orders_status' => [
          'rel' => 's.orders_status_id'
        ],
        's.language_id' => $OSCOM_Language->getId()
      ]);

      $Qtotals = $OSCOM_Db->get('orders_total', [
        'title',
        'text',
        'class'
      ], [
        'orders_id' => (int)$order_id
      ], 'sort_order');

      while ($Qtotals->fetch()) {
        $this->totals[] = [
          'title' => $Qtotals->value('title'),
          'text' => $Qtotals->value('text'),
          'class' => $Qtotals->value('class')
        ];
      }

      $this->info = array('id' => $Qorder->valueInt('orders_id'),
                          'total' => null,
                          'currency' => $Qorder->value('currency'),
                          'currency_value' => $Qorder->value('currency_value'),
                          'payment_method' => $Qorder->value('payment_method'),
                          'cc_type' => $Qorder->value('cc_type'),
                          'cc_owner' => $Qorder->value('cc_owner'),
                          'cc_number' => $Qorder->value('cc_number'),
                          'cc_expires' => $Qorder->value('cc_expires'),
                          'date_purchased' => $Qorder->value('date_purchased'),
                          'status' => $Qorder->value('orders_status_name'),
                          'orders_status' => $Qorder->value('orders_status'),
                          'last_modified' => $Qorder->value('last_modified'));

      foreach ( $this->totals as $t ) {
        if ( $t['class'] == 'ot_total' ) {
          $this->info['total'] = $t['text'];
          break;
        }
      }

      $this->customer = array('name' => $Qorder->value('customers_name'),
                              'company' => $Qorder->value('customers_company'),
                              'street_address' => $Qorder->value('customers_street_address'),
                              'suburb' => $Qorder->value('customers_suburb'),
                              'city' => $Qorder->value('customers_city'),
                              'postcode' => $Qorder->value('customers_postcode'),
                              'state' => $Qorder->value('customers_state'),
                              'country' => $Qorder->value('customers_country'),
                              'format_id' => $Qorder->value('customers_address_format_id'),
                              'telephone' => $Qorder->value('customers_telephone'),
                              'email_address' => $Qorder->value('customers_email_address'));

      $this->delivery = array('name' => $Qorder->value('delivery_name'),
                              'company' => $Qorder->value('delivery_company'),
                              'street_address' => $Qorder->value('delivery_street_address'),
                              'suburb' => $Qorder->value('delivery_suburb'),
                              'city' => $Qorder->value('delivery_city'),
                              'postcode' => $Qorder->value('delivery_postcode'),
                              'state' => $Qorder->value('delivery_state'),
                              'country' => $Qorder->value('delivery_country'),
                              'format_id' => $Qorder->value('delivery_address_format_id'));

      $this->billing = array('name' => $Qorder->value('billing_name'),
                             'company' => $Qorder->value('billing_company'),
                             'street_address' => $Qorder->value('billing_street_address'),
                             'suburb' => $Qorder->value('billing_suburb'),
                             'city' => $Qorder->value('billing_city'),
                             'postcode' => $Qorder->value('billing_postcode'),
                             'state' => $Qorder->value('billing_state'),
                             'country' => $Qorder->value('billing_country'),
                             'format_id' => $Qorder->value('billing_address_format_id'));

      $index = 0;

      $Qproducts = $OSCOM_Db->get('orders_products', [
        'orders_products_id',
        'products_name',
        'products_model',
        'products_price',
        'products_tax',
        'products_quantity',
        'final_price'
      ], [
        'orders_id' => (int)$order_id
      ]);

      while ($Qproducts->fetch()) {
        $this->products[$index] = array('qty' => $Qproducts->value('products_quantity'),
                                        'name' => $Qproducts->value('products_name'),
                                        'model' => $Qproducts->value('products_model'),
                                        'tax' => $Qproducts->value('products_tax'),
                                        'price' => $Qproducts->value('products_price'),
                                        'final_price' => $Qproducts->value('final_price'));

        $subindex = 0;

        $Qattributes = $OSCOM_Db->get('orders_products_attributes', [
          'products_options',
          'products_options_values',
          'options_values_price',
          'price_prefix'
        ], [
          'orders_id' => (int)$order_id,
          'orders_products_id' => $Qproducts->valueInt('orders_products_id')
        ]);

        if ($Qattributes->fetch() !== false) {
          do {
            $this->products[$index]['attributes'][$subindex] = array('option' => $Qattributes->value('products_options'),
                                                                     'value' => $Qattributes->value('products_options_values'),
                                                                     'prefix' => $Qattributes->value('price_prefix'),
                                                                     'price' => $Qattributes->value('options_values_price'));

            $subindex++;
          } while ($Qattributes->fetch());
        }
        $index++;
      }
    }
  }
?>
