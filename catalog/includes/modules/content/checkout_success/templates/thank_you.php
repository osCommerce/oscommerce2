<div class="contentText">
  <div class="alert alert-success">
    <?php echo MODULE_CONTENT_CHECKOUT_SUCCESS_TEXT_SUCCESS; ?>
  </div>
</div>

<div class="contentText">
  <div class="alert alert-info">
    <?php echo sprintf(MODULE_CONTENT_CHECKOUT_SUCCESS_TEXT_SEE_ORDERS, tep_href_link('account_history.php', '', 'SSL')) . '<br /><br />' . sprintf(MODULE_CONTENT_CHECKOUT_SUCCESS_TEXT_CONTACT_STORE_OWNER, tep_href_link('contact_us.php')); ?>
  </div>
</div>

<div class="contentText">
  <div class="page-header">
    <h4><?php echo MODULE_CONTENT_CHECKOUT_SUCCESS_TEXT_THANKS_FOR_SHOPPING; ?></h4>
  </div>
</div>
