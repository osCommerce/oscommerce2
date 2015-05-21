<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;

  require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/password_reset.php');

  $error = false;

  if ( !isset($_GET['account']) || !isset($_GET['key']) ) {
    $error = true;

    $messageStack->add_session('password_forgotten', TEXT_NO_RESET_LINK_FOUND);
  }

  if ($error == false) {
    $email_address = HTML::sanitize($_GET['account']);
    $password_key = HTML::sanitize($_GET['key']);

    if ( tep_validate_email($email_address) == false ) {
      $error = true;

      $messageStack->add_session('password_forgotten', TEXT_NO_EMAIL_ADDRESS_FOUND);
    } elseif (strlen($password_key) != 40) {
      $error = true;

      $messageStack->add_session('password_forgotten', TEXT_NO_RESET_LINK_FOUND);
    } else {
      $Qcheck = $OSCOM_Db->prepare('select c.customers_id, c.customers_email_address, ci.password_reset_key, ci.password_reset_date from :table_customers c, :table_customers_info ci where c.customers_email_address = :customers_email_address and c.customers_id = ci.customers_info_id');
      $Qcheck->bindValue(':customers_email_address', $email_address);
      $Qcheck->execute();

      if ( $Qcheck->fetch() !== false ) {
        if ( empty($Qcheck->value('password_reset_key')) || ($Qcheck->value('password_reset_key') != $password_key) || (strtotime($Qcheck->value('password_reset_date') . ' +1 day') <= time()) ) {
          $error = true;

          $messageStack->add_session('password_forgotten', TEXT_NO_RESET_LINK_FOUND);
        }
      } else {
        $error = true;

        $messageStack->add_session('password_forgotten', TEXT_NO_EMAIL_ADDRESS_FOUND);
      }
    }
  }

  if ($error == true) {
    tep_redirect(tep_href_link('password_forgotten.php'));
  }

  if (isset($_GET['action']) && ($_GET['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
    $password_new = HTML::sanitize($_POST['password']);
    $password_confirmation = HTML::sanitize($_POST['confirmation']);

    if (strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH) {
      $error = true;

      $messageStack->add('password_reset', ENTRY_PASSWORD_NEW_ERROR);
    } elseif ($password_new != $password_confirmation) {
      $error = true;

      $messageStack->add('password_reset', ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING);
    }

    if ($error == false) {
      $OSCOM_Db->save('customers', ['customers_password' => tep_encrypt_password($password_new)], ['customers_id' => $Qcheck->valueInt('customers_id')]);

      $OSCOM_Db->save('customers_info', ['customers_info_date_account_last_modified' => 'now()', 'password_reset_key' => 'null', 'password_reset_date' => 'null'], ['customers_info_id' => $Qcheck->valueInt('customers_id')]);

      $messageStack->add_session('login', SUCCESS_PASSWORD_RESET, 'success');

      tep_redirect(tep_href_link('login.php', '', 'SSL'));
    }
  }

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link('login.php', '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2);

  require('includes/template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php
  if ($messageStack->size('password_reset') > 0) {
    echo $messageStack->output('password_reset');
  }
?>

<?php echo tep_draw_form('password_reset', tep_href_link('password_reset.php', 'account=' . $email_address . '&key=' . $password_key . '&action=process', 'SSL'), 'post', 'class="form-horizontal" role="form"', true); ?>

<div class="contentContainer">
  <div class="contentText">
    <div class="alert alert-info"><?php echo TEXT_MAIN; ?></div>

    <p class="inputRequirement text-right"><?php echo FORM_REQUIRED_INFORMATION; ?></p>

    <div class="form-group has-feedback">
      <label for="inputPassword" class="control-label col-sm-3"><?php echo ENTRY_PASSWORD; ?></label>
      <div class="col-sm-9">
        <?php
        echo tep_draw_password_field('password', NULL, 'minlength="' . ENTRY_PASSWORD_MIN_LENGTH . '" required aria-required="true" id="inputPassword" placeholder="' . ENTRY_PASSWORD_TEXT . '"');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>
    <div class="form-group has-feedback">
      <label for="inputConfirmation" class="control-label col-sm-3"><?php echo ENTRY_PASSWORD_CONFIRMATION; ?></label>
      <div class="col-sm-9">
        <?php
        echo tep_draw_password_field('confirmation', NULL, 'minlength="' . ENTRY_PASSWORD_MIN_LENGTH . '" required aria-required="true" id="inputConfirmation" placeholder="' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '"');
        echo FORM_REQUIRED_INPUT;
        ?>
      </div>
    </div>

  </div>

  <div class="text-right">
    <?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', null, 'primary', null, 'btn-success'); ?>
  </div>
</div>

</form>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
