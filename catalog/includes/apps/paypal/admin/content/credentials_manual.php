<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/
?>

<div id="appPayPalToolbar" style="padding-bottom: 15px;">
  <?php echo $OSCOM_PayPal->drawButton('PayPal', tep_href_link('paypal.php', 'action=credentialsManual&module=PP'), 'info', 'data-module="PP"'); ?>
  <?php echo $OSCOM_PayPal->drawButton('Payflow', tep_href_link('paypal.php', 'action=credentialsManual&module=PF'), 'info', 'data-module="PF"'); ?>
</div>

<form name="paypalCredentialsManual" action="<?php echo tep_href_link('paypal.php', 'action=credentialsManual&subaction=process&module=' . $current_module); ?>" method="post" class="pp-form">

<?php
  if ( $current_module == 'PP' ) {
?>

<h3 class="pp-panel-header-warning">Live Credentials</h3>
<div class="pp-panel pp-panel-warning">
  <table>
    <tr>
      <td width="420px" valign="top">
        <div>
          <p>
            <label for="live_username">API Username</label>
            <?php echo tep_draw_input_field('live_username', OSCOM_APP_PAYPAL_LIVE_API_USERNAME); ?>
          </p>
        </div>

        <div>
          <p>
            <label for="live_password">API Password</label>
            <?php echo tep_draw_input_field('live_password', OSCOM_APP_PAYPAL_LIVE_API_PASSWORD); ?>
          </p>
        </div>

        <div>
          <p>
            <label for="live_signature">API Signature</label>
            <?php echo tep_draw_input_field('live_signature', OSCOM_APP_PAYPAL_LIVE_API_SIGNATURE); ?>
          </p>
        </div>
      </td>
      <td width="420px" valign="top">
        <div>
          <p>
            <label for="live_email">PayPal E-Mail Address</label>
            <?php echo tep_draw_input_field('live_email', OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL); ?>
          </p>
        </div>

        <div>
          <p>
            <label for="live_email_primary">Primary E-Mail Address</label>
            <?php echo tep_draw_input_field('live_email_primary', OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL_PRIMARY); ?>
          </p>
          <p><em>The primary e-mail address only needs to be entered if it differs to the e-mail address entered above. This must be the e-mail address of the account marked as primary in your PayPal account settings.</em></p>
        </div>
      </td>
    </tr>
  </table>
</div>

<h3 class="pp-panel-header-warning">Sandbox Credentials</h3>
<div class="pp-panel pp-panel-warning">
  <table>
    <tr>
      <td width="420px" valign="top">
        <div>
          <p>
            <label for="sandbox_username">API Username</label>
            <?php echo tep_draw_input_field('sandbox_username', OSCOM_APP_PAYPAL_SANDBOX_API_USERNAME); ?>
          </p>
        </div>

        <div>
          <p>
            <label for="sandbox_password">API Password</label>
            <?php echo tep_draw_input_field('sandbox_password', OSCOM_APP_PAYPAL_SANDBOX_API_PASSWORD); ?>
          </p>
        </div>

        <div>
          <p>
            <label for="sandbox_signature">API Signature</label>
            <?php echo tep_draw_input_field('sandbox_signature', OSCOM_APP_PAYPAL_SANDBOX_API_SIGNATURE); ?>
          </p>
        </div>
      </td>
      <td width="420px" valign="top">
        <div>
          <p>
            <label for="sandbox_email">PayPal E-Mail Address</label>
            <?php echo tep_draw_input_field('sandbox_email', OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL); ?>
          </p>
        </div>

        <div>
          <p>
            <label for="sandbox_email_primary">Primary E-Mail Address</label>
            <?php echo tep_draw_input_field('sandbox_email_primary', OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL_PRIMARY); ?>
          </p>
          <p><em>The primary e-mail address only needs to be entered if it differs to the e-mail address entered above. This must be the e-mail address of the account marked as primary in your PayPal account settings.</em></p>
        </div>
      </td>
    </tr>
  </table>
</div>

<?php
  } elseif ( $current_module == 'PF' ) {
?>

<h3 class="pp-panel-header-warning">Live Credentials</h3>
<div class="pp-panel pp-panel-warning">
  <div>
    <p>
      <label for="live_partner">Partner</label>
      <?php echo tep_draw_input_field('live_partner', OSCOM_APP_PAYPAL_PF_LIVE_PARTNER); ?>
    </p>
  </div>

  <div>
    <p>
      <label for="live_vendor">Merchant Login</label>
      <?php echo tep_draw_input_field('live_vendor', OSCOM_APP_PAYPAL_PF_LIVE_VENDOR); ?>
    </p>
  </div>

  <div>
    <p>
      <label for="live_user">User</label>
      <?php echo tep_draw_input_field('live_user', OSCOM_APP_PAYPAL_PF_LIVE_USER); ?>
    </p>
  </div>

  <div>
    <p>
      <label for="live_password">Password</label>
      <?php echo tep_draw_input_field('live_password', OSCOM_APP_PAYPAL_PF_LIVE_PASSWORD); ?>
    </p>
  </div>
</div>

<h3 class="pp-panel-header-warning">Sandbox Credentials</h3>
<div class="pp-panel pp-panel-warning">
  <div>
    <p>
      <label for="sandbox_partner">Partner</label>
      <?php echo tep_draw_input_field('sandbox_partner', OSCOM_APP_PAYPAL_PF_SANDBOX_PARTNER); ?>
    </p>
  </div>

  <div>
    <p>
      <label for="sandbox_vendor">Merchant Login</label>
      <?php echo tep_draw_input_field('sandbox_vendor', OSCOM_APP_PAYPAL_PF_SANDBOX_VENDOR); ?>
    </p>
  </div>

  <div>
    <p>
      <label for="sandbox_user">User</label>
      <?php echo tep_draw_input_field('sandbox_user', OSCOM_APP_PAYPAL_PF_SANDBOX_USER); ?>
    </p>
  </div>

  <div>
    <p>
      <label for="sandbox_password">Password</label>
      <?php echo tep_draw_input_field('sandbox_password', OSCOM_APP_PAYPAL_PF_SANDBOX_PASSWORD); ?>
    </p>
  </div>
</div>

<?php
  }
?>

<p><?php echo $OSCOM_PayPal->drawButton('Save', null, 'success'); ?></p>

</form>

<script>
$(function() {
  $('#appPayPalToolbar a[data-module="<?php echo $current_module; ?>"]').addClass('pp-button-primary');
});
</script>
