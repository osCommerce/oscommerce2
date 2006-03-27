<?php
/*
  $Id: images.php,v 1.3 2001/12/01 20:17:18 hpdl Exp $

  The Exchange Project - Community Made Shopping!
  http://www.theexchangeproject.org

  Copyright (c) 2000,2001 The Exchange Project

  Released under the GNU General Public License
*/

  if (!$HTTP_POST_VARS['DB_SERVER']) {
?>
<html>
<head>
<title>The Exchange Project Preview Release 2.2 Database Update Script</title>
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
<b>The Exchange Project Preview Release 2.2 Database Update Script</b>
<p>
This script removes all hardcode occurences of 'images/' in the following tables:
<p>
categories.categories_image<br>
manufacturers.manufacturers_image<br>
products.products_image
<p>
<form name="database" action="<?php echo basename($PHP_SELF); ?>" method="post">
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

  function tep_db_connect() {
    global $db_link, $HTTP_POST_VARS;

    $db_link = mysql_connect($HTTP_POST_VARS['DB_SERVER'], $HTTP_POST_VARS['DB_SERVER_USERNAME'], $HTTP_POST_VARS['DB_SERVER_PASSWORD']);

    if ($db_link) mysql_select_db($HTTP_POST_VARS['DB_DATABASE']);

    return $db_link;
  }

  function tep_db_error ($query, $errno, $error) { 
    die('<font color="#000000"><b>' . $errno . ' - ' . $error . '<br><br>' . $query . '<br><br><small><font color="#ff0000">[TEP STOP]</font></small><br><br></b></font>');
  }

  function tep_db_query($db_query) {
    global $db_link;

    $result = mysql_query($db_query, $db_link) or tep_db_error($db_query, mysql_errno(), mysql_error());

    return $result;
  }

  function tep_db_fetch_array($db_query) {
    $result = mysql_fetch_array($db_query);

    return $result;
  }

  tep_db_connect() or die('Unable to connect to database server!');

// categories
  $categories_query = tep_db_query("select categories_id, categories_image from categories where left(categories_image, 7) = 'images/'");
  while ($categories = tep_db_fetch_array($categories_query)) {
    tep_db_query("update categories set categories_image = substring('" . $categories['categories_image'] . "', 8) where categories_id = '" . $categories['categories_id'] . "'");
  }

// manufacturers
  $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_image from manufacturers where left(manufacturers_image, 7) = 'images/'");
  while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
    tep_db_query("update manufacturers set manufacturers_image = substring('" . $manufacturers['manufacturers_image'] . "', 8) where manufacturers_id = '" . $manufacturers['manufacturers_id'] . "'");
  }

// products
  $products_query = tep_db_query("select products_id, products_image from products where left(products_image, 7) = 'images/'");
  while ($products = tep_db_fetch_array($products_query)) {
    tep_db_query("update products set products_image = substring('" . $products['products_image'] . "', 8) where products_id = '" . $products['products_id'] . "'");
  }
?>

Done!