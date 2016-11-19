<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  chdir('../../../../');
  require('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot(array('page' => 'checkout_payment.php'));
    OSCOM::redirect('login.php');
  }

  if ( isset($_GET['payment_error']) && tep_not_null($_GET['payment_error']) ) {
    $redirect_url = OSCOM::link('checkout_payment.php', 'payment_error=' . $_GET['payment_error'] . (isset($_GET['error']) && tep_not_null($_GET['error']) ? '&error=' . $_GET['error'] : ''));
  } else {
    $hidden_params = '';

    if ($_SESSION['payment'] == 'sage_pay_direct') {
      $redirect_url = OSCOM::link('checkout_process.php', 'check=3D');
      $hidden_params = HTML::hiddenField('MD', $_POST['MD']) . HTML::hiddenField('PaRes', $_POST['PaRes']);
    } else {
      $redirect_url = OSCOM::link('checkout_success.php');
    }
  }

  $OSCOM_Language->loadDefinitions('checkout_confirmation');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo OSCOM::getDef('html_params'); ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo OSCOM::getDef('charset'); ?>">
<title><?php echo OSCOM::getDef('title', ['store_name' => STORE_NAME]); ?></title>
<base href="<?= OSCOM::getConfig('http_server', 'Shop') . OSCOM::getConfig('http_path', 'Shop'); ?>">
</head>
<body>
<form name="redirect" action="<?php echo $redirect_url; ?>" method="post" target="_top"><?php echo $hidden_params; ?>
<noscript>
  <p align="center" class="main">The transaction is being finalized. Please click continue to finalize your order.</p>
  <p align="center" class="main"><input type="submit" value="Continue" /></p>
</noscript>
</form>
<script>
document.redirect.submit();
</script>
</body>
</html>
<?php require('includes/application_bottom.php'); ?>
