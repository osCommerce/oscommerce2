<?php
/*
  $Id: install_2.php,v 1.7 2003/07/12 08:10:08 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>

<p class="pageTitle">New Installation</p>

<p><b>Database Import</b></p>

<?php
  if (isset($HTTP_POST_VARS['DB_SERVER']) && !empty($HTTP_POST_VARS['DB_SERVER']) && isset($HTTP_POST_VARS['DB_TEST_CONNECTION']) && ($HTTP_POST_VARS['DB_TEST_CONNECTION'] == 'true')) {
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
<form name="install" action="install.php?step=2" method="post">

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

<?php
      reset($HTTP_POST_VARS);
      while (list($key, $value) = each($HTTP_POST_VARS)) {
        if (($key != 'x') && ($key != 'y') && ($key != 'DB_TEST_CONNECTION')) {
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

<p>&nbsp;</p>

<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td align="center"><a href="index.php"><img src="images/button_cancel.gif" border="0" alt="Cancel"></a></td>
    <td align="center"><input type="image" src="images/button_back.gif" border="0" alt="Back"></td>
  </tr>
</table>

</form>

<?php
    } else {
      $script_filename = getenv('PATH_TRANSLATED');
      if (empty($script_filename)) {
        $script_filename = getenv('SCRIPT_FILENAME');
      }

      $script_filename = str_replace('\\', '/', $script_filename);
      $script_filename = str_replace('//', '/', $script_filename);

      $dir_fs_www_root_array = explode('/', dirname($script_filename));
      $dir_fs_www_root = array();
      for ($i=0, $n=sizeof($dir_fs_www_root_array)-1; $i<$n; $i++) {
        $dir_fs_www_root[] = $dir_fs_www_root_array[$i];
      }
      $dir_fs_www_root = implode('/', $dir_fs_www_root) . '/';
?>

<form name="install" action="install.php?step=3" method="post">

<table width="95%" border="0" cellpadding="2" class="formPage">
  <tr>
    <td>
      <p>A test connection made to the database was <b>successful</b>.</p>
      <p>Please continue the installation process to execute the database import procedure.</p>
      <p>It is important this procedure is not interrupted, otherwise the database may end up corrupt.</p>
      <p>The file to import must be located and named at:</p>
      <p><?php echo $dir_fs_www_root . 'install/oscommerce.sql'; ?></p>
    </td>
  </tr>
</table>

<?php
      reset($HTTP_POST_VARS);
      while (list($key, $value) = each($HTTP_POST_VARS)) {
        if (($key != 'x') && ($key != 'y') && ($key != 'DB_TEST_CONNECTION')) {
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

<p>&nbsp;</p>

<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td align="center"><a href="index.php"><img src="images/button_cancel.gif" border="0" alt="Cancel"></a></td>
    <td align="center"><input type="image" src="images/button_continue.gif" border="0" alt="Continue"></td>
  </tr>
</table>

</form>

<?php
    }
  } else {
?>

<form name="install" action="install.php?step=2" method="post">

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
      <div id="dbUser" class="longDescription">The username used to connect to the database server. An example username is 'mysql_10'.<br><br>Note: Create and Drop permissions <b>are required</b> at this point of the installation procedure.</div>
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
  <tr>
    <td width="30%" valign="top">Persistent Connections:</td>
    <td width="70%" class="smallDesc">
      <?php echo osc_draw_checkbox_field('USE_PCONNECT', 'true'); ?>
      <img src="images/layout/help_icon.gif" onClick="toggleBox('dbConn');"><br>
      <div id="dbConnSD"></div>
      <div id="dbConn" class="longDescription">Enable persistent database connections.<br><br>Note: Persistent connections should be disabled for shared servers.</div>
    </td>
  </tr>
  <tr>
    <td width="30%" valign="top">Session Storage:</td>
    <td width="70%" class="smallDesc">
      <?php echo osc_draw_radio_field('STORE_SESSIONS', 'files', true); ?>&nbsp;Files&nbsp;&nbsp;<?php echo osc_draw_radio_field('STORE_SESSIONS', 'mysql'); ?>&nbsp;Database&nbsp;&nbsp;
      <img src="images/layout/help_icon.gif" onClick="toggleBox('dbSess');"><br>
      <div id="dbSessSD"></div>
      <div id="dbSess" class="longDescription">Store user session data as files on the server, or in the database.<br><br>Note: Due to security related issues, database session storage is recommended for shared servers.</td></div>
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

<?php
  reset($HTTP_POST_VARS);
  while (list($key, $value) = each($HTTP_POST_VARS)) {
    if (($key != 'x') && ($key != 'y') && ($key != 'DB_SERVER') && ($key != 'DB_SERVER_USERNAME') && ($key != 'DB_SERVER_PASSWORD') && ($key != 'DB_DATABASE') && ($key != 'USE_PCONNECT') && ($key != 'STORE_SESSIONS') && ($key != 'DB_TEST_CONNECTION')) {
      if (is_array($value)) {
        for ($i=0; $i<sizeof($value); $i++) {
          echo osc_draw_hidden_field($key . '[]', $value[$i]);
        }
      } else {
        echo osc_draw_hidden_field($key, $value);
      }
    }
  }

  echo osc_draw_hidden_field('DB_TEST_CONNECTION', 'true');
?>

</form>

<?php
  }
?>
