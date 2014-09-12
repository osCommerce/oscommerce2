<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/
?>

<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td width="60%" valign="top">
      <h3 class="pp-panel-header-warning">PayPal Payments Standard</h3>
      <div class="pp-panel pp-panel-warning">
        <ul>
          <li>Accept credit cards and PayPal on your online store.</li>
          <li>Simplified PCI compliance standards.</li>
          <li>Accept multiple currencies worldwide.</li>
          <li>Optimized mobile checkout experience.</li>
        </ul>

        <p>PayPal handles the payment acceptance experience and returns the customer to your store after payment has been made.</p>
        <p>For new buyers, signing up for a PayPal account is optional meaning customers can complete their payments first and then decide to save their information in a PayPal account for future purchases.</p>
      </div>

      <p>
        <?php echo $OSCOM_PayPal->drawButton('Install Module', tep_href_link('paypal.php', 'action=configure&subaction=install&module=' . $current_module), 'success'); ?>
      </p>
    </td>
    <td width="40%" valign="top" style="padding-left: 5px;">
      <img src="<?php echo tep_catalog_href_link('images/apps/paypal/video_placeholder.png', '', 'SSL'); ?>" width="100%" />
    </td>
  </tr>
</table>
