<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2017 osCommerce

  Released under the GNU General Public License
*/

  if (!class_exists('currencies')) {
    include(DIR_FS_CATALOG . 'includes/classes/currencies.php');
  }

  $ma_data = [];

  if (tep_not_null(OSCOM_APP_PAYPAL_BRAINTREE_CURRENCIES_MA)) {
    foreach (explode(';', OSCOM_APP_PAYPAL_BRAINTREE_CURRENCIES_MA) as $ma) {
      list($a, $currency) = explode(':', $ma);

      $ma_data[$currency] = $a;
    }
  }

  $sandbox_ma_data = [];

  if (tep_not_null(OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_CURRENCIES_MA)) {
    foreach (explode(';', OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_CURRENCIES_MA) as $ma) {
      list($a, $currency) = explode(':', $ma);

      $sandbox_ma_data[$currency] = $a;
    }
  }

  $currencies = new currencies();
?>

<form name="braintreeCredentials" action="<?php echo tep_href_link('braintree.php', 'action=credentials&subaction=process'); ?>" method="post" class="bt-form">

<h3 class="bt-panel-header-info"><?php echo $OSCOM_Braintree->getDef('braintree_live_title'); ?></h3>
<div class="bt-panel bt-panel-info" style="margin-bottom: 0px;">
  <div>
    <p>
      <label for="live_merchant_id"><?php echo $OSCOM_Braintree->getDef('braintree_live_merchant_id'); ?></label>
      <?php echo tep_draw_input_field('live_merchant_id', OSCOM_APP_PAYPAL_BRAINTREE_MERCHANT_ID); ?>
    </p>
  </div>

  <div>
    <p>
      <label for="live_public_key"><?php echo $OSCOM_Braintree->getDef('braintree_live_public_key'); ?></label>
      <?php echo tep_draw_input_field('live_public_key', OSCOM_APP_PAYPAL_BRAINTREE_PUBLIC_KEY); ?>
    </p>
  </div>

  <div>
    <p>
      <label for="live_private_key"><?php echo $OSCOM_Braintree->getDef('braintree_live_private_key'); ?></label>
      <?php echo tep_draw_input_field('live_private_key', OSCOM_APP_PAYPAL_BRAINTREE_PRIVATE_KEY); ?>
    </p>
  </div>
</div>

<h3 class="bt-panel-header-info"><?php echo $OSCOM_Braintree->getDef('braintree_live_merchant_currency_accounts'); ?></h3>
<div class="bt-panel bt-panel-info">

<?php
  foreach (array_keys($currencies->currencies) as $c) {
?>

  <div>
    <p>
      <label for="live_ma<?php echo $c; ?>"><?php echo $c . ($c == DEFAULT_CURRENCY ? ' <small>(' . $OSCOM_Braintree->getDef('default') . ')</small>' : ''); ?></label>
      <?php echo tep_draw_input_field('currency_ma[' . $c . ']', (isset($ma_data[$c]) ? $ma_data[$c] : '')); ?>
    </p>
  </div>

<?php
  }

  echo tep_draw_hidden_field('live_currencies_ma', OSCOM_APP_PAYPAL_BRAINTREE_CURRENCIES_MA);
?>

</div>

<h3 class="bt-panel-header-warning"><?php echo $OSCOM_Braintree->getDef('braintree_sandbox_title'); ?></h3>
<div class="bt-panel bt-panel-warning" style="margin-bottom: 0px;">
  <div>
    <p>
      <label for="sandbox_merchant_id"><?php echo $OSCOM_Braintree->getDef('braintree_sandbox_merchant_id'); ?></label>
      <?php echo tep_draw_input_field('sandbox_merchant_id', OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_MERCHANT_ID); ?>
    </p>
  </div>

  <div>
    <p>
      <label for="sandbox_public_key"><?php echo $OSCOM_Braintree->getDef('braintree_sandbox_public_key'); ?></label>
      <?php echo tep_draw_input_field('sandbox_public_key', OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_PUBLIC_KEY); ?>
    </p>
  </div>

  <div>
    <p>
      <label for="sandbox_private_key"><?php echo $OSCOM_Braintree->getDef('braintree_sandbox_private_key'); ?></label>
      <?php echo tep_draw_input_field('sandbox_private_key', OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_PRIVATE_KEY); ?>
    </p>
  </div>
</div>

<h3 class="bt-panel-header-warning"><?php echo $OSCOM_Braintree->getDef('braintree_sandbox_merchant_currency_accounts'); ?></h3>
<div class="bt-panel bt-panel-warning">

<?php
  foreach (array_keys($currencies->currencies) as $c) {
?>

  <div>
    <p>
      <label for="sandbox_ma<?php echo $c; ?>"><?php echo $c . ($c == DEFAULT_CURRENCY ? ' <small>(' . $OSCOM_Braintree->getDef('default') . ')</small>' : ''); ?></label>
      <?php echo tep_draw_input_field('sandbox_currency_ma[' . $c . ']', (isset($sandbox_ma_data[$c]) ? $sandbox_ma_data[$c] : '')); ?>
    </p>
  </div>

<?php
  }

  echo tep_draw_hidden_field('sandbox_currencies_ma', OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_CURRENCIES_MA);
?>

</div>

<p><?php echo $OSCOM_Braintree->drawButton($OSCOM_Braintree->getDef('button_save'), null, 'success'); ?></p>

</form>

<script>
$(function() {
  $('form[name="braintreeCredentials"]').submit(function() {
    var ma_string = '';
    var ma_sandbox_string = '';

// live
    $('form[name="braintreeCredentials"] input[name^="currency_ma["]').each(function() {
      if ($(this).val().length > 0) {
        ma_string += $(this).val() + ':' + $(this).attr('name').slice(12, -1) + ';';
      }
    });

    if (ma_string.length > 0) {
      ma_string = ma_string.slice(0, -1);
    }

    $('form[name="braintreeCredentials"] input[name="live_currencies_ma"]').val(ma_string);

// sandbox
    $('form[name="braintreeCredentials"] input[name^="sandbox_currency_ma["]').each(function() {
      if ($(this).val().length > 0) {
        ma_sandbox_string += $(this).val() + ':' + $(this).attr('name').slice(20, -1) + ';';
      }
    });

    if (ma_sandbox_string.length > 0) {
      ma_sandbox_string = ma_sandbox_string.slice(0, -1);
    }

    $('form[name="braintreeCredentials"] input[name="sandbox_currencies_ma"]').val(ma_sandbox_string);
  })
});
</script>
