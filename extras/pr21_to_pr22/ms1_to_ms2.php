<?php
/*
  $Id: ms1_to_ms2.php,v 1.3 2003/06/23 01:27:34 thomasamoulton Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>

<script language="JavaScript"><!--
function changeStyle(what, how) {
  if (document.getElementById) {
    document.getElementById(what).style.fontWeight = how;
  } else if (document.all) {
    document.all[what].style.fontWeight = how;
  }
}

function changeText(where, what) {
  if (document.getElementById) {
    document.getElementById(where).innerHTML = what;
  } else if (document.all) {
    document.all[where].innerHTML = what;
  }
}
//--></script>

<html>
<head>
<title>osCommerce Preview Release 2.2 Database Update Script</title>
<style type=text/css><!--
A:link, A:visited { color: #0029A3; text-decoration: none; }
A:hover { color: #5D59ac; text-decoration: underline; }
TD, UL, P, BODY { font-family: Verdana, Arial, sans-serif; font-size: 11px; line-height: 1.5; }
.boxMe { font-family: Verdana, Arial, sans-serif; font-size: 11px; color: #000000; background-color: #e5e5e5; }
.noteBox { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 11px; line-height: 1.5; background-color: #fef3da; border: thin dashed; padding: 6px; }
.navigationBar { font-family: Verdana, Arial, sans-serif; font-size: 10px; font-weight: bold; color: #ffffff; }
.footerBar { font-family: Verdana, Arial, sans-serif; font-size: 10px; color: #ffffff; }
.mainText { font-family: Verdana, Arial, sans-serif; font-size: 11px; line-height: 1.5; }
.smallText { font-family: Verdana, Arial, sans-serif; font-size: 10px; line-height: 1.5; }
.infoBoxHeading { font-family: Verdana, Arial, sans-serif; font-size: 10px; font-weight: bold; color: #ffffff; }
.infoBoxText { font-family: Verdana, Arial, sans-serif; font-size: 10px; }
.pageHeading { font-family: Verdana, Arial, sans-serif; font-size: 20px; color: #9a9a9a; font-weight: bold; }
.pageTitle { font-family: Verdana, Arial, sans-serif; font-size: 11px; line-height: 1.5; font-weight: bold; text-decoration: underline; }
  }
//--></style>
</head>
<body>
<p>
<b>osCommerce Release 2.2 MS1 to MS2 Database Update Script</b>
<p>This script can be copied to any web directory to upgrade a MS1 database
to a MS2 database. By MS1 and MS2 I mean the state of the database the DAY
that the MS release was made, not *any* MS1 like CVS tree.

So if you upgraded to MS1 and stayed there you can use this script.
<?php
  if (!$HTTP_POST_VARS['DB_SERVER']) {
?>
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

  function osc_db_connect() {
    global $db_link, $HTTP_POST_VARS;

    $db_link = mysql_connect($HTTP_POST_VARS['DB_SERVER'], $HTTP_POST_VARS['DB_SERVER_USERNAME'], $HTTP_POST_VARS['DB_SERVER_PASSWORD']);

    if ($db_link) mysql_select_db($HTTP_POST_VARS['DB_DATABASE']);

    return $db_link;
  }

  function osc_db_error ($query, $errno, $error) { 
    die('<font color="#000000"><b>' . $errno . ' - ' . $error . '<br><br>' . $query . '<br><br><small><font color="#ff0000">[TEP STOP]</font></small><br><br></b></font>');
  }

  function osc_db_query($db_query) {
    global $db_link;

    $result = mysql_query($db_query, $db_link) or osc_db_error($db_query, mysql_errno(), mysql_error());

    return $result;
  }

  function osc_db_fetch_array($db_query) {
    $result = mysql_fetch_array($db_query);

    return $result;
  }

  function osc_db_num_rows($db_query) {
    return mysql_num_rows($db_query);
  }

// Sets timeout for the current script.
// Cant be used in safe mode.
  function osc_set_time_limit($limit) {
    if (!get_cfg_var('safe_mode')) {
      set_time_limit($limit);
    }
  }

  osc_db_connect() or die('Unable to connect to database server!');
?>
<p><span class="pageHeading">osCommerce</span><br><font color="#9a9a9a">Open Source E-Commerce Solutions</font></p>

<p class="pageTitle">Upgrade</p>

<p><b>Step 1: Database Upgrade</b></p>

<?php
  function osc_db_update_configuration_key($key, $new_key) {

    $sql = "update configuration set configuration_key = '" . $new_key . "' where configuration_key = '" . $key . "'";
    osc_db_query($sql);
    $db_error = mysql_error();
    if ($db_error != false) die($db_error);
    osc_db_query("update configuration set last_modified = NOW() where configuration_key = '" . $new_key . "'");
  }

  function osc_db_update_configuration_title($key, $new_title) {

    $sql = "update configuration set configuration_title = '" . $new_title . "' where configuration_key = '" . $key . "'";
    osc_db_query($sql);
    $db_error = mysql_error();
    if ($db_error != false) die($db_error);
    osc_db_query("update configuration set last_modified = NOW() where configuration_key = '" . $key . "'");
  }

  function osc_db_update_configuration_description($key, $new_description) {

    $sql = "update configuration set configuration_description = '" . $new_description . "' where configuration_key = '" . $key . "'";
    osc_db_query($sql);
    $db_error = mysql_error();
    if ($db_error != false) die($db_error);
    osc_db_query("update configuration set last_modified = NOW() where configuration_key = '" . $key . "'");
  }

  function osc_db_update_configuration_use_null($key) {

    $sql = "update configuration set use_function = NULL where configuration_key = '" . $key . "'";
    osc_db_query($sql);
    $db_error = mysql_error();
    if ($db_error != false) die($db_error);
    osc_db_query("update configuration set last_modified = NOW() where configuration_key = '" . $key . "'");
  }

  osc_set_time_limit(0);

// send data to the browser, so the flushing works with IE
  for ($i=0; $i<300; $i++) print(' ');
  print ("\n");
?>

<p><span id="addressBook"><span id="addressBookMarker">-</span> Address Book</span><br>
<span id="configuration"><span id="configurationMarker">-</span> Configuration</span><br>

<p>Status: <span id="statusText">Preparing</span></p>

<?php flush(); ?>

<script language="javascript"><!--
changeStyle('addressBook', 'bold');
changeText('addressBookMarker', '?');
changeText('statusText', 'Updating Address Book');
//--></script>

<?php
  flush();

  /* Now convert the address_book_id to unique entries, now most are =1 */
  osc_db_query("alter table address_book add temp_id int(11) not NULL default '0' FIRST");
  $ab_query = osc_db_query("select customers_id, address_book_id from address_book order by address_book_id");
  $ab_id = 1;
  while ($ab = osc_db_fetch_array($ab_query)) {
    osc_db_query("update customers set customers_default_address_id = '" . $ab_id . "' where customers_id = '" . $ab['customers_id'] . "'");
    osc_db_query("update address_book set temp_id = '" . $ab_id . "' where customers_id = '" . $ab['customers_id'] . "' and address_book_id = '" . $ab['address_book_id'] . "'");
    $ab_id++;
  }

  osc_db_query("ALTER TABLE address_book DROP PRIMARY KEY");
  osc_db_query("ALTER TABLE address_book DROP COLUMN address_book_id");
  osc_db_query("ALTER TABLE address_book ADD PRIMARY KEY (temp_id)");
  osc_db_query("ALTER TABLE address_book CHANGE COLUMN temp_id address_book_id int(11) NOT NULL auto_increment");
  osc_db_query("ALTER TABLE address_book ADD INDEX idx_address_book_customers_id (customers_id)");

  osc_db_query("ALTER TABLE customers CHANGE COLUMN customers_default_address_id customers_default_address_id int(11) NOT NULL default '0'");
