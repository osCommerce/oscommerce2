<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\DateTime;
  use OSC\OM\OSCOM;

  if (!strstr($PHP_SELF, 'account_history_info.php')) {
// Get last order id for checkout_success
    $Qorders = $OSCOM_Db->get('orders', 'orders_id', ['customers_id' => $_SESSION['customer_id']], 'orders_id desc', 1);
    $last_order = $Qorders->valueInt('orders_id');
  } else {
    $last_order = (int)$_GET['order_id'];
  }

// Now get all downloadable products in that order
  $Qdownloads = $OSCOM_Db->prepare('select date_format(o.date_purchased, "%Y-%m-%d") as date_purchased_day, opd.download_maxdays, op.products_name, opd.orders_products_download_id, opd.orders_products_filename, opd.download_count, opd.download_maxdays from :table_orders o, :table_orders_products op, :table_orders_products_download opd, :table_orders_status os where o.orders_id = :orders_id and o.customers_id = :customers_id and o.orders_id = op.orders_id and op.orders_products_id = opd.orders_products_id and opd.orders_products_filename != "" and o.orders_status = os.orders_status_id and os.downloads_flag = 1 and os.language_id = :language_id');
  $Qdownloads->bindInt(':orders_id', $last_order);
  $Qdownloads->bindInt(':customers_id', $_SESSION['customer_id']);
  $Qdownloads->bindInt(':language_id', $OSCOM_Language->getId());
  $Qdownloads->execute();

  if ($Qdownloads->fetch() !== false) {
?>

  <h2><?php echo HEADING_DOWNLOAD; ?></h2>

  <div class="contentText">
    <table border="0" width="100%" cellspacing="1" cellpadding="2">

<?php
    do {
// MySQL 3.22 does not have INTERVAL
      list($dt_year, $dt_month, $dt_day) = explode('-', $Qdownloads->value('date_purchased_day'));
      $download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + $Qdownloads->valueInt('download_maxdays'), $dt_year);
      $download_expiry = date('Y-m-d H:i:s', $download_timestamp);

      echo '      <tr>' . "\n";

// The link will appear only if:
// - Download remaining count is > 0, AND
// - The file is present in the DOWNLOAD directory, AND EITHER
// - No expiry date is enforced (maxdays == 0), OR
// - The expiry date is not reached
      if ( ($Qdownloads->valueInt('download_count') > 0) && (is_file(OSCOM::getConfig('dir_root', 'Shop') . 'download/' . $Qdownloads->value('orders_products_filename'))) && ( ($Qdownloads->valueInt('download_maxdays') == 0) || ($download_timestamp > time())) ) {
        echo '        <td><a href="' . OSCOM::link('download.php', 'order=' . $last_order . '&id=' . $Qdownloads->valueInt('orders_products_download_id')) . '">' . $Qdownloads->value('products_name') . '</a></td>' . "\n";
      } else {
        echo '        <td>' . $Qdownloads->value('products_name') . '</td>' . "\n";
      }

      echo '        <td>' . TABLE_HEADING_DOWNLOAD_DATE . DateTime::toLong($download_expiry) . '</td>' . "\n" .
           '        <td align="right">' . $Qdownloads->valueInt('download_count') . TABLE_HEADING_DOWNLOAD_COUNT . '</td>' . "\n" .
           '      </tr>' . "\n";
    } while ($Qdownloads->fetch());
?>

    </table>

<?php
    if (!strstr($PHP_SELF, 'account_history_info.php')) {
?>

    <p><?php printf(FOOTER_DOWNLOAD, '<a href="' . OSCOM::link('account.php') . '">' . OSCOM::getDef('header_title_my_account') . '</a>'); ?></p>

<?php
    }
?>

  </div>

<?php
  }
?>
