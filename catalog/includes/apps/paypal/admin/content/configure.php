<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/
?>

<h3>PayPal Configuration</h3>

<div id="appPayPalToolbar" style="padding-bottom: 15px;">
  <?php echo $OSCOM_PayPal->drawButton('Express Checkout', tep_href_link('paypal.php', 'action=configure&module=EC'), 'info', 'data-module="EC"'); ?>
  <?php echo $OSCOM_PayPal->drawButton('Direct Payment', tep_href_link('paypal.php', 'action=configure&module=DP'), 'info', 'data-module="DP"'); ?>
  <?php echo $OSCOM_PayPal->drawButton('Hosted Solution', tep_href_link('paypal.php', 'action=configure&module=HS'), 'info', 'data-module="HS"'); ?>
  <?php echo $OSCOM_PayPal->drawButton('Payments Standard', tep_href_link('paypal.php', 'action=configure&module=PS'), 'info', 'data-module="PS"'); ?>
  <?php echo $OSCOM_PayPal->drawButton('General', tep_href_link('paypal.php', 'action=configure&module=G'), 'info', 'data-module="G"'); ?>
</div>

<?php
  if ( $OSCOM_PayPal->isInstalled($current_module) || ($current_module == 'G') ) {
?>

<form name="paypalConfigure" action="<?php echo tep_href_link('paypal.php', 'action=configure&subaction=process&module=' . $current_module); ?>" method="post" class="pp-form">

<div class="pp-panel pp-panel-info" style="padding-bottom: 15px;">

<?php
    foreach ( $OSCOM_PayPal->getInputParameters($current_module) as $cfg ) {
      echo $cfg;
    }
?>

</div>

<p>

<?php
  echo $OSCOM_PayPal->drawButton('Save', null, 'success');

  if ( $current_module != 'G' ) {
    echo '  <span style="float: right;">' . $OSCOM_PayPal->drawButton('Uninstall &hellip;', '#', 'warning', 'data-button="paypalButtonUninstallModule"') . '</span>';
  }
?>

</p>

</form>

<?php
    if ( $current_module != 'G' ) {
      $uninstall_link = tep_href_link('paypal.php', 'action=configure&subaction=uninstall&module=' . $current_module);
?>

<div id="paypal-dialog-uninstall" title="Uninstall Module">
  <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you want to uninstall this module?</p>
</div>

<script>
$(function() {
  $('#paypal-dialog-uninstall').dialog({
    autoOpen: false,
    resizable: false,
    modal: true,
    buttons: {
      "Uninstall Module": function() {
        window.location = '<?php echo $uninstall_link; ?>';
      },
      "Cancel": function() {
        $(this).dialog('close');
      }
    }
  });

  $('a[data-button="paypalButtonUninstallModule"]').click(function(e) {
    e.preventDefault();

    $('#paypal-dialog-uninstall').dialog('open');
  });
});
</script>

<?php
    }
  } else {
    include(DIR_FS_CATALOG . 'includes/apps/paypal/modules/' . $current_module . '/content/install.php');
  }
?>

<script>
$(function() {
  $('#appPayPalToolbar a[data-module="<?php echo $current_module; ?>"]').addClass('pp-button-primary');
});
</script>
