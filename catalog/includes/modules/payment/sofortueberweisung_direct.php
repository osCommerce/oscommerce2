<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2006 - 2007 Henri Schmidhuber (http://www.in-solution.de)
  Copyright (c) 2008 osCommerce

  Released under the GNU General Public License
*/

  class sofortueberweisung_direct {
    var $code, $title, $description, $enabled;

// class constructor
    function sofortueberweisung_direct() {
      global $order;

      $this->code = 'sofortueberweisung_direct';
      $this->title = MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_PREPARE_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_PREPARE_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      $this->email_footer = MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_EMAIL_FOOTER;

      $this->form_action_url = 'https://www.sofort-ueberweisung.de/payment.php';
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
      global $cart_Sofortueberweisung_Direct_ID;

      if (tep_session_is_registered('cart_Sofortueberweisung_Direct_ID')) {
        $order_id = substr($cart_Sofortueberweisung_Direct_ID, strpos($cart_Sofortueberweisung_Direct_ID, '-')+1);

        $check_query = tep_db_query('select orders_id from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '" limit 1');

        if (tep_db_num_rows($check_query) < 1) {
          tep_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . (int)$order_id . '"');

          tep_session_unregister('cart_Sofortueberweisung_Direct_ID');
        }
      }

      return array('id' => $this->code,
                   'module' => $this->public_title,
                   'fields' => array(array('title' => MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_DESCRIPTION_CHECKOUT_PAYMENT)));
    }

    function pre_confirmation_check() {
      global $cartID, $cart;

      // We need the cartID
      if (empty($cart->cartID)) {
        $cartID = $cart->cartID = $cart->generate_cart_id();
      }

      if (!tep_session_is_registered('cartID')) {
        tep_session_register('cartID');
      }
    }

    function confirmation() {
      global $cartID, $cart_Sofortueberweisung_Direct_ID, $customer_id, $languages_id, $order, $order_total_modules;

      $insert_order = false;

      if (tep_session_is_registered('cart_Sofortueberweisung_Direct_ID')) {
        $order_id = substr($cart_Sofortueberweisung_Direct_ID, strpos($cart_Sofortueberweisung_Direct_ID, '-')+1);

        $curr_check = tep_db_query("select currency from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
        $curr = tep_db_fetch_array($curr_check);

        if ( ($curr['currency'] != $order->info['currency']) || ($cartID != substr($cart_Sofortueberweisung_Direct_ID, 0, strlen($cartID))) ) {
          $check_query = tep_db_query('select orders_id from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '" limit 1');

          if (tep_db_num_rows($check_query) < 1) {
            tep_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . (int)$order_id . '"');
            tep_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . (int)$order_id . '"');
            tep_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '"');
            tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . (int)$order_id . '"');
            tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . ' where orders_id = "' . (int)$order_id . '"');
            tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . (int)$order_id . '"');
          }

          $insert_order = true;
        }
      } else {
        $insert_order = true;
      }

      if ($insert_order == true) {
        $order_totals = array();
        if (is_array($order_total_modules->modules)) {
          reset($order_total_modules->modules);
          while (list(, $value) = each($order_total_modules->modules)) {
            $class = substr($value, 0, strrpos($value, '.'));
            if ($GLOBALS[$class]->enabled) {
              for ($i=0, $n=sizeof($GLOBALS[$class]->output); $i<$n; $i++) {
                if (tep_not_null($GLOBALS[$class]->output[$i]['title']) && tep_not_null($GLOBALS[$class]->output[$i]['text'])) {
                  $order_totals[] = array('code' => $GLOBALS[$class]->code,
                                          'title' => $GLOBALS[$class]->output[$i]['title'],
                                          'text' => $GLOBALS[$class]->output[$i]['text'],
                                          'value' => $GLOBALS[$class]->output[$i]['value'],
                                          'sort_order' => $GLOBALS[$class]->sort_order);
                }
              }
            }
          }
        }

        $sql_data_array = array('customers_id' => $customer_id,
                                'customers_name' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
                                'customers_company' => $order->customer['company'],
                                'customers_street_address' => $order->customer['street_address'],
                                'customers_suburb' => $order->customer['suburb'],
                                'customers_city' => $order->customer['city'],
                                'customers_postcode' => $order->customer['postcode'],
                                'customers_state' => $order->customer['state'],
                                'customers_country' => $order->customer['country']['title'],
                                'customers_telephone' => $order->customer['telephone'],
                                'customers_email_address' => $order->customer['email_address'],
                                'customers_address_format_id' => $order->customer['format_id'],
                                'delivery_name' => $order->delivery['firstname'] . ' ' . $order->delivery['lastname'],
                                'delivery_company' => $order->delivery['company'],
                                'delivery_street_address' => $order->delivery['street_address'],
                                'delivery_suburb' => $order->delivery['suburb'],
                                'delivery_city' => $order->delivery['city'],
                                'delivery_postcode' => $order->delivery['postcode'],
                                'delivery_state' => $order->delivery['state'],
                                'delivery_country' => $order->delivery['country']['title'],
                                'delivery_address_format_id' => $order->delivery['format_id'],
                                'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'],
                                'billing_company' => $order->billing['company'],
                                'billing_street_address' => $order->billing['street_address'],
                                'billing_suburb' => $order->billing['suburb'],
                                'billing_city' => $order->billing['city'],
                                'billing_postcode' => $order->billing['postcode'],
                                'billing_state' => $order->billing['state'],
                                'billing_country' => $order->billing['country']['title'],
                                'billing_address_format_id' => $order->billing['format_id'],
                                'payment_method' => $order->info['payment_method'],
                                'cc_type' => $order->info['cc_type'],
                                'cc_owner' => $order->info['cc_owner'],
                                'cc_number' => $order->info['cc_number'],
                                'cc_expires' => $order->info['cc_expires'],
                                'date_purchased' => 'now()',
                                'orders_status' => $order->info['order_status'],
                                'currency' => $order->info['currency'],
                                'currency_value' => $order->info['currency_value']);

        tep_db_perform(TABLE_ORDERS, $sql_data_array);

        $insert_id = tep_db_insert_id();

        for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
          $sql_data_array = array('orders_id' => $insert_id,
                                  'title' => $order_totals[$i]['title'],
                                  'text' => $order_totals[$i]['text'],
                                  'value' => $order_totals[$i]['value'],
                                  'class' => $order_totals[$i]['code'],
                                  'sort_order' => $order_totals[$i]['sort_order']);

          tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
        }

        for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
          $sql_data_array = array('orders_id' => $insert_id,
                                  'products_id' => tep_get_prid($order->products[$i]['id']),
                                  'products_model' => $order->products[$i]['model'],
                                  'products_name' => $order->products[$i]['name'],
                                  'products_price' => $order->products[$i]['price'],
                                  'final_price' => $order->products[$i]['final_price'],
                                  'products_tax' => $order->products[$i]['tax'],
                                  'products_quantity' => $order->products[$i]['qty']);

          tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);

          $order_products_id = tep_db_insert_id();

          $attributes_exist = '0';
          if (isset($order->products[$i]['attributes'])) {
            $attributes_exist = '1';
            for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
              if (DOWNLOAD_ENABLED == 'true') {
                $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
                                     from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                     left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                     on pa.products_attributes_id=pad.products_attributes_id
                                     where pa.products_id = '" . $order->products[$i]['id'] . "'
                                     and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
                                     and pa.options_id = popt.products_options_id
                                     and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                                     and pa.options_values_id = poval.products_options_values_id
                                     and popt.language_id = '" . $languages_id . "'
                                     and poval.language_id = '" . $languages_id . "'";
                $attributes = tep_db_query($attributes_query);
              } else {
                $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $order->products[$i]['id'] . "' and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "' and poval.language_id = '" . $languages_id . "'");
              }
              $attributes_values = tep_db_fetch_array($attributes);

              $sql_data_array = array('orders_id' => $insert_id,
                                      'orders_products_id' => $order_products_id,
                                      'products_options' => $attributes_values['products_options_name'],
                                      'products_options_values' => $attributes_values['products_options_values_name'],
                                      'options_values_price' => $attributes_values['options_values_price'],
                                      'price_prefix' => $attributes_values['price_prefix']);

              tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

              if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
                $sql_data_array = array('orders_id' => $insert_id,
                                        'orders_products_id' => $order_products_id,
                                        'orders_products_filename' => $attributes_values['products_attributes_filename'],
                                        'download_maxdays' => $attributes_values['products_attributes_maxdays'],
                                        'download_count' => $attributes_values['products_attributes_maxcount']);

                tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
              }
            }
          }
        }

        $cart_Sofortueberweisung_Direct_ID = $cartID . '-' . $insert_id;
        tep_session_register('cart_Sofortueberweisung_Direct_ID');
      }

      return array('title' => MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_DESCRIPTION_CHECKOUT_CONFIRMATION);
    }

    function process_button() {
      global $order, $cart, $customer_id, $currencies, $cart_Sofortueberweisung_Direct_ID;

      $order_id = substr($cart_Sofortueberweisung_Direct_ID, strpos($cart_Sofortueberweisung_Direct_ID, '-')+1);

      $parameter= array();
      $parameter['kdnr']	= MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_KDNR;  // Repräsentiert Ihre Kundennummer bei der Sofortüberweisung
      $parameter['projekt'] = MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_PROJEKT;  // Die verantwortliche Projektnummer bei der Sofortüberweisung, zu der die Zahlung gehört
      $parameter['betrag'] = number_format($order->info['total'] * $currencies->get_value('EUR'), 2, '.','');  // Beziffert den Zahlungsbetrag, der an Sie übermittelt werden soll
      $vzweck1 = str_replace('{{orderid}}', $order_id, MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_V_ZWECK_1);
      $vzweck2 = str_replace('{{orderid}}', $order_id, MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_V_ZWECK_2);

      $vzweck1 = str_replace('{{order_date}}', strftime(DATE_FORMAT_SHORT), $vzweck1);
      $vzweck2 = str_replace('{{order_date}}', strftime(DATE_FORMAT_SHORT), $vzweck2);

      $vzweck1 = str_replace('{{customer_id}}', $customer_id, $vzweck1);
      $vzweck2 = str_replace('{{customer_id}}', $customer_id, $vzweck2);

      $vzweck1 = str_replace('{{customer_name}}', $order->customer['firstname'] . ' ' . $order->customer['lastname'], $vzweck1);
      $vzweck2 = str_replace('{{customer_name}}', $order->customer['firstname'] . ' ' . $order->customer['lastname'], $vzweck2);

      $vzweck1 = str_replace('{{customer_company}}', $order->customer['company'], $vzweck1);
      $vzweck2 = str_replace('{{customer_company}}', $order->customer['company'], $vzweck2);

      $vzweck1 = str_replace('{{customer_email}}', $order->customer['email_address'], $vzweck1);
      $vzweck2 = str_replace('{{customer_email}}', $order->customer['email_address'], $vzweck2);

      // Kürzen auf 27 Zeichen
      $vzweck1 = substr($vzweck1, 0, 27);
      $vzweck2 = substr($vzweck2, 0, 27);

      $parameter['v_zweck_1'] = tep_output_string($vzweck1);  // Definieren Sie hier Ihre Verwendungszwecke
      $parameter['v_zweck_2'] = tep_output_string($vzweck2);  // Definieren Sie hier Ihre Verwendungszwecke

      $parameter['kunden_var_0'] = tep_output_string($order_id);  // Eindeutige Identifikation der Zahlung, z.B. Session ID oder Auftragsnummer.
      $parameter['kunden_var_1'] = tep_output_string($customer_id);
      $parameter['kunden_var_2'] = tep_output_string(tep_session_id());
      $parameter['kunden_var_3'] = tep_output_string($cart->cartID);
      $parameter['kunden_var_4'] = '';
      $parameter['kunden_var_5'] = '';
      // $parameter['Partner'] = '';

      if (strlen(MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_INPUT_PASSWORT) > 0) {
        $tmparray = array(
          $parameter['betrag'],
          $parameter['v_zweck_1'],
          $parameter['v_zweck_2'],
          '', // von_konto_inhaber
          '', // von_konto_nr
          '', // von_konto_blz
          $parameter['kunden_var_0'],
          $parameter['kunden_var_1'],
          $parameter['kunden_var_2'],
          $parameter['kunden_var_3'],
          $parameter['kunden_var_4'],
          $parameter['kunden_var_5'],
          MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_INPUT_PASSWORT);
        $parameter['key'] = md5(implode("|", $tmparray));
      }
      $process_button_string = '';
      reset($parameter);
      while (list($key, $value) = each($parameter)) {
        $process_button_string .= tep_draw_hidden_field($key, $value). "\n";
      }

      return $process_button_string;
    }

    function before_process() {
      global $HTTP_GET_VARS, $customer_id, $order, $order_totals, $sendto, $billto, $languages_id, $payment, $currencies, $cart, $cart_Sofortueberweisung_Direct_ID;
      global $$payment;

      $md5var4 = md5($HTTP_GET_VARS['sovar3'] . MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_CNT_PASSWORT);
      // Statusupdate nur wenn keine Cartänderung vorgenommen
      $order_total_integer = number_format($order->info['total'] * $currencies->get_value('EUR'), 2, '.','')*100;
      if ($order_total_integer < 1) {
        $order_total_integer = '000';
      } elseif ($order_total_integer < 10) {
        $order_total_integer = '00' . $order_total_integer;
      } elseif ($order_total_integer < 100) {
        $order_total_integer = '0' . $order_total_integer;
      }

      $order_id = substr($cart_Sofortueberweisung_Direct_ID, strpos($cart_Sofortueberweisung_Direct_ID, '-')+1);

      $check_query = tep_db_query("select orders_status from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
      if (tep_db_num_rows($check_query)) {
        $check = tep_db_fetch_array($check_query);

        if ($check['orders_status'] == MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_PREPARE_ORDER_STATUS_ID) {
          $sql_data_array = array('orders_id' => $order_id,
                                  'orders_status_id' => MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_PREPARE_ORDER_STATUS_ID,
                                  'date_added' => 'now()',
                                  'customer_notified' => '0',
                                  'comments' => '');

          if (($md5var4 == $HTTP_GET_VARS['sovar4']) && ((int)$HTTP_GET_VARS['betrag_integer'] == (int)$order_total_integer)) {
            $sql_data_array['comments'] = 'Zahlung durch Sofortüberweisung Weiter-Button/Weiterleitung bestätigt!';
          } else {
            $sql_data_array['comments'] = MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_CHECK_ERROR . '\n' . ($HTTP_GET_VARS['betrag_integer']/100) . '!=' . ($order_total_integer/100);
          }

          if (MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_STORE_TRANSACTION_DETAILS == 'True') {
            $sql_data_array['comments'] = (!empty($sql_data_array['comments']) ? $sql_data_array['comments'] . "\n\n" : '') . serialize($HTTP_GET_VARS) . "\n" . serialize($HTTP_POST_VARS);
          }

          tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        }
      }

      tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . (MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID) . "', last_modified = now() where orders_id = '" . (int)$order_id . "'");

      $sql_data_array = array('orders_id' => $order_id,
                              'orders_status_id' => (MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID),
                              'date_added' => 'now()',
                              'customer_notified' => (SEND_EMAILS == 'true') ? '1' : '0',
                              'comments' => $order->info['comments']);

      tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

// initialized for the email confirmation
      $products_ordered = '';
      $subtotal = 0;
      $total_tax = 0;

      for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
// Stock Update - Joao Correia
        if (STOCK_LIMITED == 'true') {
          if (DOWNLOAD_ENABLED == 'true') {
            $stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
                                FROM " . TABLE_PRODUCTS . " p
                                LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                ON p.products_id=pa.products_id
                                LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                ON pa.products_attributes_id=pad.products_attributes_id
                                WHERE p.products_id = '" . tep_get_prid($order->products[$i]['id']) . "'";
// Will work with only one option for downloadable products
// otherwise, we have to build the query dynamically with a loop
            $products_attributes = $order->products[$i]['attributes'];
            if (is_array($products_attributes)) {
              $stock_query_raw .= " AND pa.options_id = '" . $products_attributes[0]['option_id'] . "' AND pa.options_values_id = '" . $products_attributes[0]['value_id'] . "'";
            }
            $stock_query = tep_db_query($stock_query_raw);
          } else {
            $stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
          }
          if (tep_db_num_rows($stock_query) > 0) {
            $stock_values = tep_db_fetch_array($stock_query);
// do not decrement quantities if products_attributes_filename exists
            if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename'])) {
              $stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
            } else {
              $stock_left = $stock_values['products_quantity'];
            }
            tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . $stock_left . "' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
            if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
              tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
            }
          }
        }

