<div class="contentContainer <?php echo (MODULE_CONTENT_LOGIN_NEW_USER_CONTENT_WIDTH == 'Half') ? 'grid_8' : 'grid_16'; ?>">
  <h2><?php echo HEADING_NEW_CUSTOMER; ?></h2>

  <div class="contentText">
    <p><?php echo TEXT_NEW_CUSTOMER; ?></p>
    <p><?php echo TEXT_NEW_CUSTOMER_INTRODUCTION; ?></p>

    <p align="right"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'triangle-1-e', tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL')); ?></p>
  </div>
</div>
