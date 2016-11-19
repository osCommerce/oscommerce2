<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Hash;
  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  $login_request = true;

  require('includes/application_top.php');

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
            if (Hash::verify($password, $Qadmin->value('user_password'))) {
// migrate old hashed password to new php password_hash
              if (Hash::needsRehash($Qadmin->value('user_password'))) {
                $OSCOM_Db->save('administrators', [
                  'user_password' => Hash::encrypt($password)
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
                $get_string = http_build_query($_SESSION['redirect_origin']['get']);

                unset($_SESSION['redirect_origin']);

                OSCOM::redirect($page, $get_string);
              } else {
                OSCOM::redirect(FILENAME_DEFAULT);
              }
            }
          }

          if (isset($_POST['username'])) {
            $OSCOM_MessageStack->add(OSCOM::getDef('error_invalid_administrator'), 'error');
          }
        } else {
          $OSCOM_MessageStack->add(OSCOM::getDef('error_action_recorder', ['module_action_recorder_admin_login_minutes' => (defined('MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES') ? (int)MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES : 5)]));
        }

        if (isset($_POST['username'])) {
          $actionRecorder->record(false);
        }

        break;

      case 'logoff':
        $OSCOM_Hooks->call('Account', 'LogoutBefore');

        unset($_SESSION['admin']);

        if (isset($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && !empty($_SERVER['PHP_AUTH_PW'])) {
          $_SESSION['auth_ignore'] = true;
        }

        $OSCOM_Hooks->call('Account', 'LogoutAfter');

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
              'user_password' => Hash::encrypt($password)
            ]);
          }
        }

        OSCOM::redirect(FILENAME_LOGIN);

        break;
    }
  }

  $Qcheck = $OSCOM_Db->get('administrators', 'id', null, null, 1);

  if (!$Qcheck->check()) {
    $OSCOM_MessageStack->add(OSCOM::getDef('text_create_first_administrator'), 'warning');
  }

  require($oscTemplate->getFile('template_top.php'));
?>

<h2><i class="fa fa-home"></i> <a href="<?= OSCOM::link('login.php'); ?>"><?= STORE_NAME; ?></a></h3>

<?php
  $heading = array();
  $contents = array();

  if ($Qcheck->check()) {
    $heading[] = array('text' => OSCOM::getDef('heading_title'));

    $contents = array('form' => HTML::form('login', OSCOM::link(FILENAME_LOGIN, 'action=process')));
    $contents[] = array('text' => OSCOM::getDef('text_username') . '<br />' . HTML::inputField('username'));
    $contents[] = array('text' => OSCOM::getDef('text_password') . '<br />' . HTML::passwordField('password'));
    $contents[] = array('text' => HTML::button(OSCOM::getDef('button_login'), 'fa fa-sign-in', null, null, 'btn-primary'));
  } else {
    $heading[] = array('text' => OSCOM::getDef('heading_title'));

    $contents = array('form' => HTML::form('login', OSCOM::link(FILENAME_LOGIN, 'action=create')));
    $contents[] = array('text' => OSCOM::getDef('text_create_first_administrator'));
    $contents[] = array('text' => OSCOM::getDef('text_username') . '<br />' . HTML::inputField('username'));
    $contents[] = array('text' => OSCOM::getDef('text_password') . '<br />' . HTML::passwordField('password'));
    $contents[] = array('text' => HTML::button(OSCOM::getDef('button_create_administrator'), 'fa fa-sign-in', null, null, 'btn-primary'));
  }

  echo HTML::panel($heading, $contents);

  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
