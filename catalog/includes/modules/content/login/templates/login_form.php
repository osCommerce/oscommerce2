<?php
use OSC\OM\HTML;
use OSC\OM\OSCOM;
?>
<div class="login-form <?php echo (MODULE_CONTENT_LOGIN_FORM_CONTENT_WIDTH == 'Half') ? 'col-sm-6' : 'col-sm-12'; ?>">
  <div class="panel panel-success">
    <div class="panel-body">
      <h2><?php echo OSCOM::getDef('module_content_login_heading_returning_customer'); ?></h2>

      <p class="alert alert-success"><?php echo OSCOM::getDef('module_content_login_text_returning_customer'); ?></p>

      <?php echo HTML::form('login', OSCOM::link('login.php', 'action=process'), 'post', '', ['tokenize' => true]); ?>

      <div class="form-group">
        <?php echo HTML::inputField('email_address', NULL, 'autofocus="autofocus" required id="inputEmail" placeholder="' . OSCOM::getDef('entry_email_address_text') . '"', 'email'); ?>
      </div>

      <div class="form-group">
        <?php echo HTML::passwordField('password', NULL, 'required aria-required="true" id="inputPassword" autocomplete="new-password" placeholder="' . OSCOM::getDef('entry_password_text') . '"', 'password'); ?>
      </div>

      <p class="text-right"><?php echo HTML::button(OSCOM::getDef('image_button_login'), 'fa fa-sign-in', null, null, 'btn-success btn-block'); ?></p>

      </form>
    </div>
  </div>

    <p><?php echo '<a class="btn btn-default" role="button" href="' . OSCOM::link('password_forgotten.php') . '">' . OSCOM::getDef('module_content_login_text_password_forgotten') . '</a>'; ?></p>

</div>
