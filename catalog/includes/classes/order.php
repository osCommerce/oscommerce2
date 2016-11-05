<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Registry;

  class order {
    var $info, $totals, $products, $customer, $delivery, $content_type;

    function __construct($order_id = '') {
      $this->info = array();
      $this->totals = array();
      $this->products = array();
      $this->customer = array();
      $this->delivery = array();

      if (tep_not_null($order_id)) {
        $this->query($order_id);
      } else {
        $this->cart();
      }
    }

    function query($order_id) {
      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      $order_total = $shipping_title = '';

      $Qorder = $OSCOM_Db->prepare('select * from :table_orders where orders_id = :orders_id');
      $Qorder->bindInt(':orders_id', $order_id);
      $Qorder->execute();

      $Qtotals = $OSCOM_Db->prepare('select title, text, class from :table_orders_total where orders_id = :orders_id order by sort_order');
      $Qtotals->bindInt(':orders_id', $order_id);
      $Qtotals->execute();

      while ($Qtotals->fetch()) {
        $this->totals[] = array('title' => $Qtotals->value('title'),
                                'text' => $Qtotals->value('text'));

        if ($Qtotals->value('class') == 'ot_total') {
          $order_total = strip_tags($Qtotals->value('text'));
        } elseif ($Qtotals->value('class') == 'ot_shipping') {
          $shipping_title = strip_tags($Qtotals->value('title'));

          if (substr($shipping_title, -1) == ':') {
            $shipping_title = substr($shipping_title, 0, -1);
          }
        }
      }

      $Qstatus = $OSCOM_Db->prepare('select orders_status_name from :table_orders_status where orders_status_id = :orders_status_id and language_id = :language_id');
      $Qstatus->bindInt(':orders_status_id', $Qorder->valueInt('orders_status'));
      $Qstatus->bindInt(':language_id', $OSCOM_Language->getId());
      $Qstatus->execute();

      $this->info = array('currency' => $Qorder->value('currency'),
                          'currency_value' => $Qorder->valueDecimal('currency_value'),
                          'payment_method' => $Qorder->value('payment_method'),
                          'cc_type' => $Qorder->value('cc_type'),
                          'cc_owner' => $Qorder->value('cc_owner'),
                          'cc_number' => $Qorder->value('cc_number'),
                          'cc_expires' => $Qorder->value('cc_expires'),
                          'date_purchased' => $Qorder->value('date_purchased'),
                          'orders_status' => $Qstatus->value('orders_status_name'),
                          'last_modified' => $Qorder->value('last_modified'),
                          'total' => $order_total,
                          'shipping_method' => $shipping_title);

      $this->customer = array('id' => $Qorder->valueInt('customers_id'),
                              'name' => $Qorder->value('customers_name'),
                              'company' => $Qorder->value('customers_company'),
                              'street_address' => $Qorder->value('customers_street_address'),
                              'suburb' => $Qorder->value('customers_suburb'),
                              'city' => $Qorder->value('customers_city'),
                              'postcode' => $Qorder->value('customers_postcode'),
                              'state' => $Qorder->value('customers_state'),
                              'country' => array('title' => $Qorder->value('customers_country')),
                              'format_id' => $Qorder->valueInt('customers_address_format_id'),
                              'telephone' => $Qorder->value('customers_telephone'),
                              'email_address' => $Qorder->value('customers_email_address'));

      $this->delivery = array('name' => $Qorder->value('delivery_name'),
                              'company' => $Qorder->value('delivery_company'),
                              'street_address' => $Qorder->value('delivery_street_address'),
                              'suburb' => $Qorder->value('delivery_suburb'),
                              'city' => $Qorder->value('delivery_city'),
                              'postcode' => $Qorder->value('delivery_postcode'),
                              'state' => $Qorder->value('delivery_state'),
                              'country' => array('title' => $Qorder->value('delivery_country')),
                              'format_id' => $Qorder->valueInt('delivery_address_format_id'));

      if (empty($this->delivery['name']) && empty($this->delivery['street_address'])) {
        $this->delivery = false;
      }

      $this->billing = array('name' => $Qorder->value('billing_name'),
                             'company' => $Qorder->value('billing_company'),
                             'street_address' => $Qorder->value('billing_street_address'),
                             'suburb' => $Qorder->value('billing_suburb'),
                             'city' => $Qorder->value('billing_city'),
                             'postcode' => $Qorder->value('billing_postcode'),
                             'state' => $Qorder->value('billing_state'),
                             'country' => array('title' => $Qorder->value('billing_country')),
                             'format_id' => $Qorder->valueInt('billing_address_format_id'));

      $index = 0;

      $Qproducts = $OSCOM_Db->prepare('select orders_products_id, products_id, products_name, products_model, products_price, products_tax, products_quantity, final_price from :table_orders_products where orders_id = :orders_id');
      $Qproducts->bindInt(':orders_id', $order_id);
      $Qproducts->execute();

      while ($Qproducts->fetch()) {
        $this->products[$index] = array('qty' => $Qproducts->valueInt('products_quantity'),
                                        'id' => $Qproducts->valueInt('products_id'),
                                        'name' => $Qproducts->value('products_name'),
                                        'model' => $Qproducts->value('products_model'),
                                        'tax' => $Qproducts->valueDecimal('products_tax'),
                                        'price' => $Qproducts->valueDecimal('products_price'),
                                        'final_price' => $Qproducts->valueDecimal('final_price'));

        $subindex = 0;

        $Qattributes = $OSCOM_Db->prepare('select products_options, products_options_values, options_values_price, price_prefix from :table_orders_products_attributes where orders_id = :orders_id and orders_products_id = :orders_products_id');
        $Qattributes->bindInt(':orders_id', $order_id);
        $Qattributes->bindInt(':orders_products_id', $Qproducts->valueInt('orders_products_id'));
        $Qattributes->execute();

        if ($Qattributes->fetch() !== false) {
          do {
            $this->products[$index]['attributes'][$subindex] = array('option' => $Qattributes->value('products_options'),
                                                                     'value' => $Qattributes->value('products_options_values'),
                                                                     'prefix' => $Qattributes->value('price_prefix'),
                                                                     'price' => $Qattributes->valueDecimal('options_values_price'));

            $subindex++;
          } while ($Qattributes->fetch());
        }

        $this->info['tax_groups']["{$this->products[$index]['tax']}"] = '1';

        $index++;
      }
    }

    function cart() {
      global $currencies;

      $OSCOM_Db = Registry::get('Db');
      $OSCOM_Language = Registry::get('Language');

      $this->content_type = $_SESSION['cart']->get_content_type();

      if ( ($this->content_type != 'virtual') && ($_SESSION['sendto'] == false) ) {
        $_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
      }

      $customer_address = [
        'customers_firstname' => null,
        'customers_lastname' => null,
        'customers_telephone' => null,
        'customers_email_address' => null,
        'entry_company' => null,
        'entry_street_address' => null,
        'entry_suburb' => null,
        'entry_postcode' => null,
        'entry_city' => null,
        'entry_zone_id' => null,
        'zone_name' => null,
        'countries_id' => null,
        'countries_name' => null,
        'countries_iso_code_2' => null,
        'countries_iso_code_3' => null,
        'address_format_id' => 0,
        'entry_state' => null
      ];

      if (isset($_SESSION['customer_id'])) {
        $Qcustomer = $OSCOM_Db->prepare('select c.customers_firstname, c.customers_lastname, c.customers_telephone, c.customers_email_address, ab.entry_company, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, z.zone_name, co.countries_id, co.countries_name, co.countries_iso_code_2, co.countries_iso_code_3, co.address_format_id, ab.entry_state from :table_customers c, :table_address_book ab left join :table_zones z on (ab.entry_zone_id = z.zone_id) left join :table_countries co on (ab.entry_country_id = co.countries_id) where c.customers_id = :customers_id and c.customers_id = ab.customers_id and c.customers_default_address_id = ab.address_book_id');
        $Qcustomer->bindInt(':customers_id', $_SESSION['customer_id']);
        $Qcustomer->execute();

        $customer_address = $Qcustomer->toArray();
      }

      $shipping_address = array('entry_firstname' => null,
                                'entry_lastname' => null,
                                'entry_company' => null,
                                'entry_street_address' => null,
                                'entry_suburb' => null,
                                'entry_postcode' => null,
                                'entry_city' => null,
                                'entry_zone_id' => null,
                                'zone_name' => null,
                                'entry_country_id' => null,
                                'countries_id' => null,
                                'countries_name' => null,
                                'countries_iso_code_2' => null,
                                'countries_iso_code_3' => null,
                                'address_format_id' => 0,
                                'entry_state' => null);

      if (isset($_SESSION['sendto'])) {
        if (is_array($_SESSION['sendto']) && !empty($_SESSION['sendto'])) {
          $shipping_address = array('entry_firstname' => $_SESSION['sendto']['firstname'],
                                    'entry_lastname' => $_SESSION['sendto']['lastname'],
                                    'entry_company' => $_SESSION['sendto']['company'],
                                    'entry_street_address' => $_SESSION['sendto']['street_address'],
                                    'entry_suburb' => $_SESSION['sendto']['suburb'],
                                    'entry_postcode' => $_SESSION['sendto']['postcode'],
                                    'entry_city' => $_SESSION['sendto']['city'],
                                    'entry_zone_id' => $_SESSION['sendto']['zone_id'],
                                    'zone_name' => $_SESSION['sendto']['zone_name'],
                                    'entry_country_id' => $_SESSION['sendto']['country_id'],
                                    'countries_id' => $_SESSION['sendto']['country_id'],
                                    'countries_name' => $_SESSION['sendto']['country_name'],
                                    'countries_iso_code_2' => $_SESSION['sendto']['country_iso_code_2'],
                                    'countries_iso_code_3' => $_SESSION['sendto']['country_iso_code_3'],
                                    'address_format_id' => $_SESSION['sendto']['address_format_id'],
                                    'entry_state' => $_SESSION['sendto']['zone_name']);
        } elseif (is_numeric($_SESSION['sendto'])) {
          $Qaddress = $OSCOM_Db->prepare('select ab.entry_firstname, ab.entry_lastname, ab.entry_company, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, z.zone_name, ab.entry_country_id, c.countries_id, c.countries_name, c.countries_iso_code_2, c.countries_iso_code_3, c.address_format_id, ab.entry_state from :table_address_book ab left join :table_zones z on (ab.entry_zone_id = z.zone_id) left join :table_countries c on (ab.entry_country_id = c.countries_id) where ab.customers_id = :customers_id and ab.address_book_id = :address_book_id');
          $Qaddress->bindInt(':customers_id', $_SESSION['customer_id']);
          $Qaddress->bindInt(':address_book_id', $_SESSION['sendto']);
          $Qaddress->execute();

          $shipping_address = $Qaddress->toArray();
        }
      }

      $billing_address = array('entry_firstname' => null,
                               'entry_lastname' => null,
                               'entry_company' => null,
                               'entry_street_address' => null,
                               'entry_suburb' => null,
                               'entry_postcode' => null,
                               'entry_city' => null,
                               'entry_zone_id' => null,
                               'zone_name' => null,
                               'entry_country_id' => null,
                               'countries_id' => null,
                               'countries_name' => null,
                               'countries_iso_code_2' => null,
                               'countries_iso_code_3' => null,
                               'address_format_id' => 0,
                               'entry_state' => null);

      if (isset($_SESSION['billto'])) {
        if (is_array($_SESSION['billto']) && !empty($_SESSION['billto'])) {
          $billing_address = array('entry_firstname' => $_SESSION['billto']['firstname'],
                                   'entry_lastname' => $_SESSION['billto']['lastname'],
                                   'entry_company' => $_SESSION['billto']['company'],
                                   'entry_street_address' => $_SESSION['billto']['street_address'],
                                   'entry_suburb' => $_SESSION['billto']['suburb'],
                                   'entry_postcode' => $_SESSION['billto']['postcode'],
                                   'entry_city' => $_SESSION['billto']['city'],
                                   'entry_zone_id' => $_SESSION['billto']['zone_id'],
                                   'zone_name' => $_SESSION['billto']['zone_name'],
                                   'entry_country_id' => $_SESSION['billto']['country_id'],
                                   'countries_id' => $_SESSION['billto']['country_id'],
                                   'countries_name' => $_SESSION['billto']['country_name'],
                                   'countries_iso_code_2' => $_SESSION['billto']['country_iso_code_2'],
                                   'countries_iso_code_3' => $_SESSION['billto']['country_iso_code_3'],
                                   'address_format_id' => $_SESSION['billto']['address_format_id'],
                                   'entry_state' => $_SESSION['billto']['zone_name']);
        } elseif (is_numeric($_SESSION['billto'])) {
          $Qaddress = $OSCOM_Db->prepare('select ab.entry_firstname, ab.entry_lastname, ab.entry_company, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, z.zone_name, ab.entry_country_id, c.countries_id, c.countries_name, c.countries_iso_code_2, c.countries_iso_code_3, c.address_format_id, ab.entry_state from :table_address_book ab left join :table_zones z on (ab.entry_zone_id = z.zone_id) left join :table_countries c on (ab.entry_country_id = c.countries_id) where ab.customers_id = :customers_id and ab.address_book_id = :address_book_id');
          $Qaddress->bindInt(':customers_id', $_SESSION['customer_id']);
          $Qaddress->bindInt(':address_book_id', $_SESSION['billto']);
          $Qaddress->execute();

          $billing_address = $Qaddress->toArray();
        }
      }

      if ($this->content_type == 'virtual') {
        $tax_address = array('entry_country_id' => $billing_address['entry_country_id'],
                             'entry_zone_id' => $billing_address['entry_zone_id']);
      } else {
        $tax_address = array('entry_country_id' => $shipping_address['entry_country_id'],
                             'entry_zone_id' => $shipping_address['entry_zone_id']);
      }

      $this->info = array('order_status' => DEFAULT_ORDERS_STATUS_ID,
                          'currency' => $_SESSION['currency'],
                          'currency_value' => $currencies->currencies[$_SESSION['currency']]['value'],
                          'payment_method' => isset($_SESSION['payment']) ? $_SESSION['payment'] : '',
                          'cc_type' => '',
                          'cc_owner' => '',
                          'cc_number' => '',
                          'cc_expires' => '',
                          'shipping_method' => isset($_SESSION['shipping']) ? $_SESSION['shipping']['title'] : '',
                          'shipping_cost' => isset($_SESSION['shipping']) ? $_SESSION['shipping']['cost'] : 0,
                          'subtotal' => 0,
                          'tax' => 0,
                          'tax_groups' => array(),
                          'comments' => (isset($_SESSION['comments']) && !empty($_SESSION['comments']) ? $_SESSION['comments'] : ''));

      if (isset($_SESSION['payment'])) {
        if (strpos($_SESSION['payment'], '\\') !== false) {
          $code = 'Payment_' . str_replace('\\', '_', $_SESSION['payment']);

          if (Registry::exists($code)) {
            $OSCOM_PM = Registry::get($code);
          }
        } elseif (isset($GLOBALS[$_SESSION['payment']]) && is_object($GLOBALS[$_SESSION['payment']])) {
          $OSCOM_PM = $GLOBALS[$_SESSION['payment']];
        }

        if (isset($OSCOM_PM)) {
          if (isset($OSCOM_PM->public_title)) {
            $this->info['payment_method'] = $OSCOM_PM->public_title;
          } else {
            $this->info['payment_method'] = $OSCOM_PM->title;
          }

          if ( isset($OSCOM_PM->order_status) && is_numeric($OSCOM_PM->order_status) && ($OSCOM_PM->order_status > 0) ) {
            $this->info['order_status'] = $OSCOM_PM->order_status;
          }
        }
      }

      $this->customer = array('firstname' => $customer_address['customers_firstname'],
                              'lastname' => $customer_address['customers_lastname'],
                              'company' => $customer_address['entry_company'],
                              'street_address' => $customer_address['entry_street_address'],
                              'suburb' => $customer_address['entry_suburb'],
                              'city' => $customer_address['entry_city'],
                              'postcode' => $customer_address['entry_postcode'],
                              'state' => ((tep_not_null($customer_address['entry_state'])) ? $customer_address['entry_state'] : $customer_address['zone_name']),
                              'zone_id' => $customer_address['entry_zone_id'],
                              'country' => array('id' => $customer_address['countries_id'], 'title' => $customer_address['countries_name'], 'iso_code_2' => $customer_address['countries_iso_code_2'], 'iso_code_3' => $customer_address['countries_iso_code_3']),
                              'format_id' => $customer_address['address_format_id'],
                              'telephone' => $customer_address['customers_telephone'],
                              'email_address' => $customer_address['customers_email_address']);

      $this->delivery = array('firstname' => $shipping_address['entry_firstname'],
                              'lastname' => $shipping_address['entry_lastname'],
                              'company' => $shipping_address['entry_company'],
                              'street_address' => $shipping_address['entry_street_address'],
                              'suburb' => $shipping_address['entry_suburb'],
                              'city' => $shipping_address['entry_city'],
                              'postcode' => $shipping_address['entry_postcode'],
                              'state' => ((tep_not_null($shipping_address['entry_state'])) ? $shipping_address['entry_state'] : $shipping_address['zone_name']),
                              'zone_id' => $shipping_address['entry_zone_id'],
                              'country' => array('id' => $shipping_address['countries_id'], 'title' => $shipping_address['countries_name'], 'iso_code_2' => $shipping_address['countries_iso_code_2'], 'iso_code_3' => $shipping_address['countries_iso_code_3']),
                              'country_id' => $shipping_address['entry_country_id'],
                              'format_id' => $shipping_address['address_format_id']);

      $this->billing = array('firstname' => $billing_address['entry_firstname'],
                             'lastname' => $billing_address['entry_lastname'],
                             'company' => $billing_address['entry_company'],
                             'street_address' => $billing_address['entry_street_address'],
                             'suburb' => $billing_address['entry_suburb'],
                             'city' => $billing_address['entry_city'],
                             'postcode' => $billing_address['entry_postcode'],
                             'state' => ((tep_not_null($billing_address['entry_state'])) ? $billing_address['entry_state'] : $billing_address['zone_name']),
                             'zone_id' => $billing_address['entry_zone_id'],
                             'country' => array('id' => $billing_address['countries_id'], 'title' => $billing_address['countries_name'], 'iso_code_2' => $billing_address['countries_iso_code_2'], 'iso_code_3' => $billing_address['countries_iso_code_3']),
                             'country_id' => $billing_address['entry_country_id'],
                             'format_id' => $billing_address['address_format_id']);

      $index = 0;
      $products = $_SESSION['cart']->get_products();
      for ($i=0, $n=sizeof($products); $i<$n; $i++) {
        $this->products[$index] = array('qty' => $products[$i]['quantity'],
                                        'name' => $products[$i]['name'],
                                        'model' => $products[$i]['model'],
                                        'tax' => tep_get_tax_rate($products[$i]['tax_class_id'], $tax_address['entry_country_id'], $tax_address['entry_zone_id']),
                                        'tax_description' => tep_get_tax_description($products[$i]['tax_class_id'], $tax_address['entry_country_id'], $tax_address['entry_zone_id']),
                                        'price' => $products[$i]['price'],
                                        'final_price' => $products[$i]['price'] + $_SESSION['cart']->attributes_price($products[$i]['id']),
                                        'weight' => $products[$i]['weight'],
                                        'id' => $products[$i]['id']);

        if ($products[$i]['attributes']) {
          $subindex = 0;
          foreach($products[$i]['attributes'] as $option => $value) {
            $Qattributes = $OSCOM_Db->prepare('select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from :table_products_options popt, :table_products_options_values poval, :table_products_attributes pa where pa.products_id = :products_id and pa.options_id = :options_id and pa.options_id = popt.products_options_id and pa.options_values_id = :options_values_id and pa.options_values_id = poval.products_options_values_id and popt.language_id = :language_id and popt.language_id = poval.language_id');
            $Qattributes->bindInt(':products_id', $products[$i]['id']);
            $Qattributes->bindInt(':options_id', $option);
            $Qattributes->bindInt(':options_values_id', $value);
            $Qattributes->bindInt(':language_id', $OSCOM_Language->getId());
            $Qattributes->execute();

            $this->products[$index]['attributes'][$subindex] = array('option' => $Qattributes->value('products_options_name'),
                                                                     'value' => $Qattributes->value('products_options_values_name'),
                                                                     'option_id' => $option,
                                                                     'value_id' => $value,
                                                                     'prefix' => $Qattributes->value('price_prefix'),
                                                                     'price' => $Qattributes->value('options_values_price'));

            $subindex++;
          }
        }

        $shown_price = $currencies->calculate_price($this->products[$index]['final_price'], $this->products[$index]['tax'], $this->products[$index]['qty']);
        $this->info['subtotal'] += $shown_price;

        $products_tax = $this->products[$index]['tax'];
        $products_tax_description = $this->products[$index]['tax_description'];
        if (DISPLAY_PRICE_WITH_TAX == 'true') {
          $this->info['tax'] += $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
          if (isset($this->info['tax_groups']["$products_tax_description"])) {
            $this->info['tax_groups']["$products_tax_description"] += $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
          } else {
            $this->info['tax_groups']["$products_tax_description"] = $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
          }
        } else {
          $this->info['tax'] += ($products_tax / 100) * $shown_price;
          if (isset($this->info['tax_groups']["$products_tax_description"])) {
            $this->info['tax_groups']["$products_tax_description"] += ($products_tax / 100) * $shown_price;
          } else {
            $this->info['tax_groups']["$products_tax_description"] = ($products_tax / 100) * $shown_price;
          }
        }

        $index++;
      }

      if (DISPLAY_PRICE_WITH_TAX == 'true') {
        $this->info['total'] = $this->info['subtotal'] + $this->info['shipping_cost'];
      } else {
        $this->info['total'] = $this->info['subtotal'] + $this->info['tax'] + $this->info['shipping_cost'];
      }
    }
  }
?>