?>
<script language="javascript"><!--
changeStyle('addressBook', 'normal');
changeText('addressBookMarker', '*');
changeText('statusText', 'Updating Address Book .. done!');

changeStyle('configuration', 'bold');
changeText('configurationMarker', '?');
changeText('statusText', 'Updating Configuration');
//--></script>

<?php
  flush();

  osc_db_update_configuration_key('ENTRY_COMPANY_LENGTH', 'ENTRY_COMPANY_MIN_LENGTH');

  osc_db_query("update configuration set use_function = 'tep_cfg_get_zone_name' where configuration_key = 'STORE_ZONE'");

  osc_db_update_configuration_key('STORE_ORIGIN_ZIP', 'SHIPPING_ORIGIN_ZIP');

  osc_db_query("INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) VALUES ('Country of Origin', 'SHIPPING_ORIGIN_COUNTRY', '223', 'Select the country of origin to be used in shipping quotes.', '7', '1', 'tep_get_country_name', 'tep_cfg_pull_down_country_list(', now())");

  $country_query = osc_db_query("select configuration_value as name from configuration where configuration_key = 'STORE_ORIGIN_COUNTRY'");
  if (osc_db_num_rows($country_query) > 0) {
    $country = osc_db_fetch_array($country_query);
    if ($country['name'] != '') {
      $new_country_query = osc_db_query("select countries_id from countries where countries_iso_code_2 = '" . $country['name'] . "'");
      $new_country = osc_db_fetch_array($new_country_query);
      if ($new_country['countries_iso_code_2'] != NULL) {
        osc_db_query("update configuration set configuration_value = " . $new_country['countries_iso_code_2'] . " where configuration_key = 'SHIPPING_ORIGIN_COUNTRY'");
      }
    }
  }

  osc_db_query("delete from configuration where configuration_key = 'STORE_ORIGIN_COUNTRY'");

  osc_db_query("insert into configuration_group values ('15', 'Sessions', 'Session options', '15', '1')");

  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Session Directory', 'SESSION_WRITE_DIRECTORY', '/tmp', 'If sessions are file based, store them in this directory.', '15', '1', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Force Cookie Use', 'SESSION_FORCE_COOKIE_USE', 'False', 'Force the use of sessions when cookies are only enabled.', '15', '2', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Check SSL Session ID', 'SESSION_CHECK_SSL_SESSION_ID', 'False', 'Validate the SSL_SESSION_ID on every secure HTTPS page request.', '15', '3', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Check User Agent', 'SESSION_CHECK_USER_AGENT', 'False', 'Validate the clients browser user agent on every page request.', '15', '4', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Check IP Address', 'SESSION_CHECK_IP_ADDRESS', 'False', 'Validate the clients IP address on every page request.', '15', '5', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Prevent Spider Sessions', 'SESSION_BLOCK_SPIDERS', 'False', 'Prevent known spiders from starting a session.', '15', '6', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
  osc_db_query("insert into configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Recreate Session', 'SESSION_RECREATE', 'False', 'Recreate the session to generate a new session ID when the customer logs on or creates an account (PHP >=4.1 needed).', '15', '7', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");

  osc_db_update_configuration_title('SHIPPING_ORIGIN_COUNTRY', 'Country of Origin');

  osc_db_update_configuration_description('ACCOUNT_COMPANY', 'Display company in the customers account');
  osc_db_update_configuration_description('ACCOUNT_DOB', 'Display date of birth in the customers account');
  osc_db_update_configuration_description('ACCOUNT_GENDER', 'Display gender in the customers account');
  osc_db_update_configuration_description('ACCOUNT_STATE', 'Display state in the customers account');
  osc_db_update_configuration_description('ACCOUNT_SUBURB', 'Display suburb in the customers account');
  osc_db_update_configuration_description('SHIPPING_ORIGIN_COUNTRY', 'Select the country of origin to be used in shipping quotes.');
?>

<script language="javascript"><!--
changeStyle('configuration', 'normal');
changeText('configurationMarker', '*');
changeText('statusText', 'Updating Configuration .. done!');

changeStyle('statusText', 'bold');
changeText('statusText', 'Update Complete!');
//--></script>

<?php flush(); ?>

<p>The database upgrade procedure was successful!</p>
