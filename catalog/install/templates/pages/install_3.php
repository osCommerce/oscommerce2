<?php
/*
  $Id: install_3.php,v 1.8 2003/07/11 14:59:01 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

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

<p class="pageTitle">New Installation</p>

<p><b>Database Import</b></p>

<?php
  if (osc_in_array('database', $HTTP_POST_VARS['install'])) {
    $db = array();
    $db['DB_SERVER'] = trim(stripslashes($HTTP_POST_VARS['DB_SERVER']));
    $db['DB_SERVER_USERNAME'] = trim(stripslashes($HTTP_POST_VARS['DB_SERVER_USERNAME']));
    $db['DB_SERVER_PASSWORD'] = trim(stripslashes($HTTP_POST_VARS['DB_SERVER_PASSWORD']));
    $db['DB_DATABASE'] = trim(stripslashes($HTTP_POST_VARS['DB_DATABASE']));

    osc_db_connect($db['DB_SERVER'], $db['DB_SERVER_USERNAME'], $db['DB_SERVER_PASSWORD']);

    $db_error = false;
    $sql_file = $dir_fs_www_root . 'install/oscommerce.sql';

    osc_set_time_limit(0);
    osc_db_install($db['DB_DATABASE'], $sql_file);

    if ($db_error != false) {
?>
<form name="install" action="install.php?step=3" method="post">

<table width="95%" border="0" cellpadding="2" class="formPage">
  <tr>
    <td>
      <p>The following error has occurred:</p>
       <p class="boxme"><?php echo $db_error; ?></p>
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
    <td align="center"><input type="image" src="images/button_retry.gif" border="0" alt="Retry"></td>
  </tr>
</table>

</form>

<?php
    } else {
?>
<form name="install" action="install.php?step=4" method="post">

<table width="95%" border="0" cellpadding="2" class="formPage">
  <tr>
    <td>
      <p>The database import was <b>successful!</b></p>
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
<?php
      if (osc_in_array('configure', $HTTP_POST_VARS['install'])) {
?>
    <td align="center"><input type="image" src="images/button_continue.gif" border="0" alt="Continue"></td>
<?php
      } else {
?>
    <td align="center"><a href="index.php"><img src="images/button_continue.gif" border="0" alt="Continue"></a></td>
<?php
      }
?>
  </tr>
</table>

</form>

<?php
    }
  }
?>
