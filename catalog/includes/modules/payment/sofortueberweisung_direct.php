<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2006 - 2007 Henri Schmidhuber (http://www.in-solution.de)
  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

  class sofortueberweisung_direct {
    var $code, $title, $description, $enabled;

// class constructor
    function sofortueberweisung_direct() {
      global $order;

      $this->code = 'sofortueberweisung_direct';
      $this->title = MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_STATUS == 'True') ? true : false);


      if (is_object($order)) $this->update_status();

      $this->email_footer = MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_EMAIL_FOOTER;
      $this->text_redirect = MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_REDIRECT;
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
      return array('id' => $this->code,
                   'module' => $this->title,
                   'fields' => array(array('title' => MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_DESCRIPTION_CHECKOUT_PAYMENT)));
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
      return array('title' => MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_DESCRIPTION_CHECKOUT_CONFIRMATION);
    }

    function process_button() {
     global $order, $cart, $customer_id, $insert_id, $currencies;

      // We need the cartID
      if (!tep_session_is_registered('cartID')) tep_session_register('cartID');
      if (empty($cart->cartID)) $cart->cartID = $cart->generate_cart_id();

      $parameter= array();
      $parameter['kdnr']	= MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_KDNR;  // Repräsentiert Ihre Kundennummer bei der Sofortüberweisung
      $parameter['projekt'] = MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_PROJEKT;  // Die verantwortliche Projektnummer bei der Sofortüberweisung, zu der die Zahlung gehört
      $parameter['betrag'] = number_format($order->info['total'] * $currencies->get_value('EUR'), 2, '.','');  // Beziffert den Zahlungsbetrag, der an Sie übermittelt werden soll
      $vzweck1 = str_replace('{{orderid}}', $insert_id, MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_V_ZWECK_1);
      $vzweck2 = str_replace('{{orderid}}', $insert_id, MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_V_ZWECK_2);

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

      $parameter['kunden_var_0'] = tep_output_string($insert_id);  // Eindeutige Identifikation der Zahlung, z.B. Session ID oder Auftragsnummer.
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
      foreach ($parameter as $key => $value) {
        $process_button_string .= tep_draw_hidden_field($key, $value). "\n";
      }
      return $process_button_string;
    }

    function before_process() {
      global $HTTP_GET_VARS, $order, $currencies;
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

      if (($md5var4 == $HTTP_GET_VARS['sovar4']) && ($HTTP_GET_VARS['betrag_integer'] == $order_total_integer)) {
        // we have an verified order
        if ( (int)MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ORDER_STATUS_ID > 0) {
          $this->order_status = MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ORDER_STATUS_ID;
          $order->info['order_status'] = MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ORDER_STATUS_ID;
        }
      } else {
        $order->info['comments'] .= "\n" . MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_CHECK_ERROR . '\n' . ($HTTP_GET_VARS['betrag_integer']/100) .'!=' . ($order_total_integer/100);
      }
      if (MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_STORE_TRANSACTION_DETAILS == 'True') {
        $order->info['comments'] .= "\n" . serialize($HTTP_GET_VARS);
      }

      return false;
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
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Sofortüberweisung direkter Modus aktivieren', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_STATUS', 'True', 'Bezahlung per Sofortüberweisung acceptieren?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now());");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Kundennummer:', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_KDNR', '10000', 'Ihre Kundennummer bei der Sofortüberweisung', '6', '1', now());");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Projektnummer:', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_PROJEKT', '500000', 'Die verantwortliche Projektnummer bei der Sofortüberweisung, zu der die Zahlung gehört', '6', '1', now());");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Input-Passwort:', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_INPUT_PASSWORT', 'abcdef', 'Das Input-Passwort (unter Nicht änderbare Parameter / Input-Passwort)', '6', '1', now());");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Contentpasswort:', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_CNT_PASSWORT', '123456', 'Das Contentpasswort (unter Content-Passwort)', '6', '1', now());");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_SORT_ORDER', '0.9', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ORDER_STATUS_ID', '0', 'Order Status nach Eingang Bestellung', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Store Transactiondetails', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_STORE_TRANSACTION_DETAILS', 'False', 'Transactionsdetails bei Benachrichtigung in das Kommentarfeld speichern (zum debuggen, ist für Kunden via Konto sichtbar)', '6', '2', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now());");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_STATUS', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ZONE', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_ORDER_STATUS_ID', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_SORT_ORDER', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_KDNR', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_PROJEKT', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_INPUT_PASSWORT', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_CNT_PASSWORT', 'MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_STORE_TRANSACTION_DETAILS');
    }
  }
?>
