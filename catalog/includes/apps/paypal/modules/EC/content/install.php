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
    <td width="60%" valign="top">
      <h3 class="pp-panel-header-warning">PayPal Express Checkout</h3>
      <div class="pp-panel pp-panel-warning">
        <ul>
          <li>Adds PayPal to your online store checkout.</li>
          <li>Sell to customers faster who are more likely to pay with PayPal.</li>
          <li>Access to new customers who prefer PayPal.</li>
          <li>Customers can use guest checkout pages and avoid entering billing and shipping information.</li>
          <li>Optimized mobile checkout experience.</li>
        </ul>

        <p>Express Checkout offers the ease of convenience and security of PayPal, can be set up in minutes, and turns more shoppers into buyers.</p>
      </div>

      <p>
        <?php echo $OSCOM_PayPal->drawButton('Install Module', tep_href_link('paypal.php', 'action=configure&subaction=install&module=' . $current_module), 'success'); ?>
      </p>
    </td>
    <td width="40%" valign="top">
      <img src="<?php echo tep_catalog_href_link('images/apps/paypal/video_placeholder.png', '', 'SSL'); ?>" width="100%" />
    </td>
  </tr>
</table>
