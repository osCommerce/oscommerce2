<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/
?>

<span style="float: right; padding-bottom: 15px;"><?php echo $OSCOM_PayPal->drawButton(IMAGE_BACK, tep_href_link('paypal.php', 'action=log&page=' . $HTTP_GET_VARS['page']), 'info'); ?></span>

<h3>PayPal Log</h3>

<table class="pp-table pp-table-hover">
  <thead>
    <tr>
      <th colspan="2">Request</th>
    </tr>
  </thead>
  <tbody>

<?php
  foreach ( $log_request as $key => $value ) {
?>

    <tr>
      <td><?php echo tep_output_string_protected($key); ?></td>
      <td><?php echo tep_output_string_protected($value); ?></td>
    </tr>

<?php
  }
?>

    <tr class="pp-table-header">
      <th colspan="2">Response</th>
    </tr>

<?php
  foreach ( $log_response as $key => $value ) {
?>

    <tr>
      <td><?php echo tep_output_string_protected($key); ?></td>
      <td><?php echo tep_output_string_protected($value); ?></td>
    </tr>

<?php
  }
?>

  </tbody>
</table>

