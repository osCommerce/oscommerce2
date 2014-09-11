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
      <h3 class="pp-panel-header-warning">PayPal Payments Pro (Direct Payment)</h3>
      <div class="pp-panel pp-panel-warning">
        <ul>
          <li>Direct Payment lets you design and host your own checkout pages.</li>
          <li>You keep your customers on your site instead of having them pay on PayPal; PayPal remains invisible.</li>
        </ul>

        <p>Direct Payment enables buyers to pay by credit or debit card during your checkout flow. You have complete control over the experience; however, you must consider PCI compliance.</p>
        <p>Please note: Direct Payment is not a stand-alone product - you are required to enable and use Express Checkout together with Direct Payment.</p>
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
