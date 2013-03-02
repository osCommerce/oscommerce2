<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  $login_request = true;

  require('includes/application_top.php');
  require('includes/functions/password_funcs.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

// prepare to logout an active administrator if the login page is accessed again
  if (isset($_SESSION['admin'])) {
    $action = 'logoff';
  }

  if (osc_not_null($action)) {
    switch ($action) {
      case 'process':
        if (isset($_SESSION['redirect_origin']) && isset($_SESSION['redirect_origin']['auth_user'])) {
          $username = osc_db_prepare_input($_SESSION['redirect_origin']['auth_user']);
          $password = osc_db_prepare_input($_SESSION['redirect_origin']['auth_pw']);
        } else {
          $username = osc_db_prepare_input($_POST['username']);
          $password = osc_db_prepare_input($_POST['password']);
        }

        $actionRecorder = new actionRecorderAdmin('ar_admin_login', null, $username);

        if ($actionRecorder->canPerform()) {
          $check_query = osc_db_query("select id, user_name, user_password from " . TABLE_ADMINISTRATORS . " where user_name = '" . osc_db_input($username) . "'");

          if (osc_db_num_rows($check_query) == 1) {
            $check = osc_db_fetch_array($check_query);

            if (osc_validate_password($password, $check['user_password'])) {
// migrate old hashed password to new phpass password
              if (osc_password_type($check['user_password']) != 'phpass') {
                osc_db_query("update " . TABLE_ADMINISTRATORS . " set user_password = '" . osc_encrypt_password($password) . "' where id = '" . (int)$check['id'] . "'");
              }

              $_SESSION['admin'] = array('id' => $check['id'],
                                         'username' => $check['user_name']);

              $actionRecorder->_user_id = $_SESSION['admin']['id'];
              $actionRecorder->record();

              if (isset($_SESSION['redirect_origin'])) {
                $page = $_SESSION['redirect_origin']['page'];
                $get_string = http_build_query($_SESSION['redirect_origin']['get']);

                unset($_SESSION['redirect_origin']);

                osc_redirect(osc_href_link($page, $get_string));
              } else {
                osc_redirect(osc_href_link(FILENAME_DEFAULT));
              }
            }
          }

          $messageStack->add(ERROR_INVALID_ADMINISTRATOR, 'error');
        } else {
          $messageStack->add(sprintf(ERROR_ACTION_RECORDER, (defined('MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES') ? (int)MODULE_ACTION_RECORDER_ADMIN_LOGIN_MINUTES : 5)));
        }

        $actionRecorder->record(false);

        break;

      case 'logoff':
        unset($_SESSION['admin']);

        if (isset($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && !empty($_SERVER['PHP_AUTH_PW'])) {
          $_SESSION['auth_ignore'] = true;
        }

        osc_redirect(osc_href_link(FILENAME_DEFAULT));

        break;

      case 'create':
        $check_query = osc_db_query("select id from " . TABLE_ADMINISTRATORS . " limit 1");

        if (osc_db_num_rows($check_query) == 0) {
          $username = osc_db_prepare_input($_POST['username']);
          $password = osc_db_prepare_input($_POST['password']);

          osc_db_query("insert into " . TABLE_ADMINISTRATORS . " (user_name, user_password) values ('" . osc_db_input($username) . "', '" . osc_db_input(osc_encrypt_password($password)) . "')");
        }

        osc_redirect(osc_href_link(FILENAME_LOGIN));

        break;
    }
  }

  $languages = osc_get_languages();
  $languages_array = array();
  $languages_selected = DEFAULT_LANGUAGE;
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $languages_array[] = array('id' => $languages[$i]['code'],
                               'text' => $languages[$i]['name']);
    if ($languages[$i]['directory'] == $_SESSION['language']) {
      $languages_selected = $languages[$i]['code'];
    }
  }

  $admins_check_query = osc_db_query("select id from " . TABLE_ADMINISTRATORS . " limit 1");
  if (osc_db_num_rows($admins_check_query) < 1) {
    $messageStack->add(TEXT_CREATE_FIRST_ADMINISTRATOR, 'warning');
  }

  require(DIR_WS_INCLUDES . 'template_top.php');

  if (osc_db_num_rows($admins_check_query) > 0) {
    $form_action = 'process';
    $button_text = BUTTON_LOGIN;
  } else {
    $form_action = 'create';
    $button_text = BUTTON_CREATE_ADMINISTRATOR;
  }
?>

<?php echo osc_draw_form('login', FILENAME_LOGIN, 'action=' . $form_action); ?>

<div class="well offset3 span6" style="margin-top: 100px;">
  <fieldset>
    <legend>

<?php
  echo HEADING_TITLE;

	if ( count($languages_array) > 1 ) {
		echo osc_draw_form('adminlanguage', FILENAME_DEFAULT, '', 'get') .
         osc_draw_pull_down_menu('language', $languages_array, $languages_selected, 'onchange="this.form.submit();" class="pull-right"') . osc_hide_session_id() .
         '</form>';
	}
?>

    </legend>

    <div class="input-prepend span11">
      <span class="add-on"><i class="icon-user"></i></span><?php echo osc_draw_input_field('username', '', 'class="input-block-level" placeholder="' . TEXT_USERNAME . '"'); ?>
	  </div>

    <div class="input-prepend span11">
      <span class="add-on"><i class="icon-lock"></i></span><?php echo osc_draw_password_field('password', '','class="input-block-level" placeholder="' . TEXT_PASSWORD . '"'); ?>
    </div>

    <button class="btn btn-primary pull-right" type="submit"><?php echo $button_text; ?></button>
  </fieldset>
</div>

</form>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
