<div class="contentContainer <?php echo (MODULE_CONTENT_CREATE_ACCOUNT_LINK_CONTENT_WIDTH == 'Half') ? 'col-sm-6' : 'col-sm-12'; ?>">
  <h2><?php echo MODULE_CONTENT_LOGIN_HEADING_NEW_CUSTOMER; ?></h2>

  <div class="contentText">
    <div class="alert alert-info">
      <p><?php echo MODULE_CONTENT_LOGIN_TEXT_NEW_CUSTOMER; ?></p>
      <p><?php echo MODULE_CONTENT_LOGIN_TEXT_NEW_CUSTOMER_INTRODUCTION; ?></p>
    </div>

    <p align="right"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'), null, null, 'btn-info btn-block'); ?></p>
  </div>
</div>
