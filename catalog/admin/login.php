<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  $login_request = true;

  require('includes/application_top.php');
  require('includes/functions/password_funcs.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

// prepare to logout an active administrator if the login page is accessed again
  if (isset($_SESSION['admin'])) {
    $action = 'logoff';
  }

  if (tep_not_null($action)) {
    switch ($action) {
      case 'process':
        if (isset($_SESSION['redirect_origin']) && isset($_SESSION['redirect_origin']['auth_user']) && !isset($_POST['username'])) {
          $username = HTML::sanitize($_SESSION['redirect_origin']['auth_user']);
          $password = HTML::sanitize($_SESSION['redirect_origin']['auth_pw']);
        } else {
          $username = HTML::sanitize($_POST['username']);
          $password = HTML::sanitize($_POST['password']);
        }

        $actionRecorder = new actionRecorderAdmin('ar_admin_login', null, $username);

        if ($actionRecorder->canPerform()) {
          $Qadmin = $OSCOM_Db->get('administrators', [
            'id',
            'user_name',
            'user_password'
          ], [
            'user_name' => $username
          ]);

          if ($Qadmin->fetch() !== false) {
            if (tep_validate_password($password, $Qadmin->value('user_password'))) {
// migrate old hashed password to new phpass password
              if (tep_password_type($Qadmin->value('user_password')) != 'phpass') {
                $OSCOM_Db->save('administrators', [
                  'user_password' => tep_encrypt_password($password)
                ], [
                  'id' => $Qadmin->valueInt('id')
                ]);
              }

              $_SESSION['admin'] = [
                'id' => $Qadmin->valueInt('id'),
                'username' => $Qadmin->value('user_name')
              ];

              $actionRecorder->_user_id = $_SESSION['admin']['id'];
              $actionRecorder->record();

              if (isset($_SESSION['redirect_origin'])) {
                $page = $_SESSION['redirect_origin']['page'];
                $get_string = '';

                if (function_exists('http_build_query')) {
                  $get_string = http_build_query($_SESSION['redirect_origin']['get']);
                }

                unset($_SESSION['redirect_origin']);

                OSCOM::redirect($page, $get_string);
              } else {
                OSCOM::redirect(FILENAME_DEFAULT);
              }
            }
          }

          if (isset($_POST['username'])) {
            $OSCOM_MessageStack->add(ERROR_INVALID_ADMINISTRATOR, 'error');
          }
        } else {
          $OSCOM_MessageStack->add(sprintf(ERROR_ACTION_RECORDER, (defined('MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES') ? (int)MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES : 5)));
        }

        if (isset($_POST['username'])) {
          $actionRecorder->record(false);
        }

        break;

      case 'logoff':
        unset($_SESSION['admin']);

        if (isset($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && !empty($_SERVER['PHP_AUTH_PW'])) {
          $_SESSION['auth_ignore'] = true;
        }

        OSCOM::redirect(FILENAME_DEFAULT);

        break;

      case 'create':
        $Qcheck = $OSCOM_Db->get('administrators', 'id', null, null, 1);

        if (!$Qcheck->check()) {
          $username = HTML::sanitize($_POST['username']);
          $password = HTML::sanitize($_POST['password']);

          if ( !empty($username) ) {
            $OSCOM_Db->save('administrators', [
              'user_name' => $username,
              'user_password' => tep_encrypt_password($password)
            ]);
          }
        }

        OSCOM::redirect(FILENAME_LOGIN);

        break;
    }
  }

  $languages = tep_get_languages();
  $languages_array = array();
  $languages_selected = DEFAULT_LANGUAGE;
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $languages_array[] = array('id' => $languages[$i]['code'],
                               'text' => $languages[$i]['name']);
    if ($languages[$i]['directory'] == $_SESSION['language']) {
      $languages_selected = $languages[$i]['code'];
    }
  }

  $Qcheck = $OSCOM_Db->get('administrators', 'id', null, null, 1);

  if (!$Qcheck->check()) {
    $OSCOM_MessageStack->add(TEXT_CREATE_FIRST_ADMINISTRATOR, 'warning');
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

        <td class="pageHeading" align="right"><?php echo HTML::form('adminlanguage', OSCOM::link(FILENAME_DEFAULT), 'get', null, ['session_id' => true]) . HTML::selectField('language', $languages_array, $languages_selected, 'onchange="this.form.submit();"') . '</form>'; ?></td>

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

  if ($Qcheck->check()) {
    $heading[] = array('text' => '<strong>' . HEADING_TITLE . '</strong>');

    $contents = array('form' => HTML::form('login', OSCOM::link(FILENAME_LOGIN, 'action=process')));
    $contents[] = array('text' => TEXT_USERNAME . '<br />' . HTML::inputField('username'));
    $contents[] = array('text' => '<br />' . TEXT_PASSWORD . '<br />' . HTML::passwordField('password'));
    $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(BUTTON_LOGIN, 'fa fa-sign-in'));
  } else {
    $heading[] = array('text' => '<strong>' . HEADING_TITLE . '</strong>');

    $contents = array('form' => HTML::form('login', OSCOM::link(FILENAME_LOGIN, 'action=create')));
    $contents[] = array('text' => TEXT_CREATE_FIRST_ADMINISTRATOR);
    $contents[] = array('text' => '<br />' . TEXT_USERNAME . '<br />' . HTML::inputField('username'));
    $contents[] = array('text' => '<br />' . TEXT_PASSWORD . '<br />' . HTML::passwordField('password'));
    $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(BUTTON_CREATE_ADMINISTRATOR, 'fa fa-sign-in'));
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
