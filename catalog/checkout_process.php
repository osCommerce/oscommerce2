<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\Mail;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  include('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot(array('page' => 'checkout_payment.php'));
    OSCOM::redirect('login.php');
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($_SESSION['cart']->count_contents() < 1) {
    OSCOM::redirect('shopping_cart.php');
  }

// if no shipping method has been selected, redirect the customer to the shipping method selection page
  if (!isset($_SESSION['shipping']) || !isset($_SESSION['sendto'])) {
    OSCOM::redirect('checkout_shipping.php');
  }

  if ( (tep_not_null(MODULE_PAYMENT_INSTALLED)) && (!isset($_SESSION['payment'])) ) {
    OSCOM::redirect('checkout_payment.php');
 }

// avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($_SESSION['cart']->cartID) && isset($_SESSION['cartID'])) {
    if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
      OSCOM::redirect('checkout_shipping.php');
    }
  }

  $OSCOM_Language->loadDefinitions('checkout_process');

// load selected payment module
  require('includes/classes/payment.php');
  $payment_modules = new payment($_SESSION['payment']);

// load the selected shipping module
  require('includes/classes/shipping.php');
  $shipping_modules = new shipping($_SESSION['shipping']);

  require('includes/classes/order.php');
  $order = new order;

// Stock Check
  $any_out_of_stock = false;
  if (STOCK_CHECK == 'true') {
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      if (tep_check_stock($order->products[$i]['id'], $order->products[$i]['qty'])) {
        $any_out_of_stock = true;
      }
    }
    // Out of Stock
    if ( (STOCK_ALLOW_CHECKOUT != 'true') && ($any_out_of_stock == true) ) {
      OSCOM::redirect('shopping_cart.php');
    }
  }

  $payment_modules->update_status();

  if (strpos($payment_modules->selected_module, '\\') !== false) {
    $code = 'Payment_' . str_replace('\\', '_', $payment_modules->selected_module);

    if (Registry::exists($code)) {
      $OSCOM_PM = Registry::get($code);
    }
  } elseif (isset($GLOBALS[$_SESSION['payment']]) && is_object($GLOBALS[$_SESSION['payment']])) {
    $OSCOM_PM = $GLOBALS[$_SESSION['payment']];
  }

  if ( !isset($OSCOM_PM) || ($payment_modules->selected_module != $_SESSION['payment']) || ($OSCOM_PM->enabled == false) ) {
    OSCOM::redirect('checkout_payment.php', 'error_message=' . urlencode(OSCOM::getDef('error_no_payment_module_selected')));
  }

  require('includes/classes/order_total.php');
  $order_total_modules = new order_total;

  $order_totals = $order_total_modules->process();

// load the before_process function from the payment modules
  $payment_modules->before_process();

  $sql_data_array = array('customers_id' => $_SESSION['customer_id'],
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
                          'delivery_name' => trim($order->delivery['firstname'] . ' ' . $order->delivery['lastname']),
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

  $OSCOM_Db->save('orders', $sql_data_array);
  $insert_id = $OSCOM_Db->lastInsertId();

  for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
    $sql_data_array = array('orders_id' => $insert_id,
                            'title' => $order_totals[$i]['title'],
                            'text' => $order_totals[$i]['text'],
                            'value' => $order_totals[$i]['value'],
                            'class' => $order_totals[$i]['code'],
                            'sort_order' => $order_totals[$i]['sort_order']);

    $OSCOM_Db->save('orders_total', $sql_data_array);
  }

  $customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
  $sql_data_array = array('orders_id' => $insert_id,
                          'orders_status_id' => $order->info['order_status'],
                          'date_added' => 'now()',
                          'customer_notified' => $customer_notification,
                          'comments' => $order->info['comments']);

  $OSCOM_Db->save('orders_status_history', $sql_data_array);

// initialized for the email confirmation
  $products_ordered = '';

  for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
