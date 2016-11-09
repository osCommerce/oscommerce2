<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  require('includes/classes/currencies.php');
  $currencies = new currencies();

  $oID = HTML::sanitize($_GET['oID']);

  include('includes/classes/order.php');
  $order = new order($oID);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo OSCOM::getDef('html_params'); ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo OSCOM::getDef('charset'); ?>">
<title><?php echo OSCOM::getDef('title', ['store_name' => STORE_NAME]); ?></title>
<link rel="stylesheet" type="text/css" href="<?= OSCOM::linkPublic('Templates/Sail/css/stylesheet.css'); ?>">
</head>
<body>
<!-- body_text //-->
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>
    <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td class="pageHeading"><?php echo STORE_NAME . '<br>' . nl2br(STORE_ADDRESS) . '<br>' . STORE_PHONE; ?></td>
        <td class="pageHeading" align="right"><?php echo HTML::image(OSCOM::linkImage('Shop/' . STORE_LOGO), STORE_NAME); ?></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><strong><?php echo OSCOM::getDef('entry_sold_to'); ?></strong></td>
          </tr>
          <tr>
            <td class="main"><?php echo tep_address_format($order->customer['format_id'], $order->billing, 1, '', '<br />'); ?></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td class="main"><?php echo $order->customer['telephone']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo '<a href="mailto:' . $order->customer['email_address'] . '"><u>' . $order->customer['email_address'] . '</u></a>'; ?></td>
          </tr>
        </table></td>
        <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><strong><?php echo OSCOM::getDef('entry_ship_to'); ?></strong></td>
          </tr>
          <tr>
            <td class="main"><?php echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />'); ?></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><table border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td class="main"><strong><?php echo OSCOM::getDef('entry_payment_method'); ?></strong></td>
        <td class="main"><?php echo $order->info['payment_method']; ?></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr class="dataTableHeadingRow">
        <td class="dataTableHeadingContent" colspan="2"><?php echo OSCOM::getDef('table_heading_products'); ?></td>
        <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_products_model'); ?></td>
      </tr>
<?php
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      echo '      <tr class="dataTableRow">' . "\n" .
           '        <td class="dataTableContent" valign="top" align="right">' . $order->products[$i]['qty'] . '&nbsp;x</td>' . "\n" .
           '        <td class="dataTableContent" valign="top">' . $order->products[$i]['name'];

      if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
        for ($j=0, $k=sizeof($order->products[$i]['attributes']); $j<$k; $j++) {
          echo '<br /><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
          echo '</i></small></nobr>';
        }
      }

      echo '        </td>' . "\n" .
           '        <td class="dataTableContent" valign="top">' . $order->products[$i]['model'] . '</td>' . "\n" .
           '      </tr>' . "\n";
    }
?>
    </table></td>
  </tr>
</table>
<!-- body_text_eof //-->
<br />
</body>
</html>
<?php require('includes/application_bottom.php'); ?>
