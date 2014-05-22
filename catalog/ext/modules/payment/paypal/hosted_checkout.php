<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

  $error = false;

  if (!defined('MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS') || (MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS  != 'True')) {
    $error = true;
  }

  if ( $error === false ) {
    if ( !isset($HTTP_GET_VARS['key']) || !tep_session_is_registered('pphs_key') || ($HTTP_GET_VARS['key'] != $pphs_key) || !tep_session_is_registered('pphs_result') ) {
      $error = true;
    }
  }

  if ( $error === false ) {
    if (($pphs_result['ACK'] != 'Success') && ($pphs_result['ACK'] != 'SuccessWithWarning')) {
      $error = true;

      tep_session_register('pphs_error_msg');
      $pphs_error_msg = $pphs_result['L_LONGMESSAGE0'];
    }
  }

  if ( $error === false ) {
    if ( MODULE_PAYMENT_PAYPAL_PRO_HS_GATEWAY_SERVER == 'Live' ) {
      $form_url = 'https://securepayments.paypal.com/webapps/HostedSoleSolutionApp/webflow/sparta/hostedSoleSolutionProcess';
    } else {
      $form_url = 'https://securepayments.sandbox.paypal.com/webapps/HostedSoleSolutionApp/webflow/sparta/hostedSoleSolutionProcess';
    }
  } else {
    $form_url = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=paypal_pro_hs', 'SSL');
  }
?>
<!DOCTYPE html>
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<title><?php echo tep_output_string_protected($oscTemplate->getTitle()); ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>" />
</head>
<body>

<div style="text-align: center;">
  <?php echo tep_image('ext/modules/payment/paypal/images/hss_load.gif');?>
</div>

<form name="pphs" action="<?php echo $form_url; ?>" method="post" <?php echo ($error == true ? 'target="_top"' : ''); ?>>
  <input type="hidden" name="hosted_button_id" value="<?php echo (isset($pphs_result['HOSTEDBUTTONID']) ? tep_output_string_protected($pphs_result['HOSTEDBUTTONID']) : ''); ?>" />
</form>

<script>
  document.pphs.submit();
</script>

</body>
</html>

<?php
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
