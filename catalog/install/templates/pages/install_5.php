<?php
/*
  $Id: install_5.php,v 1.22 2003/07/09 01:11:06 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  $https_www_address = str_replace('http://', 'https://', $HTTP_POST_VARS['HTTP_WWW_ADDRESS']);
?>

<p class="pageTitle">New Installation</p>

<p><b>osCommerce Configuration</b></p>

<form name="install" action="install.php?step=6" method="post">

<p><b>Please enter the secure web server information:</b></p>

<table width="95%" border="0" cellpadding="2" class="formPage">
  <tr>
    <td width="30%" valign="top">Secure WWW Address:</td>
    <td width="70%" class="smallDesc">
      <?php echo osc_draw_input_field('HTTPS_WWW_ADDRESS', $https_www_address); ?>
      <img src="images/layout/help_icon.gif" onClick="toggleBox('httpsWWW');"><br>
      <div id="httpsWWWSD">The full website address to the online store on the secure server</div>
      <div id="httpsWWW" class="longDescription">The secure web address to the online store, for example <i>https://ssl.my-hosting-company.com/my_name/catalog/</i></div>
    </td>
  </tr>
  <tr>
    <td width="30%" valign="top">Secure Cookie Domain:</td>
    <td width="70%" class="smallDesc">
      <?php echo osc_draw_input_field('HTTPS_COOKIE_DOMAIN', $HTTP_POST_VARS['HTTP_COOKIE_DOMAIN']); ?>
      <img src="images/layout/help_icon.gif" onClick="toggleBox('httpsCookieD');"><br>
      <div id="httpsCookieDSD">The secure domain to store cookies in</div>
      <div id="httpsCookieD" class="longDescription">The full or top-level domain of the secure server to store the cookies in, for example <i>ssl.my-hosting-company.com</i></div>
    </td>
  </tr>
  <tr>
    <td width="30%" valign="top">Secure Cookie Path:</td>
    <td width="70%" class="smallDesc">
      <?php echo osc_draw_input_field('HTTPS_COOKIE_PATH', $HTTP_POST_VARS['HTTP_COOKIE_PATH']); ?>
      <img src="images/layout/help_icon.gif" onClick="toggleBox('dbCookieP');"><br>
      <div id="dbCookiePSD">The secure path to store cookies under</div>
      <div id="dbCookieP" class="longDescription">The web address of the secure server to limit the cookie to, for example <i>/my_name/catalog/</i></div>
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
?>

</form>