// Stock Update - Joao Correia
    if (STOCK_LIMITED == 'true') {
      if (DOWNLOAD_ENABLED == 'true') {
        $stock_query_sql = 'select p.products_quantity, pad.products_attributes_filename
                            from :table_products p
                            left join :table_products_attributes pa
                            on p.products_id = pa.products_id
                            left join :table_products_attributes_download pad
                            on pa.products_attributes_id = pad.products_attributes_id
                            where p.products_id = :products_id';

// Will work with only one option for downloadable products
// otherwise, we have to build the query dynamically with a loop
        $products_attributes = (isset($order->products[$i]['attributes'])) ? $order->products[$i]['attributes'] : '';
        if (is_array($products_attributes)) {
          $stock_query_sql .= ' and pa.options_id = :options_id and pa.options_values_id = :options_values_id';
        }

        $Qstock = $OSCOM_Db->prepare($stock_query_sql);
        $Qstock->bindInt(':products_id', tep_get_prid($order->products[$i]['id']));

        if (is_array($products_attributes)) {
          $Qstock->bindInt(':options_id', $products_attributes[0]['option_id']);
          $Qstock->bindInt(':options_values_id', $products_attributes[0]['value_id']);
        }

        $Qstock->execute();
      } else {
        $Qstock = $OSCOM_Db->prepare('select products_quantity from :table_products where products_id = :products_id');
        $Qstock->bindInt(':products_id', tep_get_prid($order->products[$i]['id']));
        $Qstock->execute();
      }

      if ($Qstock->fetch() !== false) {
// do not decrement quantities if products_attributes_filename exists
        if ((DOWNLOAD_ENABLED != 'true') || tep_not_null($Qstock->value('products_attributes_filename'))) {
          $stock_left = $Qstock->valueInt('products_quantity') - $order->products[$i]['qty'];
        } else {
          $stock_left = $Qstock->valueInt('products_quantity');
        }

        if ($stock_left != $Qstock->valueInt('products_quantity')) {
          $OSCOM_Db->save('products', ['products_quantity' => (int)$stock_left], ['products_id' => tep_get_prid($order->products[$i]['id'])]);
        }

        if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
         $OSCOM_Db->save('products', ['products_status' => '0'], ['products_id' => tep_get_prid($order->products[$i]['id'])]);
        }
      }
    }

// Update products_ordered (for bestsellers list)
    $Qupdate = $OSCOM_Db->prepare('update :table_products set products_ordered = products_ordered + :products_ordered where products_id = :products_id');
    $Qupdate->bindInt(':products_ordered', $order->products[$i]['qty']);
    $Qupdate->bindInt(':products_id', tep_get_prid($order->products[$i]['id']));
    $Qupdate->execute();

    $sql_data_array = array('orders_id' => $insert_id,
                            'products_id' => tep_get_prid($order->products[$i]['id']),
                            'products_model' => $order->products[$i]['model'],
                            'products_name' => $order->products[$i]['name'],
                            'products_price' => $order->products[$i]['price'],
                            'final_price' => $order->products[$i]['final_price'],
                            'products_tax' => $order->products[$i]['tax'],
                            'products_quantity' => $order->products[$i]['qty']);

    $OSCOM_Db->save('orders_products', $sql_data_array);
    $order_products_id = $OSCOM_Db->lastInsertId();

//------insert customer choosen option to order--------
    $attributes_exist = '0';
    $products_ordered_attributes = '';
    if (isset($order->products[$i]['attributes'])) {
      $attributes_exist = '1';
      for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
        if (DOWNLOAD_ENABLED == 'true') {
          $attributes_query = 'select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount, pad.products_attributes_filename
                               from :table_products_options popt, :table_products_options_values poval, :table_products_attributes pa
                               left join :table_products_attributes_download pad on pa.products_attributes_id = pad.products_attributes_id
                               where pa.products_id = :products_id
                               and pa.options_id = :options_id
                               and pa.options_id = popt.products_options_id
                               and pa.options_values_id = :options_values_id
                               and pa.options_values_id = poval.products_options_values_id
                               and popt.language_id = :language_id
                               and popt.language_id = poval.language_id';
        } else {
          $attributes_query = 'select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                               from :table_products_options popt, :table_products_options_values poval, :table_products_attributes pa
                               where pa.products_id = :products_id
                               and pa.options_id = :options_id
                               and pa.options_id = popt.products_options_id
                               and pa.options_values_id = :options_values_id
                               and pa.options_values_id = poval.products_options_values_id
                               and popt.language_id = :language_id
                               and popt.language_id = poval.language_id';
        }

        $Qattributes = $OSCOM_Db->prepare($attributes_query);
        $Qattributes->bindInt(':products_id', $order->products[$i]['id']);
        $Qattributes->bindInt(':options_id', $order->products[$i]['attributes'][$j]['option_id']);
        $Qattributes->bindInt(':options_values_id', $order->products[$i]['attributes'][$j]['value_id']);
        $Qattributes->bindInt(':language_id', $OSCOM_Language->getId());
        $Qattributes->execute();

        $sql_data_array = array('orders_id' => $insert_id,
                                'orders_products_id' => $order_products_id,
                                'products_options' => $Qattributes->value('products_options_name'),
                                'products_options_values' => $Qattributes->value('products_options_values_name'),
                                'options_values_price' => $Qattributes->value('options_values_price'),
                                'price_prefix' => $Qattributes->value('price_prefix'));

        $OSCOM_Db->save('orders_products_attributes', $sql_data_array);

        if ((DOWNLOAD_ENABLED == 'true') && $Qattributes->hasValue('products_attributes_filename') && tep_not_null($Qattributes->value('products_attributes_filename'))) {
          $sql_data_array = array('orders_id' => $insert_id,
                                  'orders_products_id' => $order_products_id,
                                  'orders_products_filename' => $Qattributes->value('products_attributes_filename'),
                                  'download_maxdays' => $Qattributes->value('products_attributes_maxdays'),
                                  'download_count' => $Qattributes->value('products_attributes_maxcount'));

          $OSCOM_Db->save('orders_products_download', $sql_data_array);
        }

        $products_ordered_attributes .= "\n\t" . $Qattributes->value('products_options_name') . ' ' . $Qattributes->value('products_options_values_name');
      }
    }
