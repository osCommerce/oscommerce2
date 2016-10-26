<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  chdir('../../../../');
  require('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot(array('mode' => 'SSL', 'page' => 'checkout_payment.php'));
    OSCOM::redirect('login.php', '', 'SSL');
  }

  if (!isset($_SESSION['sage_pay_direct_acsurl'])) {
    OSCOM::redirect('checkout_payment.php', '', 'SSL');
  }

  if (!isset($_SESSION['payment']) || ($_SESSION['payment'] != 'sage_pay_direct')) {
    OSCOM::redirect('checkout_payment.php', '', 'SSL');
  }

  require('includes/languages/' . $_SESSION['language'] . '/checkout_confirmation.php');
  require('includes/languages/' . $_SESSION['language'] . '/modules/payment/sage_pay_direct.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo MODULE_PAYMENT_SAGE_PAY_DIRECT_3DAUTH_TITLE; ?></title>
<base href="<?= OSCOM::getConfig('http_server', 'Shop') . OSCOM::getConfig('http_path', 'Shop'); ?>">
</head>
<body>
<FORM name="form" action="<?php echo $_SESSION['sage_pay_direct_acsurl']; ?>" method="POST">
<input type="hidden" name="PaReq" value="<?php echo $_SESSION['sage_pay_direct_pareq']; ?>" />
<input type="hidden" name="TermUrl" value="<?php echo OSCOM::link('ext/modules/payment/sage_pay/redirect.php', '', 'SSL'); ?>" />
<input type="hidden" name="MD" value="<?php echo $_SESSION['sage_pay_direct_md']; ?>" />
<NOSCRIPT>
<?php echo '<center><p>' . MODULE_PAYMENT_SAGE_PAY_DIRECT_3DAUTH_INFO . '</p><p><input type="submit" value="' . MODULE_PAYMENT_SAGE_PAY_DIRECT_3DAUTH_BUTTON . '"/></p></center>'; ?>
</NOSCRIPT>
<script><!--
document.form.submit();
//--></script>
</body>
</html>
<?php require('includes/application_bottom.php'); ?>
