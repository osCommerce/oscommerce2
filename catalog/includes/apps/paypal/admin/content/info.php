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
      <h3 class="pp-panel-header-info">Online Documentation</h3>
      <div class="pp-panel pp-panel-info">
        <p>Online documentation is available at the osCommerce Library website:</p>

        <p><?php echo $OSCOM_PayPal->drawButton('Online Documentation', 'http://library.oscommerce.com/Package&paypal&oscom23', 'info', 'target="_blank"'); ?></p>
      </div>
    </div>
  </div>

  <div style="float: left; width: 50%;">
    <div style="padding: 2px;">
      <h3 class="pp-panel-header-warning">Online Forum</h3>
      <div class="pp-panel pp-panel-warning">
        <p>Support enquiries can be posted at the osCommerce Support Forum PayPal Channel:</p>

        <p><?php echo $OSCOM_PayPal->drawButton('osCommerce Support Forums', 'http://forums.oscommerce.com/forum/54-paypal/', 'warning', 'target="_blank"'); ?></p>
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

  OSCOM.APP.PAYPAL.versionCheck();
});
</script>
