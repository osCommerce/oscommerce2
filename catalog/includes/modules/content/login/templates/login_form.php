<?php
use OSC\OM\HTML;
use OSC\OM\OSCOM;
?>
<div class="login-form <?php echo (MODULE_CONTENT_LOGIN_FORM_CONTENT_WIDTH == 'Half') ? 'col-sm-6' : 'col-sm-12'; ?>">
  <div class="panel panel-success">
    <div class="panel-body">
      <h2><?php echo MODULE_CONTENT_LOGIN_HEADING_RETURNING_CUSTOMER; ?></h2>

      <p class="alert alert-success"><?php echo MODULE_CONTENT_LOGIN_TEXT_RETURNING_CUSTOMER; ?></p>

      <?php echo HTML::form('login', OSCOM::link('login.php', 'action=process', 'SSL'), 'post', '', ['tokenize' => true]); ?>

      <div class="form-group">
        <?php echo HTML::inputField('email_address', NULL, 'autofocus="autofocus" required id="inputEmail" placeholder="' . ENTRY_EMAIL_ADDRESS_TEXT . '"', 'email'); ?>
      </div>

      <div class="form-group">
        <?php echo HTML::passwordField('password', NULL, 'required aria-required="true" id="inputPassword" autocomplete="new-password" placeholder="' . ENTRY_PASSWORD_TEXT . '"', 'password'); ?>
      </div>

      <p class="text-right"><?php echo HTML::button(IMAGE_BUTTON_LOGIN, 'fa fa-sign-in', null, null, 'btn-success btn-block'); ?></p>

      </form>
    </div>
  </div>

    <p><?php echo '<a class="btn btn-default" role="button" href="' . OSCOM::link('password_forgotten.php', '', 'SSL') . '">' . MODULE_CONTENT_LOGIN_TEXT_PASSWORD_FORGOTTEN . '</a>'; ?></p>

</div>