// Update products_ordered (for bestsellers list)
        tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");

//------insert customer choosen option to order--------
        $attributes_exist = '0';
        $products_ordered_attributes = '';
        if (isset($order->products[$i]['attributes'])) {
          $attributes_exist = '1';
          for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
            if (DOWNLOAD_ENABLED == 'true') {
              $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
                                   from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                   left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                   on pa.products_attributes_id=pad.products_attributes_id
                                   where pa.products_id = '" . $order->products[$i]['id'] . "'
                                   and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
                                   and pa.options_id = popt.products_options_id
                                   and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                                   and pa.options_values_id = poval.products_options_values_id
                                   and popt.language_id = '" . $languages_id . "'
                                   and poval.language_id = '" . $languages_id . "'";
              $attributes = tep_db_query($attributes_query);
            } else {
              $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $order->products[$i]['id'] . "' and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "' and poval.language_id = '" . $languages_id . "'");
            }
            $attributes_values = tep_db_fetch_array($attributes);

            $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
          }
        }
//------insert customer choosen option eof ----
        $total_weight += ($order->products[$i]['qty'] * $order->products[$i]['weight']);
        $total_tax += tep_calculate_tax($total_products_price, $products_tax) * $order->products[$i]['qty'];
        $total_cost += $total_products_price;

        $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
      }

