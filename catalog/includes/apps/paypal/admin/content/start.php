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
  <div style="float: left; width: 50%;">
    <div style="padding: 2px;">
      <h3 class="pp-panel-header-info"><?php echo $OSCOM_PayPal->getDef('onboarding_intro_title'); ?></h3>
      <div class="pp-panel pp-panel-info">
        <?php echo $OSCOM_PayPal->getDef('onboarding_intro_body', array('button_retrieve_live_credentials' => $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_retrieve_live_credentials'), tep_href_link('paypal.php', 'action=start&subaction=process&type=live'), 'info'), 'button_retrieve_sandbox_credentials' => $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_retrieve_sandbox_credentials'), tep_href_link('paypal.php', 'action=start&subaction=process&type=sandbox'), 'info'))); ?>
      </div>
    </div>
  </div>

  <div style="float: left; width: 50%;">
    <div style="padding: 2px;">
      <h3 class="pp-panel-header-warning"><?php echo $OSCOM_PayPal->getDef('manage_credentials_title'); ?></h3>
      <div class="pp-panel pp-panel-warning">
        <?php echo $OSCOM_PayPal->getDef('manage_credentials_body', array('button_manage_credentials' => $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_manage_credentials'), tep_href_link('paypal.php', 'action=credentials'), 'warning'))); ?>
      </div>
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
