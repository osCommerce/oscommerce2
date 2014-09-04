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
    <td width="40%" valign="top">
      <div class="pp-panel pp-panel-warning">
        <h3>PayPal Payments Pro (Hosted Solution)</h3>

        <ul>
          <li>Seamless experience for your customers.</li>
          <li>No need for a merchant bank account or payment gateway.</li>
          <li>Fully PCI compliant.</li>
        </ul>

        <p>Hosted Solution offers a way to securely accept credit and debit card or PayPal payments without capturing or storing card information directly on your website. Payment information is collected by PayPal using an inline frame in your online store checkout procedure.</p>
      </div>

      <p>
        <?php echo $OSCOM_PayPal->drawButton('Install Module', tep_href_link('paypal.php', 'action=configure&subaction=install&module=' . $current_module), 'success'); ?>
      </p>
    </td>
    <td width="60%" valign="top">
      <img src="<?php echo tep_catalog_href_link('images/apps/paypal/video_placeholder.png', '', 'SSL'); ?>" width="100%" />
    </td>
  </tr>
</table>
