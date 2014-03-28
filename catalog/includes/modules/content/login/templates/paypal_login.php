<div class="contentContainer <?php echo (MODULE_CONTENT_PAYPAL_LOGIN_CONTENT_WIDTH == 'Half') ? 'grid_8' : 'grid_16'; ?>">
  <h2><?php echo MODULE_CONTENT_PAYPAL_LOGIN_TEMPLATE_TITLE; ?></h2>

  <div class="contentText">
    <p><?php echo MODULE_CONTENT_PAYPAL_LOGIN_TEMPLATE_CONTENT; ?></p>

    <div id="PayPalLoginButton" style="text-align: right; padding-top: 5px;"></div>
  </div>
</div>

<script type="text/javascript" src="https://www.paypalobjects.com/js/external/api.js"></script>
<script type="text/javascript">
paypal.use( ["login"], function(login) {
  login.render ({

<?php
  if ( MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE == 'Sandbox' ) {
    echo '    "authend": "sandbox",';
  }
?>

    "appid": "<?php echo MODULE_CONTENT_PAYPAL_LOGIN_CLIENT_ID; ?>",
    "scopes": "profile email address phone https://uri.paypal.com/services/paypalattributes https://uri.paypal.com/services/expresscheckout",
    "containerid": "PayPalLoginButton",
    "returnurl": "<?php echo str_replace('&amp;', '&', tep_href_link(FILENAME_LOGIN, 'action=paypal_login', 'SSL')); ?>"
  });
});
</script>
