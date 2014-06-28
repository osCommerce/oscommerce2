<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/
?>

<table border="0" width="100%" cellspacing="0" cellpadding="4">
  <tr>
    <td width="50%" valign="top">
      <h3>For Starters</h3>

      <div class="pp-panel pp-panel-info">
        <p>Start selling and earning faster by allowing osCommerce to automatically and securely configure your online store with your PayPal account credentials. Don't know what your PayPal API username, API signature, or API passwords are? Allow us to set things up right for you!</p>

        <p>Don't have a PayPal Account? We can do this for new and existing PayPal sellers.</p>

        <p style="text-align: center;">
          <?php echo $OSCOM_PayPal->drawButton('Start with a Live Account', tep_href_link('paypal.php', 'action=start&subaction=process&type=live'), 'info'); ?>
          <?php echo $OSCOM_PayPal->drawButton('Start with a Sandbox Account', tep_href_link('paypal.php', 'action=start&subaction=process&type=sandbox'), 'info'); ?>
        </p>

        <p>Live PayPal Accounts are for live shops ready to start receiving funds. Sandbox PayPal Accounts are used for testing purposes where orders are processed but no actual funds are sent or received.</p>
      </div>
    </td>
    <td width="50%" valign="top">
      <h3>For Experts</h3>

      <div class="pp-panel pp-panel-warning">
        <p>Already know your PayPal API Credentials?</p>

        <p style="padding-top: 10px;"><?php echo $OSCOM_PayPal->drawButton('Manage Your API Credentials', tep_href_link('paypal.php', 'action=credentialsManual'), 'warning'); ?></p>
      </div>
    </td>
  </tr>
</table>

<h3>Supported Modules</h3>

<p>Your PayPal Account API Credentials can be used for the following payment methods:</p>

<table class="pp-table pp-table-hover">
  <thead>
    <tr>
      <th>Modules</th>
      <th style="text-align: center;">Status</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><span class="ui-icon ui-icon-gear" style="display: inline-block;"></span><a href="<?php echo tep_href_link('paypal.php', 'action=configure&module=EC'); ?>">PayPal Express Checkout</a></td>
      <td style="text-align: center;">

<?php
  if ( OSCOM_APP_PAYPAL_EC_STATUS == '1' ) {
    echo '<span style="color: #3fad3b; font-weight: bold;">Live</span>';
  } elseif ( OSCOM_APP_PAYPAL_EC_STATUS == '0' ) {
    echo '<span style="color: #d56d28; font-weight: bold;">Sandbox</span>';
  } elseif ( OSCOM_APP_PAYPAL_EC_STATUS == '-1' ) {
    echo '<span style="color: #d32828; font-weight: bold;">Disabled</span>';
  } else {
    echo '<span style="color: #d32828; font-weight: bold;">Not Installed</span>';
  }
?>

      </td>
    </tr>
    <tr>
      <td><span class="ui-icon ui-icon-gear" style="display: inline-block;"></span><a href="<?php echo tep_href_link('paypal.php', 'action=configure&module=DP'); ?>">PayPal Payments Pro (Direct Payment)</a></td>
      <td style="text-align: center;">

<?php
  if ( OSCOM_APP_PAYPAL_DP_STATUS == '1' ) {
    echo '<span style="color: #3fad3b; font-weight: bold;">Live</span>';
  } elseif ( OSCOM_APP_PAYPAL_DP_STATUS == '0' ) {
    echo '<span style="color: #d56d28; font-weight: bold;">Sandbox</span>';
  } elseif ( OSCOM_APP_PAYPAL_DP_STATUS == '-1' ) {
    echo '<span style="color: #d32828; font-weight: bold;">Disabled</span>';
  } else {
    echo '<span style="color: #d32828; font-weight: bold;">Not Installed</span>';
  }
?>

      </td>
    </tr>
    <tr>
      <td><span class="ui-icon ui-icon-gear" style="display: inline-block;"></span><a href="<?php echo tep_href_link('paypal.php', 'action=configure&module=HS'); ?>">PayPal Payments Pro (Hosted Solution)</a></td>
      <td style="text-align: center;">

<?php
  if ( OSCOM_APP_PAYPAL_HS_STATUS == '1' ) {
    echo '<span style="color: #3fad3b; font-weight: bold;">Live</span>';
  } elseif ( OSCOM_APP_PAYPAL_HS_STATUS == '0' ) {
    echo '<span style="color: #d56d28; font-weight: bold;">Sandbox</span>';
  } elseif ( OSCOM_APP_PAYPAL_HS_STATUS == '-1' ) {
    echo '<span style="color: #d32828; font-weight: bold;">Disabled</span>';
  } else {
    echo '<span style="color: #d32828; font-weight: bold;">Not Installed</span>';
  }
?>

      </td>
    </tr>
    <tr>
      <td><span class="ui-icon ui-icon-gear" style="display: inline-block;"></span><a href="<?php echo tep_href_link('paypal.php', 'action=configure&module=PS'); ?>">PayPal Payments Standard</a></td>
      <td style="text-align: center;">

<?php
  if ( OSCOM_APP_PAYPAL_PS_STATUS == '1' ) {
    echo '<span style="color: #3fad3b; font-weight: bold;">Live</span>';
  } elseif ( OSCOM_APP_PAYPAL_PS_STATUS == '0' ) {
    echo '<span style="color: #d56d28; font-weight: bold;">Sandbox</span>';
  } elseif ( OSCOM_APP_PAYPAL_PS_STATUS == '-1' ) {
    echo '<span style="color: #d32828; font-weight: bold;">Disabled</span>';
  } else {
    echo '<span style="color: #d32828; font-weight: bold;">Not Installed</span>';
  }
?>

      </td>
    </tr>
  </tbody>
</table>
