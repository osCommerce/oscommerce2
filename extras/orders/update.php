<?php
/*
  $Id: update.php,v 1.1 2002/04/08 01:15:19 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  if (!$HTTP_POST_VARS['DB_SERVER']) {
?>
<html>
<head>
<title>osCommerce 2.2-CVS Orders Update Script</title>
<style type=text/css><!--
  TD, P, BODY {
    font-family: Verdana, Arial, sans-serif;
    font-size: 14px;
    color: #000000;
  }
//--></style>
</head>
<body>
<p>
<b>osCommerce 2.2-CVS Orders Update Script</b>
<p>
This script updates inserts the order total information into the new
orders_total table, which takes advantage of the new order_total modules.
<p>
<form name="database" action="update.php" method="post">
<table border="0" cellspacing="2" cellpadding="2">
  <tr>
    <td colspan="2"><b>Database Server Information</b></td>
  </tr>
  <tr>
    <td>Server:</td>
    <td><input type="text" name="DB_SERVER"> <small>(eg, 192.168.0.1)</small></td>
  </tr>
  <tr>
    <td>Username:</td>
    <td><input type="text" name="DB_SERVER_USERNAME"> <small>(eg, root)</small></td>
  </tr>
  <tr>
    <td>Password:</td>
    <td><input type="text" name="DB_SERVER_PASSWORD"> <small>(eg, bee)</small></td>
  </tr>
  <tr>
    <td>Database:</td>
    <td><input type="text" name="DB_DATABASE"> <small>(eg, catalog)</small></td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  <tr>
    <td valign="top">orders_total Table:</td>
    <td><input type="text" name="OT_TABLE" value="orders_total"> <small>(eg, orders_total)</small><br><small>This table is dropped, created, then filled</small></td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2"><input type="checkbox" name="DISPLAY_PRICES_WITH_TAX"> <b>Display Prices With Tax Included</b><br><small>Should the tax be added to the SubTotal? (the tax amount is still displayed)</small></td>
  </tr>
  <tr>
    <td colspan="2"><input type="checkbox" name="DISPLAY_MULTIPLE_TAXES"> <b>Display Multiple Tax Groups</b><br><small>If more than one tax rate is used, display the individual values, or as one global tax value?</small></td>
  </tr>
  <tr>
    <td colspan="2"><input type="checkbox" name="DISPLAY_SHIPPING"> <b>Display No/Free Shipping Charges</b><br><small>Display the shipping value if it equals $0.00?</small></td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  <tr>
    <td>Sub-Total Text:</td>
    <td><input type="text" name="OT_SUBTOTAL" value="Sub-Total:"> <small>(eg, Sub-Total:)</small></td>
  </tr>
  <tr>
    <td>Tax Text:</td>
    <td><input type="text" name="OT_TAX" value="Tax:"> <small>(eg, Tax:)</small></td>
  </tr>
  <tr>
    <td>Multiple Tax Groups Text:</td>
    <td><input type="text" name="OT_TAX_MULTIPLE" value="Tax (%s):"> <small>(eg, Tax (16%):)</small></td>
  </tr>
  <tr>
    <td>Shipping Text:</td>
    <td><input type="text" name="OT_SHIPPING" value="Shipping:"> <small>(eg, Shipping:)</small></td>
  </tr>
  <tr>
    <td>Total Text:</td>
    <td><input type="text" name="OT_TOTAL" value="Total:"> <small>(eg, Total:)</small></td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><input type="submit" value="Submit"></td>
  </tr>
</table>
</form>
</body>
</html>
<?php
    exit;
  }

  function tep_db_connect($link = 'db_link') {
    global $HTTP_POST_VARS, $$link;

    $$link = mysql_connect($HTTP_POST_VARS['DB_SERVER'], $HTTP_POST_VARS['DB_SERVER_USERNAME'], $HTTP_POST_VARS['DB_SERVER_PASSWORD']);

    if ($$link) mysql_select_db($HTTP_POST_VARS['DB_DATABASE']);

    return $$link;
  }

  function tep_db_error($query, $errno, $error) { 
    die('<font color="#000000"><b>' . $errno . ' - ' . $error . '<br><br>' . $query . '<br><br><small><font color="#ff0000">[TEP STOP]</font></small><br><br></b></font>');
  }

  function tep_db_query($query, $link = 'db_link') {
    global $$link;

    $result = mysql_query($query, $$link) or tep_db_error($query, mysql_errno(), mysql_error());

    return $result;
  }

  function tep_db_fetch_array($db_query) {
    return mysql_fetch_array($db_query, MYSQL_ASSOC);
  }

  function tep_db_num_rows($db_query) {
    return mysql_num_rows($db_query);
  }

  function tep_currency_format($number, $calculate_currency_value = true, $currency_code = DEFAULT_CURRENCY, $value = '') {
    $currency_query = tep_db_query("select symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, value from currencies where code = '" . $currency_code . "'");
    $currency = tep_db_fetch_array($currency_query);

    if ($calculate_currency_value == true) {
      if (strlen($currency_code) == 3) {
        if ($value) {
          $rate = $value;
        } else {
          $rate = $currency['value'];
        }
      } else {
        $rate = 1;
      }
      $number2currency = $currency['symbol_left'] . number_format(($number * $rate), $currency['decimal_places'], $currency['decimal_point'], $currency['thousands_point']) . $currency['symbol_right'];
    } else {
      $number2currency = $currency['symbol_left'] . number_format($number, $currency['decimal_places'], $currency['decimal_point'], $currency['thousands_point']) . $currency['symbol_right'];
    }

    return $number2currency;
  }

  function tep_display_tax_value($value, $padding = TAX_DECIMAL_PLACES) {
    if (strpos($value, '.')) {
      $loop = true;
      while ($loop) {
        if (substr($value, -1) == '0') {
          $value = substr($value, 0, -1);
        } else {
          $loop = false;
          if (substr($value, -1) == '.') {
            $value = substr($value, 0, -1);
          }
        }
      }
    }

    if ($padding > 0) {
      if ($decimal_pos = strpos($value, '.')) {
        $decimals = strlen(substr($value, ($decimal_pos+1)));
        for ($i=$decimals; $i<$padding; $i++) {
          $value .= '0';
        }
      } else {
        $value .= '.';
        for ($i=0; $i<$padding; $i++) {
          $value .= '0';
        }
      }
    }

    return $value;
  }

  tep_db_connect() or die('Unable to connect to database server!');

  if (strlen($HTTP_POST_VARS['OT_TABLE']) > 0) {
    tep_db_query("drop table if exists " . $HTTP_POST_VARS['OT_TABLE']);
    tep_db_query("create table " . $HTTP_POST_VARS['OT_TABLE'] . " ( orders_total_id int unsigned not null auto_increment, orders_id int not null, title varchar(255) not null, text varchar(255) not null, value decimal(8,2) not null, class varchar(32) not null, sort_order int not null, primary key (orders_total_id), key idx_orders_total_orders_id (orders_id))");
  }

  $i = 0;
  $orders_query = tep_db_query("select orders_id, shipping_method, shipping_cost, currency, currency_value from orders");
  while ($orders = tep_db_fetch_array($orders_query)) {
    $o = array();
    $total_cost = 0;

    $o['id'] = $orders['orders_id'];
    $o['shipping_method'] = $orders['shipping_method'];
    $o['shipping_cost'] = $orders['shipping_cost'];
    $o['currency'] = $orders['currency'];
    $o['currency_value'] = $orders['currency_value'];
    $o['tax'] = 0;

    $orders_products_query = tep_db_query("select final_price, products_tax, products_quantity from orders_products where orders_id = '" . $orders['orders_id'] . "'");
    while ($orders_products = tep_db_fetch_array($orders_products_query)) {
      $o['products'][$i]['final_price'] = $orders_products['final_price'];
      $o['products'][$i]['qty'] = $orders_products['products_quantity'];

      $o['products'][$i]['tax_groups']["{$orders_products['products_tax']}"] += $orders_products['products_tax']/100 * ($orders_products['final_price'] * $orders_products['products_quantity']);
      $o['tax'] += $orders_products['products_tax']/100 * ($orders_products['final_price'] * $orders_products['products_quantity']);

      $total_cost += ($o['products'][$i]['final_price'] * $o['products'][$i]['qty']);
    }

    if ($HTTP_POST_VARS['DISPLAY_PRICES_WITH_TAX'] == 'on') {
      $subtotal_text = tep_currency_format($total_cost + $o['tax'], true, $o['currency'], $o['currency_value']);
      $subtotal_value = $total_cost + $o['tax'];
    } else {
      $subtotal_text = tep_currency_format($total_cost, true, $o['currency'], $o['currency_value']);
      $subtotal_value = $total_cost;
    }

    tep_db_query("insert into " . $HTTP_POST_VARS['OT_TABLE'] . " (orders_total_id, orders_id, title, text, value, class, sort_order) values ('', '" . $o['id'] . "', '" . $HTTP_POST_VARS['OT_SUBTOTAL'] . "', '" . $subtotal_text . "', '" . $subtotal_value . "', 'ot_subtotal', '1')");

    if ($HTTP_POST_VARS['DISPLAY_MULTIPLE_TAXES'] == 'on') {
      @reset($o['products'][$i]['tax_groups']);
      while (@list($key, $value) = each($o['products'][$i]['tax_groups'])) {
        $tax_text = tep_currency_format($value, true, $o['currency'], $o['currency_value']);
        $tax_value = $value;
        tep_db_query("insert into " . $HTTP_POST_VARS['OT_TABLE'] . " (orders_total_id, orders_id, title, text, value, class, sort_order) values ('', '" . $o['id'] . "', '" . sprintf($HTTP_POST_VARS['OT_TAX_MULTIPLE'], tep_display_tax_value($key) . '%') . "', '" . $tax_text . "', '" . $tax_value . "', 'ot_tax', '2')");
      }
    } else {
      $tax_text = tep_currency_format($o['tax'], true, $o['currency'], $o['currency_value']);
      $tax_value = $o['tax'];
      tep_db_query("insert into " . $HTTP_POST_VARS['OT_TABLE'] . " (orders_total_id, orders_id, title, text, value, class, sort_order) values ('', '" . $o['id'] . "', '" . $HTTP_POST_VARS['OT_TAX'] . "', '" . $tax_text . "', '" . $tax_value . "', 'ot_tax', '2')");
    }

    if (strlen($o['shipping_method']) < 1) {
      $o['shipping_method'] = $HTTP_POST_VARS['OT_SHIPPING'];
    } else {
      $o['shipping_method'] .= ':';
    }
    if ($HTTP_POST_VARS['DISPLAY_SHIPPING'] == 'on') {
      $shipping_text = tep_currency_format($o['shipping_cost'], true, $o['currency'], $o['currency_value']);
      $shipping_value = $o['shipping_cost'];
      tep_db_query("insert into " . $HTTP_POST_VARS['OT_TABLE'] . " (orders_total_id, orders_id, title, text, value, class, sort_order) values ('', '" . $o['id'] . "', '" . $o['shipping_method'] . "', '" . $shipping_text . "', '" . $shipping_value . "', 'ot_shipping', '3')");
    } elseif ($o['shipping_cost'] > 0) {
      $shipping_text = tep_currency_format($o['shipping_cost'], true, $o['currency'], $o['currency_value']);
      $shipping_value = $o['shipping_cost'];
      tep_db_query("insert into " . $HTTP_POST_VARS['OT_TABLE'] . " (orders_total_id, orders_id, title, text, value, class, sort_order) values ('', '" . $o['id'] . "', '" . $o['shipping_method'] . "', '" . $shipping_text . "', '" . $shipping_value . "', 'ot_shipping', '3')");
    }

    $total_text = tep_currency_format($total_cost + $o['tax'] + $o['shipping_cost'], true, $o['currency'], $o['currency_value']);
    $total_value = $total_cost + $o['tax'] + $o['shipping_cost'];
    tep_db_query("insert into " . $HTTP_POST_VARS['OT_TABLE'] . " (orders_total_id, orders_id, title, text, value, class, sort_order) values ('', '" . $o['id'] . "', '" . $HTTP_POST_VARS['OT_TOTAL'] . "', '" . $total_text . "', '" . $total_value . "', 'ot_total', '4')");

    $i++;
  }
?>
Done!