<?php
/*
  $Id: upgrade_2.php,v 1.2 2003/07/09 01:11:06 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>

<p class="pageTitle">Upgrade</p>

<?php
  $db = array();
  $db['DB_SERVER'] = trim(stripslashes($HTTP_POST_VARS['DB_SERVER']));
  $db['DB_SERVER_USERNAME'] = trim(stripslashes($HTTP_POST_VARS['DB_SERVER_USERNAME']));
  $db['DB_SERVER_PASSWORD'] = trim(stripslashes($HTTP_POST_VARS['DB_SERVER_PASSWORD']));
  $db['DB_DATABASE'] = trim(stripslashes($HTTP_POST_VARS['DB_DATABASE']));

  $db_error = false;
  osc_db_connect($db['DB_SERVER'], $db['DB_SERVER_USERNAME'], $db['DB_SERVER_PASSWORD']);

  if ($db_error == false) {
    osc_db_test_create_db_permission($db['DB_DATABASE']);
  }

  if ($db_error != false) {
?>

<form name="upgrade" action="upgrade.php" method="post">

<table width="95%" border="0" cellpadding="2" class="formPage">
  <tr>
    <td>
      <p>A test connection made to the database was <b>NOT</b> successful.</p>
      <p>The error message returned is:</p>
      <p class="boxme"><?php echo $db_error; ?></p>
      <p>Please click on the <i>Back</i> button below to review your database server settings.</p>
      <p>If you require help with your database server settings, please consult your hosting company.</p>
    </td>
  </tr>
</table>

<p>&nbsp;</p>

<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td align="center"><a href="index.php"><img src="images/button_cancel.gif" border="0" alt="Cancel"></a></td>
    <td align="center"><input type="image" src="images/button_back.gif" border="0" alt="Back"></td>
  </tr>
</table>

<?php
    reset($HTTP_POST_VARS);
    while (list($key, $value) = each($HTTP_POST_VARS)) {
      if ($key != 'x' && $key != 'y') {
        if (is_array($value)) {
          for ($i=0; $i<sizeof($value); $i++) {
            echo osc_draw_hidden_field($key . '[]', $value[$i]);
          }
        } else {
          echo osc_draw_hidden_field($key, $value);
        }
      }
    }
?>

</form>

<?php
  } else {
?>

<form name="upgrade" action="upgrade.php?step=3" method="post">

<table width="95%" border="0" cellpadding="2" class="formPage">
  <tr>
    <td>
      <p>A test connection made to the database was <b>successful</b>.</p>
      <p>Please continue the upgrade process to execute the database upgrade procedure.</p>
      <p>It is important this procedure is not interrupted, otherwise the database may end up corrupt.</p>
    </td>
  </tr>
</table>

<p>&nbsp;</p>

<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td align="center"><a href="index.php"><img src="images/button_cancel.gif" border="0" alt="Cancel"></a></td>
    <td align="center"><input type="image" src="images/button_continue.gif" border="0" alt="Continue"></td>
  </tr>
</table>

<?php
    reset($HTTP_POST_VARS);
    while (list($key, $value) = each($HTTP_POST_VARS)) {
      if ($key != 'x' && $key != 'y') {
        if (is_array($value)) {
          for ($i=0; $i<sizeof($value); $i++) {
            echo osc_draw_hidden_field($key . '[]', $value[$i]);
          }
        } else {
          echo osc_draw_hidden_field($key, $value);
        }
      }
    }
?>

</form>

<?php
  }
?>