//------insert customer choosen option eof ----
    $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
  }

// lets start with the email confirmation
  $email_order = STORE_NAME . "\n" .
                 OSCOM::getDef('email_separator') . "\n" .
                 OSCOM::getDef('email_text_order_number') . ' ' . $insert_id . "\n" .
                 OSCOM::getDef('email_text_invoice_url') . ' ' . OSCOM::link('account_history_info.php', 'order_id=' . $insert_id, false) . "\n" .
                 OSCOM::getDef('email_text_date_ordered') . ' ' . strftime(OSCOM::getDef('date_format_long')) . "\n\n";
  if ($order->info['comments']) {
    $email_order .= HTML::outputProtected($order->info['comments']) . "\n\n";
  }
  $email_order .= OSCOM::getDef('email_text_products') . "\n" .
                  OSCOM::getDef('email_separator') . "\n" .
                  $products_ordered .
                  OSCOM::getDef('email_separator') . "\n";

  for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
    $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
  }

  if ($order->content_type != 'virtual') {
    $email_order .= "\n" . OSCOM::getDef('email_text_delivery_address') . "\n" .
                    OSCOM::getDef('email_separator') . "\n" .
                    tep_address_label($_SESSION['customer_id'], $_SESSION['sendto'], 0, '', "\n") . "\n";
  }

  $email_order .= "\n" . OSCOM::getDef('email_text_billing_address') . "\n" .
                  OSCOM::getDef('email_separator') . "\n" .
                  tep_address_label($_SESSION['customer_id'], $_SESSION['billto'], 0, '', "\n") . "\n\n";
  if (isset($OSCOM_PM)) {
    $email_order .= OSCOM::getDef('email_text_payment_method') . "\n" .
                    OSCOM::getDef('email_separator') . "\n";
    $email_order .= $order->info['payment_method'] . "\n\n";
    if (isset($OSCOM_PM->email_footer)) {
      $email_order .= $OSCOM_PM->email_footer . "\n\n";
    }
  }

  $orderEmail = new Mail($order->customer['email_address'], $order->customer['firstname'] . ' ' . $order->customer['lastname'], STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, OSCOM::getDef('email_text_subject'));
  $orderEmail->setBody($email_order);
  $orderEmail->send();

// send emails to other people
  if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
    $extraEmail = new Mail(SEND_EXTRA_ORDER_EMAILS_TO, null, STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, OSCOM::getDef('email_text_subject'));
    $extraEmail->setBody($email_order);
    $extraEmail->send();
  }

// load the after_process function from the payment modules
  $payment_modules->after_process();

  $_SESSION['cart']->reset(true);

// unregister session variables used during checkout
  unset($_SESSION['sendto']);
  unset($_SESSION['billto']);
  unset($_SESSION['shipping']);
  unset($_SESSION['payment']);
  unset($_SESSION['comments']);

  OSCOM::redirect('checkout_success.php');

  require('includes/application_bottom.php');
?>
