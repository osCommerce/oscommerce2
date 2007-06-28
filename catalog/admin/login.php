<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  require('includes/functions/password_funcs.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'process':
        $username = tep_db_prepare_input($HTTP_POST_VARS['username']);
        $password = tep_db_prepare_input($HTTP_POST_VARS['password']);

        $check_query = tep_db_query("select id, user_name, user_password from " . TABLE_ADMINISTRATORS . " where user_name = '" . tep_db_input($username) . "'");

        if (tep_db_num_rows($check_query) == 1) {
          $check = tep_db_fetch_array($check_query);

          if (tep_validate_password($password, $check['user_password'])) {
            tep_session_register('admin');

            $admin = array('id' => $check['id'],
                           'username' => $check['user_name']);

            if (tep_session_is_registered('redirect_origin')) {
              $page = $redirect_origin['page'];
              $get_string = '';

              if (function_exists('http_build_query')) {
                $get_string = http_build_query($redirect_origin['get']);
              }

              tep_session_unregister('redirect_origin');

              tep_redirect(tep_href_link($page, $get_string));
            } else {
              tep_redirect(tep_href_link(FILENAME_DEFAULT));
            }
          }
        }

        break;

      case 'logoff':
        tep_session_unregister('admin');
        tep_redirect(tep_href_link(FILENAME_DEFAULT));

        break;

      case 'create':
        $check_query = tep_db_query("select id from " . TABLE_ADMINISTRATORS . " limit 1");

        if (tep_db_num_rows($check_query) == 0) {
          $username = tep_db_prepare_input($HTTP_POST_VARS['username']);
          $password = tep_db_prepare_input($HTTP_POST_VARS['password']);

          tep_db_query('insert into ' . TABLE_ADMINISTRATORS . ' (user_name, user_password) values ("' . $username . '", "' . tep_encrypt_password($password) . '")');
        }

        tep_redirect(tep_href_link(FILENAME_LOGIN));

        break;
    }
  }

  $languages = tep_get_languages();
  $languages_array = array();
  $languages_selected = DEFAULT_LANGUAGE;
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $languages_array[] = array('id' => $languages[$i]['code'],
                               'text' => $languages[$i]['name']);
    if ($languages[$i]['directory'] == $language) {
      $languages_selected = $languages[$i]['code'];
    }
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<style type="text/css"><!--
a { color:#080381; text-decoration:none; }
a:hover { color:#aabbdd; text-decoration:underline; }
a.text:link, a.text:visited { color: #000000; text-decoration: none; }
a:text:hover { color: #000000; text-decoration: underline; }
a.main:link, a.main:visited { color: #ffffff; text-decoration: none; }
A.main:hover { color: #ffffff; text-decoration: underline; }
a.sub:link, a.sub:visited { color: #dddddd; text-decoration: none; }
A.sub:hover { color: #dddddd; text-decoration: underline; }
.heading { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 20px; font-weight: bold; line-height: 1.5; color: #D3DBFF; }
.main { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 17px; font-weight: bold; line-height: 1.5; color: #ffffff; }
.sub { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; font-weight: bold; line-height: 1.5; color: #dddddd; }
.text { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 11px; font-weight: bold; line-height: 1.5; color: #000000; }
.menuBoxHeading { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; color: #ffffff; font-weight: bold; background-color: #7187bb; border-color: #7187bb; border-style: solid; border-width: 1px; }
.infoBox { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10px; color: #080381; background-color: #f2f4ff; border-color: #7187bb; border-style: solid; border-width: 1px; }
.smallText { font-family: Verdana, Arial, sans-serif; font-size: 10px; }
//--></style>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">

<table border="0" width="600" height="100%" cellspacing="0" cellpadding="0" align="center" valign="middle">
  <tr>
    <td><table border="0" width="600" height="440" cellspacing="0" cellpadding="1" align="center" valign="middle">
      <tr bgcolor="#000000">
        <td><table border="0" width="600" height="440" cellspacing="0" cellpadding="0">
          <tr bgcolor="#ffffff" height="50">
            <td height="50"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image(DIR_WS_IMAGES . 'oscommerce.png', 'osCommerce Online Merchant v2.2 RC1') . '</a>'; ?></td>
            <td align="right" class="text" nowrap><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . HEADER_TITLE_ADMINISTRATION . '</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="' . tep_catalog_href_link() . '">' . HEADER_TITLE_ONLINE_CATALOG . '</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://www.oscommerce.com" target="_blank">' . HEADER_TITLE_SUPPORT_SITE . '</a>'; ?>&nbsp;&nbsp;</td>
          </tr>
          <tr bgcolor="#080381">
            <td width="600" colspan="2" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr><?php echo tep_draw_form('languages', 'login.php', '', 'get'); ?>
                    <td class="heading"><?php echo HEADING_TITLE; ?></td>
                    <td align="right"><?php echo tep_draw_pull_down_menu('language', $languages_array, $languages_selected, 'onChange="this.form.submit();"'); ?></td>
                  <?php echo tep_hide_session_id(); ?></form></tr>
                </table></td>
              </tr>
              <tr>
                <td valign="top">

<?php
  $check_query = tep_db_query("select id from " . TABLE_ADMINISTRATORS . " limit 1");
  if (tep_db_num_rows($check_query) == 1) {
    if ($action == 'process') {
      echo '<p class="sub">' . ERROR_INVALID_ADMINISTRATOR . '</p>' . "\n";
    }
?>

                  <?php echo tep_draw_form('languages', 'login.php', 'action=process'); ?><table border="0" width="100%" cellspacing="0" cellpadding="2">
                    <tr>
                      <td class="sub"><?php echo TEXT_USERNAME; ?></td>
                      <td class="sub"><?php echo tep_draw_input_field('username'); ?></td>
                    </tr>
                    <tr>
                      <td class="sub"><?php echo TEXT_PASSWORD; ?></td>
                      <td class="sub"><?php echo tep_draw_password_field('password'); ?></td>
                    </tr>
                    <tr>
                      <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                      <td colspan="2" align="right"><input type="submit" value="<?php echo BUTTON_LOGIN; ?>" /></td>
                    </tr>
                  </table></form>

<?php
  } else {
?>

                  <p class="sub"><?php echo TEXT_CREATE_FIRST_ADMINISTRATOR; ?></p>

                  <?php echo tep_draw_form('languages', 'login.php', 'action=create'); ?><table border="0" width="100%" cellspacing="0" cellpadding="2">
                    <tr>
                      <td class="sub"><?php echo TEXT_USERNAME; ?></td>
                      <td class="sub"><?php echo tep_draw_input_field('username'); ?></td>
                    </tr>
                    <tr>
                      <td class="sub"><?php echo TEXT_PASSWORD; ?></td>
                      <td class="sub"><?php echo tep_draw_password_field('password'); ?></td>
                    </tr>
                    <tr>
                      <td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                      <td colspan="2" align="right"><input type="submit" value="<?php echo BUTTON_CREATE_ADMINISTRATOR; ?>" /></td>
                    </tr>
                  </table></form>

<?php
  }
?>

                </td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php require(DIR_WS_INCLUDES . 'footer.php'); ?></td>
      </tr>
    </table></td>
  </tr>
</table>

</body>

</html>