// lets start with the email confirmation
      $email_order = STORE_NAME . "\n" .
                     EMAIL_SEPARATOR . "\n" .
                     EMAIL_TEXT_ORDER_NUMBER . ' ' . $order_id . "\n" .
                     EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $order_id, 'SSL', false) . "\n" .
                     EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n\n";
      if ($order->info['comments']) {
        $email_order .= tep_db_output($order->info['comments']) . "\n\n";
      }
      $email_order .= EMAIL_TEXT_PRODUCTS . "\n" .
                      EMAIL_SEPARATOR . "\n" .
                      $products_ordered .
                      EMAIL_SEPARATOR . "\n";

      for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
        $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
      }

      if ($order->content_type != 'virtual') {
        $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
                        EMAIL_SEPARATOR . "\n" .
                        tep_address_label($customer_id, $sendto, 0, '', "\n") . "\n";
      }

      $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
                      EMAIL_SEPARATOR . "\n" .
                      tep_address_label($customer_id, $billto, 0, '', "\n") . "\n\n";

      if (is_object($$payment)) {
        $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
                        EMAIL_SEPARATOR . "\n";
        $payment_class = $$payment;
        $email_order .= $payment_class->title . "\n\n";
        if ($payment_class->email_footer) {
          $email_order .= $payment_class->email_footer . "\n\n";
        }
      }

      tep_mail($order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

// send emails to other people
      if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
        tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
      }

