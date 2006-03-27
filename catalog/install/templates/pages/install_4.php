<?php
/*
  $Id: install_4.php,v 1.11 2003/07/11 14:59:01 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  $cookie_path = substr(dirname(getenv('SCRIPT_NAME')), 0, -7);

  $www_location = 'http://' . getenv('HTTP_HOST') . getenv('SCRIPT_NAME');
  $www_location = substr($www_location, 0, strpos($www_location, 'install'));

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

<p><b>osCommerce Configuration</b></p>

<form name="install" action="install.php?step=5" method="post">

<p><b>Please enter the web server information:</b></p>

<table width="95%" border="0" cellpadding="2" class="formPage">
  <tr>
    <td width="30%" valign="top">WWW Address:</td>
    <td width="70%" class="smallDesc">
      <?php echo osc_draw_input_field('HTTP_WWW_ADDRESS', $www_location); ?>
      <img src="images/layout/help_icon.gif" onClick="toggleBox('dbWWW');"><br>
      <div id="dbWWWSD">The full website address to the online store</div>
      <div id="dbWWW" class="longDescription">The web address to the online store, for example <i>http://www.my-server.com/catalog/</i></div>
    </td>
  </tr>
  <tr>
    <td width="30%" valign="top">Webserver Root Directory:</td>
    <td width="70%" class="smallDesc">
      <?php echo osc_draw_input_field('DIR_FS_DOCUMENT_ROOT', $dir_fs_www_root); ?>
      <img src="images/layout/help_icon.gif"  onClick="toggleBox('dbRoot');"><br>
      <div id="dbRootSD">The server path to the online store</div>
      <div id="dbRoot" class="longDescription">The directory where osCommerce is installed on the server, for example <i>/home/myname/public_html/osCommerce/</i></div>
    </td>
  </tr>
  <tr>
    <td width="30%" valign="top">HTTP Cookie Domain:</td>
    <td width="70%" class="smallDesc">
      <?php echo osc_draw_input_field('HTTP_COOKIE_DOMAIN', getenv('HTTP_HOST')); ?>
      <img src="images/layout/help_icon.gif" onClick="toggleBox('dbCookieD');"><br>
      <div id="dbCookieDSD">The domain to store cookies in</div>
      <div id="dbCookieD" class="longDescription">The full or top-level domain to store the cookies in, for example <i>.my-server.com</i></div>
    </td>
  </tr>
  <tr>
    <td width="30%" valign="top">HTTP Cookie Path:</td>
    <td width="70%" class="smallDesc">
      <?php echo osc_draw_input_field('HTTP_COOKIE_PATH', $cookie_path); ?>
      <img src="images/layout/help_icon.gif" onClick="toggleBox('dbCookieP');"><br>
      <div id="dbCookiePSD">The path to store cookies under</div>
      <div id="dbCookieP" class="longDescription">The web address to limit the cookie to, for example <i>/catalog/</i></div>
    </td>
  </tr>
  <tr>
    <td width="30%" valign="top">Enable SSL Connections:</td>
    <td width="70%" class="smallDesc">
      <?php echo osc_draw_checkbox_field('ENABLE_SSL', 'true'); ?>
      <img src="images/layout/help_icon.gif" onClick="toggleBox('dbSSL');"><br>
      <div id="dbSSLSD"></div>
      <div id="dbSSL" class="longDescription">Enable secure SSL/HTTPS connections (requires a secure certificate installed on the web server)</div>
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
    if (($key != 'x') && ($key != 'y')) {
      if (is_array($value)) {
        for ($i=0; $i<sizeof($value); $i++) {
          echo osc_draw_hidden_field($key . '[]', $value[$i]);
        }
      } else {
        echo osc_draw_hidden_field($key, $value);
      }
    }
  }

  echo osc_draw_hidden_field('install[]', 'configure');
?>

</form>
