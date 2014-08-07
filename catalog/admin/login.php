<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  $login_request = true;

  require('includes/application_top.php');
  require('includes/functions/password_funcs.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

// prepare to logout an active administrator if the login page is accessed again
  if (tep_session_is_registered('admin')) {
    $action = 'logoff';
  }

  if (tep_not_null($action)) {
    switch ($action) {
      case 'process':
        if (tep_session_is_registered('redirect_origin') && isset($redirect_origin['auth_user']) && !isset($HTTP_POST_VARS['username'])) {
          $username = tep_db_prepare_input($redirect_origin['auth_user']);
          $password = tep_db_prepare_input($redirect_origin['auth_pw']);
        } else {
          $username = tep_db_prepare_input($HTTP_POST_VARS['username']);
          $password = tep_db_prepare_input($HTTP_POST_VARS['password']);
        }

        $actionRecorder = new actionRecorderAdmin('ar_admin_login', null, $username);

        if ($actionRecorder->canPerform()) {
          $check_query = tep_db_query("select id, user_name, user_password from " . TABLE_ADMINISTRATORS . " where user_name = '" . tep_db_input($username) . "'");

          if (tep_db_num_rows($check_query) == 1) {
            $check = tep_db_fetch_array($check_query);

            if (tep_validate_password($password, $check['user_password'])) {
// migrate old hashed password to new phpass password
              if (tep_password_type($check['user_password']) != 'phpass') {
                tep_db_query("update " . TABLE_ADMINISTRATORS . " set user_password = '" . tep_encrypt_password($password) . "' where id = '" . (int)$check['id'] . "'");
              }

              tep_session_register('admin');

              $admin = array('id' => $check['id'],
                             'username' => $check['user_name']);

              $actionRecorder->_user_id = $admin['id'];
              $actionRecorder->record();

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

          if (isset($HTTP_POST_VARS['username'])) {
            $messageStack->add(ERROR_INVALID_ADMINISTRATOR, 'error');
          }
        } else {
          $messageStack->add(sprintf(ERROR_ACTION_RECORDER, (defined('MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES') ? (int)MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES : 5)));
        }

        if (isset($HTTP_POST_VARS['username'])) {
          $actionRecorder->record(false);
        }

        break;

      case 'logoff':
        tep_session_unregister('admin');

        if (isset($HTTP_SERVER_VARS['PHP_AUTH_USER']) && !empty($HTTP_SERVER_VARS['PHP_AUTH_USER']) && isset($HTTP_SERVER_VARS['PHP_AUTH_PW']) && !empty($HTTP_SERVER_VARS['PHP_AUTH_PW'])) {
          tep_session_register('auth_ignore');
          $auth_ignore = true;
        }

        tep_redirect(tep_href_link(FILENAME_DEFAULT));

        break;

      case 'create':
        $check_query = tep_db_query("select id from " . TABLE_ADMINISTRATORS . " limit 1");

        if (tep_db_num_rows($check_query) == 0) {
          $username = tep_db_prepare_input($HTTP_POST_VARS['username']);
          $password = tep_db_prepare_input($HTTP_POST_VARS['password']);

          if ( !empty($username) ) {
            tep_db_query("insert into " . TABLE_ADMINISTRATORS . " (user_name, user_password) values ('" . tep_db_input($username) . "', '" . tep_db_input(tep_encrypt_password($password)) . "')");
          }
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

  $admins_check_query = tep_db_query("select id from " . TABLE_ADMINISTRATORS . " limit 1");
  if (tep_db_num_rows($admins_check_query) < 1) {
    $messageStack->add(TEXT_CREATE_FIRST_ADMINISTRATOR, 'warning');
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td><table border="0" width="100%" cellspacing="0" cellpadding="0" height="40">
      <tr>
        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>

<?php
  if (sizeof($languages_array) > 1) {
?>

        <td class="pageHeading" align="right"><?php echo tep_draw_form('adminlanguage', FILENAME_DEFAULT, '', 'get') . tep_draw_pull_down_menu('language', $languages_array, $languages_selected, 'onchange="this.form.submit();"') . tep_hide_session_id() . '</form>'; ?></td>

<?php
  }
?>

      </tr>
    </table></td>
  </tr>
  <tr>
    <td>

<?php
  $heading = array();
  $contents = array();

  if (tep_db_num_rows($admins_check_query) > 0) {
    $heading[] = array('text' => '<strong>' . HEADING_TITLE . '</strong>');

    $contents = array('form' => tep_draw_form('login', FILENAME_LOGIN, 'action=process'));
    $contents[] = array('text' => TEXT_USERNAME . '<br />' . tep_draw_input_field('username'));
    $contents[] = array('text' => '<br />' . TEXT_PASSWORD . '<br />' . tep_draw_password_field('password'));
    $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(BUTTON_LOGIN, 'key'));
  } else {
    $heading[] = array('text' => '<strong>' . HEADING_TITLE . '</strong>');

    $contents = array('form' => tep_draw_form('login', FILENAME_LOGIN, 'action=create'));
    $contents[] = array('text' => TEXT_CREATE_FIRST_ADMINISTRATOR);
    $contents[] = array('text' => '<br />' . TEXT_USERNAME . '<br />' . tep_draw_input_field('username'));
    $contents[] = array('text' => '<br />' . TEXT_PASSWORD . '<br />' . tep_draw_password_field('password'));
    $contents[] = array('align' => 'center', 'text' => '<br />' . tep_draw_button(BUTTON_CREATE_ADMINISTRATOR, 'key'));
  }

  $box = new box;
  echo $box->infoBox($heading, $contents);
?>

    </td>
  </tr>
</table>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