// load the after_process function from the payment modules
      $this->after_process();

      $cart->reset(true);

// unregister session variables used during checkout
      tep_session_unregister('sendto');
      tep_session_unregister('billto');
      tep_session_unregister('shipping');
      tep_session_unregister('payment');
      tep_session_unregister('comments');

      tep_session_unregister('cart_Sofortueberweisung_Direct_ID');

      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
    }

    function after_process() {
       return false;
    }

    function get_error() {
      global $HTTP_GET_VARS;

      $error = array('title' => MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_ERROR_HEADING,
                     'error' => MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_ERROR_MESSAGE);

      return $error;
    }


    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      global $HTTP_GET_VARS;

      $kdnr = (isset($HTTP_GET_VARS['kdnr']) && !empty($HTTP_GET_VARS['kdnr'])) ? tep_db_prepare_input($HTTP_GET_VARS['kdnr']) : '10000';
      $projekt = (isset($HTTP_GET_VARS['projekt']) && !empty($HTTP_GET_VARS['projekt'])) ? tep_db_prepare_input($HTTP_GET_VARS['projekt']) : '500000';
      $input_passwort = (isset($HTTP_GET_VARS['input_passwort']) && !empty($HTTP_GET_VARS['input_passwort'])) ? tep_db_prepare_input($HTTP_GET_VARS['input_passwort']) : '';
      $bna_passwort = (isset($HTTP_GET_VARS['bna_passwort']) && !empty($HTTP_GET_VARS['bna_passwort'])) ? tep_db_prepare_input($HTTP_GET_VARS['bna_passwort']) : '';
      $cnt_passwort = (isset($HTTP_GET_VARS['cnt_passwort']) && !empty($HTTP_GET_VARS['cnt_passwort'])) ? tep_db_prepare_input($HTTP_GET_VARS['cnt_passwort']) : '';

      $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Sofortüberweisung Vorbereitung' limit 1");

      if (tep_db_num_rows($check_query) < 1) {
        $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
        $status = tep_db_fetch_array($status_query);

        $status_id = $status['status_id']+1;

        $languages = tep_get_languages();

        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $languages[$i]['id'] . "', 'Sofortüberweisung Vorbereitung')");
        }

        $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
        if (tep_db_num_rows($flags_query) == 1) {
          tep_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 0 and downloads_flag = 0 where orders_status_id = '" . $status_id . "'");
        }
      } else {
        $check = tep_db_fetch_array($check_query);

        $status_id = $check['orders_status_id'];
      }

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Sofortüberweisung direkter Modus aktivieren', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_STATUS', 'True', 'Bezahlung per Sofortüberweisung acceptieren?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now());");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Kundennummer:', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_KDNR', '" . (int)$kdnr . "', 'Ihre Kundennummer bei der Sofortüberweisung', '6', '1', now());");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Projektnummer:', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_PROJEKT', '" . (int)$projekt . "', 'Die verantwortliche Projektnummer bei der Sofortüberweisung, zu der die Zahlung gehört', '6', '1', now());");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Input-Passwort:', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_INPUT_PASSWORT', '" . tep_db_input($input_passwort) . "', 'Das Input-Passwort (unter Nicht änderbare Parameter / Input-Passwort)', '6', '1', now());");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Benachrichtigung-Passwort:', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_BNA_PASSWORT', '" . tep_db_input($bna_passwort) . "', 'Das Benachrichtigung-Passwort (unter Benachrichtigungen festlegen)', '6', '1', now());");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Contentpasswort:', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_CNT_PASSWORT', '" . tep_db_input($cnt_passwort) . "', 'Das Contentpasswort (unter Content-Passwort)', '6', '1', now());");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Preparing Order Status', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_PREPARE_ORDER_STATUS_ID', '" . (int)$status_id . "', 'Order Status vor Eingang Bestellung', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ORDER_STATUS_ID', '0', 'Order Status nach Eingang Bestellung', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Store Transactiondetails', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_STORE_TRANSACTION_DETAILS', 'False', 'Transactionsdetails bei Benachrichtigung in das Kommentarfeld speichern (zum debuggen, ist für Kunden via Konto sichtbar)', '6', '2', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now());");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_STATUS', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_KDNR', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_PROJEKT', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_INPUT_PASSWORT', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_BNA_PASSWORT', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_CNT_PASSWORT', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_STORE_TRANSACTION_DETAILS', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ZONE', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_PREPARE_ORDER_STATUS_ID', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ORDER_STATUS_ID', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_SORT_ORDER');
    }
  }
?>
