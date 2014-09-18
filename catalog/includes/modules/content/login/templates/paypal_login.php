<div class="contentContainer <?php echo (OSCOM_APP_PAYPAL_LOGIN_CONTENT_WIDTH == 'Half') ? 'grid_8' : 'grid_16'; ?>">
  <h2><?php echo MODULE_CONTENT_PAYPAL_LOGIN_TEMPLATE_TITLE; ?></h2>

  <div class="contentText">

<?php
  if ( OSCOM_APP_PAYPAL_LOGIN_STATUS == '0' ) {
    echo '    <p class="messageStackError">' . MODULE_CONTENT_PAYPAL_LOGIN_TEMPLATE_SANDBOX . '</p>';
  }
?>

    <p><?php echo MODULE_CONTENT_PAYPAL_LOGIN_TEMPLATE_CONTENT; ?></p>

    <div id="PayPalLoginButton" style="text-align: right; padding-top: 5px;"></div>
  </div>
</div>

<script type="text/javascript" src="https://www.paypalobjects.com/js/external/api.js"></script>
<script type="text/javascript">
paypal.use( ["login"], function(login) {
  login.render ({

<?php
  if ( OSCOM_APP_PAYPAL_LOGIN_STATUS == '0' ) {
    echo '    "authend": "sandbox",';
  }

  if ( OSCOM_APP_PAYPAL_LOGIN_THEME == 'Neutral' ) {
    echo '    "theme": "neutral",';
  }

  if ( defined('MODULE_CONTENT_PAYPAL_LOGIN_LANGUAGE_LOCALE') && tep_not_null(MODULE_CONTENT_PAYPAL_LOGIN_LANGUAGE_LOCALE) ) {
    echo '    "locale": "' . MODULE_CONTENT_PAYPAL_LOGIN_LANGUAGE_LOCALE . '",';
  }
?>

    "appid": "<?php echo (OSCOM_APP_PAYPAL_LOGIN_STATUS == '1') ? OSCOM_APP_PAYPAL_LOGIN_LIVE_CLIENT_ID : OSCOM_APP_PAYPAL_LOGIN_SANDBOX_CLIENT_ID; ?>",
    "scopes": "<?php echo implode(' ', $use_scopes); ?>",
    "containerid": "PayPalLoginButton",
    "returnurl": "<?php echo str_replace('&amp;', '&', tep_href_link(FILENAME_LOGIN, 'action=paypal_login', 'SSL', false)); ?>"
  });
});
</script>
