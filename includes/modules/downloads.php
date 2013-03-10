<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  if ( $OSCOM_APP->getCode() != 'account' ) {
// Get last order id for checkout_success
    $orders_query = osc_db_query("select orders_id from " . TABLE_ORDERS . " where customers_id = '" . (int)$OSCOM_Customer->getID() . "' order by orders_id desc limit 1");
    $orders = osc_db_fetch_array($orders_query);
    $last_order = $orders['orders_id'];
  } else {
    $last_order = $_GET['id'];
  }

// Now get all downloadable products in that order
  $downloads_query = osc_db_query("select date_format(o.date_purchased, '%Y-%m-%d') as date_purchased_day, opd.download_maxdays, op.products_name, opd.orders_products_download_id, opd.orders_products_filename, opd.download_count, opd.download_maxdays from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd, " . TABLE_ORDERS_STATUS . " os where o.customers_id = '" . (int)$OSCOM_Customer->getID() . "' and o.orders_id = '" . (int)$last_order . "' and o.orders_id = op.orders_id and op.orders_products_id = opd.orders_products_id and opd.orders_products_filename != '' and o.orders_status = os.orders_status_id and os.downloads_flag = '1' and os.language_id = '" . (int)$_SESSION['languages_id'] . "'");
  if (osc_db_num_rows($downloads_query) > 0) {
?>

  <h2><?php echo HEADING_DOWNLOAD; ?></h2>

  <div class="contentText">
    <table border="0" width="100%" cellspacing="1" cellpadding="2">

<?php
    while ($downloads = osc_db_fetch_array($downloads_query)) {
// MySQL 3.22 does not have INTERVAL
      list($dt_year, $dt_month, $dt_day) = explode('-', $downloads['date_purchased_day']);
      $download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + $downloads['download_maxdays'], $dt_year);
      $download_expiry = date('Y-m-d H:i:s', $download_timestamp);

      echo '      <tr>' . "\n";

// The link will appear only if:
// - Download remaining count is > 0, AND
// - The file is present in the DOWNLOAD directory, AND EITHER
// - No expiry date is enforced (maxdays == 0), OR
// - The expiry date is not reached
      if ( ($downloads['download_count'] > 0) && (file_exists(DIR_FS_DOWNLOAD . $downloads['orders_products_filename'])) && ( ($downloads['download_maxdays'] == 0) || ($download_timestamp > time())) ) {
        echo '        <td><a href="' . osc_href_link('products', 'download&order=' . $last_order . '&id=' . $downloads['orders_products_download_id']) . '">' . $downloads['products_name'] . '</a></td>' . "\n";
      } else {
        echo '        <td>' . $downloads['products_name'] . '</td>' . "\n";
      }

      echo '        <td>' . TABLE_HEADING_DOWNLOAD_DATE . osc_date_long($download_expiry) . '</td>' . "\n" .
           '        <td align="right">' . $downloads['download_count'] . TABLE_HEADING_DOWNLOAD_COUNT . '</td>' . "\n" .
           '      </tr>' . "\n";
    }
?>

    </table>

<?php
    if ( $OSCOM_APP->getCode() != 'account' ) {
?>

    <p><?php printf(FOOTER_DOWNLOAD, '<a href="' . osc_href_link('account', '', 'SSL') . '">' . HEADER_TITLE_MY_ACCOUNT . '</a>'); ?></p>

<?php
    }
?>

  </div>

<?php
  }
?>
