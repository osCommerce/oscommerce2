<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/
?>

<form name="paypalCredentialsManual" action="<?php echo tep_href_link('paypal.php', 'action=credentialsManual&subaction=process'); ?>" method="post" class="pp-form">

<h3>PayPal Live Account API Credentials</h3>

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

<h3>PayPal Sandbox Account API Credentials</h3>

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

<p>
  <?php echo $OSCOM_PayPal->drawButton('Save', null, 'success'); ?>
  or <a href="<?php echo tep_href_link('paypal.php'); ?>">cancel</a>
</p>

</form>
