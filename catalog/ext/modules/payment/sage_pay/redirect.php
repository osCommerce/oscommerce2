<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot(array('mode' => 'SSL', 'get' => 'checkout&payment'));
    osc_redirect(osc_href_link('account', 'login', 'SSL'));
  }

  if ( isset($_GET['payment_error']) && osc_not_null($_GET['payment_error']) ) {
    $redirect_url = osc_href_link('checkout', 'payment&payment_error=' . $_GET['payment_error'] . (isset($_GET['error']) && osc_not_null($_GET['error']) ? '&error=' . $_GET['error'] : ''), 'SSL');
  } else {
    $hidden_params = '';

    if ($_SESSION['payment'] == 'sage_pay_direct') {
      $redirect_url = osc_href_link('checkout', 'process&check=3D', 'SSL');
      $hidden_params = osc_draw_hidden_field('MD', $_POST['MD']) . osc_draw_hidden_field('PaRes', $_POST['PaRes']);
    } else {
      $redirect_url = osc_href_link('checkout', 'success', 'SSL');
    }
  }

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/checkout.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>
<form name="redirect" action="<?php echo $redirect_url; ?>" method="post" target="_top"><?php echo $hidden_params; ?>
<noscript>
  <p align="center" class="main">The transaction is being finalized. Please click continue to finalize your order.</p>
  <p align="center" class="main"><input type="submit" value="Continue" /></p>
</noscript>
</form>
<script type="text/javascript">
document.redirect.submit();
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
