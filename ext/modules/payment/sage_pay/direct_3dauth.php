<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2009 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
  if (!$OSCOM_Customer->isLoggedOn()) {
    $OSCOM_NavigationHistory->setSnapshot(array('application' => 'checkout', 'action' => 'payment', 'mode' => 'SSL'));

    osc_redirect(osc_href_link('account', 'login', 'SSL'));
  }

  if (!isset($_SESSION['sage_pay_direct_acsurl'])) {
    osc_redirect(osc_href_link('checkout', 'payment', 'SSL'));
  }

  if (!isset($_SESSION['payment']) || ($_SESSION['payment'] != 'sage_pay_direct')) {
    osc_redirect(osc_href_link('checkout', 'payment', 'SSL'));
  }

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/checkout.php');
  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/sage_pay_direct.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo MODULE_PAYMENT_SAGE_PAY_DIRECT_3DAUTH_TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>
<FORM name="form" action="<?php echo $_SESSION['sage_pay_direct_acsurl']; ?>" method="POST">
<input type="hidden" name="PaReq" value="<?php echo $_SESSION['sage_pay_direct_pareq']; ?>" />
<input type="hidden" name="TermUrl" value="<?php echo osc_href_link('ext/modules/payment/sage_pay/redirect.php', '', 'SSL'); ?>" />
<input type="hidden" name="MD" value="<?php echo $_SESSION['sage_pay_direct_md']; ?>" />
<NOSCRIPT>
<?php echo '<center><p>' . MODULE_PAYMENT_SAGE_PAY_DIRECT_3DAUTH_INFO . '</p><p><input type="submit" value="' . MODULE_PAYMENT_SAGE_PAY_DIRECT_3DAUTH_BUTTON . '"/></p></center>'; ?>
</NOSCRIPT>
<script type="text/javascript"><!--
document.form.submit();
//--></script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
