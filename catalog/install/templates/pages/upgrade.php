<?php
/*
  $Id: upgrade.php,v 1.4 2003/07/12 09:05:43 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>

<p class="pageTitle">Upgrade</p>

<p>This upgrade procedure will upgrade the <nobr>The Exchange Project 2.1</nobr> database structure to the <nobr>osCommerce 2.2 Milestone 2</nobr> database structure.</p>

<p>It is recommended to perform this upgrade procedure on an up-to-date backup of your 2.1 database.</p>

<form name="upgrade" action="upgrade.php?step=2" method="post">

<p><b>Please enter the database server information:</b></p>

<table width="95%" border="0" cellpadding="2" class="formPage">
  <tr>
    <td width="30%" valign="top">Database Server:</td>
    <td width="70%" class="smallDesc">
      <?php echo osc_draw_input_field('DB_SERVER'); ?>
      <img src="images/layout/help_icon.gif" onClick="toggleBox('dbHost');"><br>
      <div id="dbHostSD">Hostame or IP-address of the database server</div>
      <div id="dbHost" class="longDescription">The database server can be in the form of a hostname, such as db1.myserver.com, or as an IP-address, such as 192.168.0.1</div>
    </td>
  </tr>
  <tr>
    <td width="30%" valign="top">Username:</td>
    <td width="70%" class="smallDesc">
      <?php echo osc_draw_input_field('DB_SERVER_USERNAME'); ?>
      <img src="images/layout/help_icon.gif"  onClick="toggleBox('dbUser');"><br>
      <div id="dbUserSD">Database username</div>
      <div id="dbUser" class="longDescription">The username used to connect to the database server. An example username is 'mysql_10'.<br><br>Note: Create and Drop permissions <b>are required</b> at this point of the upgrade procedure.</div>
    </td>
  </tr>
  <tr>
    <td width="30%" valign="top">Password:</td>
    <td width="70%" class="smallDesc">
      <?php echo osc_draw_password_field('DB_SERVER_PASSWORD'); ?>
      <img src="images/layout/help_icon.gif" onClick="toggleBox('dbPass');"><br>
      <div id="dbPassSD">Database password</div>
      <div id="dbPass" class="longDescription">The password is used together with the username, which forms the database user account.</div>
    </td>
  </tr>
  <tr>
    <td width="30%" valign="top">Database Name:</td>
    <td width="70%" class="smallDesc">
      <?php echo osc_draw_input_field('DB_DATABASE'); ?>
      <img src="images/layout/help_icon.gif" onClick="toggleBox('dbName');"><br>
      <div id="dbNameSD">Database Name</div>
      <div id="dbName" class="longDescription">The database used to hold the data. An example database name is 'osCommerce'.</div>
    </td>
  </tr>
</table>

<p>&nbsp;</p>

<table border="0" width="100%" cellspacing="0" cellpadding="0" align="center">
  <tr>
    <td align="center"><a href="index.php"><img src="images/button_cancel.gif" border="0" alt="Cancel"></a></td>
    <td align="center"><input type="image" src="images/button_continue.gif" border="0" alt="Continue"></td>
  </tr>
</table>

</form>
