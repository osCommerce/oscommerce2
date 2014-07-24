<div class="contentContainer <?php echo (MODULE_CONTENT_CREATE_ACCOUNT_LINK_CONTENT_WIDTH == 'Half') ? 'grid_8' : 'grid_16'; ?>">
  <h2><?php echo MODULE_CONTENT_LOGIN_HEADING_NEW_CUSTOMER; ?></h2>

  <div class="contentText">
    <p><?php echo MODULE_CONTENT_LOGIN_TEXT_NEW_CUSTOMER; ?></p>
    <p><?php echo MODULE_CONTENT_LOGIN_TEXT_NEW_CUSTOMER_INTRODUCTION; ?></p>

    <p align="right"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'), null, null, 'btn-success btn-block'); ?></p>
  </div>
</div>
