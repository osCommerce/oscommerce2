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
      <h3 class="pp-panel-header-info">Retrieve API Credentials</h3>
      <div class="pp-panel pp-panel-info">
        <p>Start selling and earning faster by allowing osCommerce to automatically and securely configure your online store with your PayPal API credentials.</p>

        <p>Don't have a PayPal Account? We can do this for new and existing PayPal sellers.</p>

        <p style="text-align: center;">
          <?php echo $OSCOM_PayPal->drawButton('Retrieve Live Credentials', tep_href_link('paypal.php', 'action=start&subaction=process&type=live'), 'info'); ?>
          <?php echo $OSCOM_PayPal->drawButton('Retrieve Sandbox Credentials', tep_href_link('paypal.php', 'action=start&subaction=process&type=sandbox'), 'info'); ?>
        </p>

        <p>Live PayPal Accounts are for live shops ready to accept payments. Sandbox PayPal Accounts are used for testing purposes where orders are processed but no actual payments are made.</p>
      </div>
    </td>
    <td width="50%" valign="top">
      <h3 class="pp-panel-header-warning">Manage API Credentials</h3>
      <div class="pp-panel pp-panel-warning">
        <p>Enter your PayPal API Credentials and start selling with PayPal.</p>

        <p style="padding-top: 10px;"><?php echo $OSCOM_PayPal->drawButton('Manage Your API Credentials', tep_href_link('paypal.php', 'action=credentialsManual'), 'warning'); ?></p>
      </div>
    </td>
  </tr>
</table>
