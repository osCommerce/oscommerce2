<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/
?>

<div style="text-align: right; padding-bottom: 15px;">
  <?php echo $OSCOM_PayPal->drawButton($OSCOM_PayPal->getDef('button_back'), tep_href_link('paypal.php', 'action=log&page=' . $HTTP_GET_VARS['page']), 'info'); ?>
</div>

<table class="pp-table pp-table-hover" width="100%">
  <thead>
    <tr>
      <th colspan="2"><?php echo $OSCOM_PayPal->getDef('table_heading_entries_request'); ?></th>
    </tr>
  </thead>
  <tbody>

<?php
  foreach ( $log_request as $key => $value ) {
?>

    <tr>
      <td width="25%"><?php echo tep_output_string_protected($key); ?></td>
      <td><?php echo tep_output_string_protected($value); ?></td>
    </tr>

<?php
  }
?>

  </tbody>
</table>

<table class="pp-table pp-table-hover" width="100%">
  <thead>
    <tr>
      <th colspan="2"><?php echo $OSCOM_PayPal->getDef('table_heading_entries_response'); ?></th>
    </tr>
  </thead>
  <tbody>

<?php
  foreach ( $log_response as $key => $value ) {
?>

    <tr>
      <td width="25%"><?php echo tep_output_string_protected($key); ?></td>
      <td><?php echo tep_output_string_protected($value); ?></td>
    </tr>

<?php
  }
?>

  </tbody>
</table>
