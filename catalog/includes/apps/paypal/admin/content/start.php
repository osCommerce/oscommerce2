<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/
?>

<div id="ppStartDashboard" style="width: 100%;">

<?php
  if ( $OSCOM_PayPal->isReqApiCountrySupported(STORE_COUNTRY) ) {
?>

  <div style="float: left; width: 50%;">
    <div style="padding: 2px;">
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
    </div>
  </div>

<?php
  }
?>

  <div style="float: left; width: 50%;">
    <div style="padding: 2px;">
      <h3 class="pp-panel-header-warning">Manage API Credentials</h3>
      <div class="pp-panel pp-panel-warning">
        <p>Enter your PayPal API Credentials and start selling with PayPal.</p>

        <p style="padding-top: 10px;"><?php echo $OSCOM_PayPal->drawButton('Manage Your API Credentials', tep_href_link('paypal.php', 'action=credentialsManual'), 'warning'); ?></p>
      </div>
    </div>
  </div>

  <div style="float: left; width: 50%;">
    <div style="padding: 2px;">
      <img src="<?php echo tep_catalog_href_link('images/apps/paypal/video_placeholder.png', '', 'SSL'); ?>" width="100%" />
    </div>
  </div>
</div>

<script>
$(function() {
  $('#ppStartDashboard > div:nth-child(2)').each(function() {
    if ( $(this).prev().height() < $(this).height() ) {
      $(this).prev().height($(this).height());
    } else {
      $(this).height($(this).prev().height());
    }
  });
});
</script>
