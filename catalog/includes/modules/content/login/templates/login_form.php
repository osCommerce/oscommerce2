<div class="contentContainer <?php echo (MODULE_CONTENT_LOGIN_FORM_CONTENT_WIDTH == 'Half') ? 'col-sm-6' : 'col-sm-12'; ?>">
  <h2><?php echo MODULE_CONTENT_LOGIN_HEADING_RETURNING_CUSTOMER; ?></h2>

  <div class="contentText">
    <div class="alert alert-success">
      <p><?php echo MODULE_CONTENT_LOGIN_TEXT_RETURNING_CUSTOMER; ?></p>
    </div>

    <?php echo tep_draw_form('login', tep_href_link(FILENAME_LOGIN, 'action=process', 'SSL'), 'post', 'class="form-horizontal" role="form"', true); ?>
    
    <div class="form-group">
      <label for="inputEmail" class="control-label col-xs-4"><?php echo ENTRY_EMAIL_ADDRESS; ?></label>
      <div class="col-xs-8">
        <?php echo tep_draw_input_field('email_address', NULL, 'autofocus="autofocus" required aria-required="true" id="inputEmail" placeholder="' . ENTRY_EMAIL_ADDRESS . '"', 'email'); ?>
      </div>
    </div>

    <div class="form-group">
      <label for="inputPassword" class="control-label col-xs-4"><?php echo ENTRY_PASSWORD; ?></label>
      <div class="col-xs-8">
        <?php echo tep_draw_password_field('password', NULL, 'required aria-required="true" id="inputPassword" placeholder="' . ENTRY_PASSWORD . '"'); ?>
      </div>
    </div>

    <p class="text-right"><?php echo tep_draw_button(IMAGE_BUTTON_LOGIN, 'glyphicon glyphicon-log-in', null, 'primary', null, 'btn-success btn-block'); ?></p>

    </form>
    
    <hr>
    
    <p><?php echo '<a href="' . tep_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL') . '">' . MODULE_CONTENT_LOGIN_TEXT_PASSWORD_FORGOTTEN . '</a>'; ?></p>
    
  </div>
</div>
