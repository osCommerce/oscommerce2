<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/
?>

<div id="appBraintreeToolbar" style="padding-bottom: 15px;">

<?php
  foreach ( $OSCOM_Braintree->getModules() as $m ) {
    if ( $OSCOM_Braintree->isInstalled($m) ) {
      echo $OSCOM_Braintree->drawButton($OSCOM_Braintree->getModuleInfo($m, 'short_title'), tep_href_link('braintree.php', 'action=configure&module=' . $m), 'info', 'data-module="' . $m . '"') . "\n";
    }
  }
?>

  <?php echo $OSCOM_Braintree->drawButton($OSCOM_Braintree->getDef('section_general'), tep_href_link('braintree.php', 'action=configure&module=G'), 'info', 'data-module="G"'); ?>
</div>

<?php
  $current_module_title = ($current_module != 'G') ? $OSCOM_Braintree->getModuleInfo($current_module, 'title') : $OSCOM_Braintree->getDef('section_general');
  $req_notes = ($current_module != 'G') ? $OSCOM_Braintree->getModuleInfo($current_module, 'req_notes') : null;

  if ( is_array($req_notes) && !empty($req_notes) ) {
    foreach ( $req_notes as $rn ) {
      echo '<div class="bt-panel bt-panel-warning"><p>' . $rn . '</p></div>';
    }
  }
?>

<form name="braintreeConfigure" action="<?php echo tep_href_link('braintree.php', 'action=configure&subaction=process&module=' . $current_module); ?>" method="post" class="bt-form">

<h3 class="bt-panel-header-info"><?php echo $current_module_title; ?></h3>
<div class="bt-panel bt-panel-info" style="padding-bottom: 15px;">

<?php
  foreach ( $OSCOM_Braintree->getInputParameters($current_module) as $cfg ) {
    echo $cfg;
  }
?>

</div>

<p>

<?php
  echo $OSCOM_Braintree->drawButton($OSCOM_Braintree->getDef('button_save'), null, 'success');
?>

</p>

</form>

<script>
$(function() {
  $('#appBraintreeToolbar a[data-module="<?php echo $current_module; ?>"]').addClass('bt-button-primary');
});
</script>
