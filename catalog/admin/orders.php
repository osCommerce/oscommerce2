<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  require('includes/application_top.php');

  $OSCOM_Hooks = Registry::get('Hooks');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $orders_statuses = array();
  $orders_status_array = array();
  $Qstatus = $OSCOM_Db->get('orders_status', [
    'orders_status_id',
    'orders_status_name'
  ], [
    'language_id' => (int)$_SESSION['languages_id']
  ]);

  while ($Qstatus->fetch()) {
    $orders_statuses[] = [
      'id' => $Qstatus->valueInt('orders_status_id'),
      'text' => $Qstatus->value('orders_status_name')
    ];

    $orders_status_array[$Qstatus->valueInt('orders_status_id')] = $Qstatus->value('orders_status_name');
  }

  if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'update_order':
        $oID = HTML::sanitize($_GET['oID']);
        $status = HTML::sanitize($_POST['status']);
        $comments = HTML::sanitize($_POST['comments']);

        $order_updated = false;

        $Qcheck = $OSCOM_Db->get('orders', [
          'customers_name',
          'customers_email_address',
          'orders_status',
          'date_purchased'
        ], [
          'orders_id' => (int)$oID
        ]);

        if ( ($Qcheck->value('orders_status') != $status) || tep_not_null($comments)) {
          $OSCOM_Db->save('orders', [
            'orders_status' => $status,
            'last_modified' => 'now()'
          ], [
            'orders_id' => (int)$oID
          ]);

          $customer_notified = '0';
          if (isset($_POST['notify']) && ($_POST['notify'] == 'on')) {
            $notify_comments = '';
            if (isset($_POST['notify_comments']) && ($_POST['notify_comments'] == 'on')) {
              $notify_comments = sprintf(EMAIL_TEXT_COMMENTS_UPDATE, $comments) . "\n\n";
            }

            $email = STORE_NAME . "\n" . EMAIL_SEPARATOR . "\n" . EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID . "\n" . EMAIL_TEXT_INVOICE_URL . ' ' . OSCOM::link('Shop/' . FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $oID, 'SSL') . "\n" . EMAIL_TEXT_DATE_ORDERED . ' ' . tep_date_long($Qcheck->value('date_purchased')) . "\n\n" . $notify_comments . sprintf(EMAIL_TEXT_STATUS_UPDATE, $orders_status_array[$status]);

            tep_mail($Qcheck->value('customers_name'), $Qcheck->value('customers_email_address'), EMAIL_TEXT_SUBJECT, $email, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

            $customer_notified = '1';
          }

          $OSCOM_Db->save('orders_status_history', [
            'orders_id' => (int)$oID,
            'orders_status_id' => $status,
            'date_added' => 'now()',
            'customer_notified' => $customer_notified,
            'comments' => $comments
          ]);

          $order_updated = true;
        }

        if ($order_updated == true) {
         $OSCOM_MessageStack->add(SUCCESS_ORDER_UPDATED, 'success');
        } else {
          $OSCOM_MessageStack->add(WARNING_ORDER_NOT_UPDATED, 'warning');
        }

        OSCOM::redirect(FILENAME_ORDERS, tep_get_all_get_params(array('action')) . 'action=edit');
        break;
      case 'deleteconfirm':
        $oID = HTML::sanitize($_GET['oID']);

        tep_remove_order($oID, $_POST['restock']);

        OSCOM::redirect(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')));
        break;
    }
  }

  if (($action == 'edit') && isset($_GET['oID'])) {
    $oID = HTML::sanitize($_GET['oID']);

    $Qorders = $OSCOM_Db->get('orders', 'orders_id', ['orders_id' => (int)$oID]);
    $order_exists = true;
    if ($Qorders->fetch() === false) {
      $order_exists = false;
      $OSCOM_MessageStack->add(sprintf(ERROR_ORDER_DOES_NOT_EXIST, $oID), 'error');
    }
  }

  include(DIR_WS_CLASSES . 'order.php');

  $OSCOM_Hooks->call('Orders', 'Action');

  require(DIR_WS_INCLUDES . 'template_top.php');

  $base_url = ($request_type == 'SSL') ? HTTPS_SERVER . DIR_WS_HTTPS_ADMIN : HTTP_SERVER . DIR_WS_ADMIN;

  if (($action == 'edit') && ($order_exists == true)) {
    $order = new order($oID);
?>

<h1 class="pageHeading"><?php echo HEADING_TITLE . ': #' . (int)$oID . ' (' . $order->info['total'] . ')'; ?></h1>

<div style="text-align: right; padding-bottom: 15px;"><?php echo HTML::button(IMAGE_ORDERS_INVOICE, 'fa fa-file-o', OSCOM::link(FILENAME_ORDERS_INVOICE, 'oID=' . $_GET['oID']), null, array('newwindow' => true)) . HTML::button(IMAGE_ORDERS_PACKINGSLIP, 'fa fa-file', OSCOM::link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $_GET['oID']), null, array('newwindow' => true)) . HTML::button(IMAGE_BACK, 'fa fa-chevron-left', OSCOM::link(FILENAME_ORDERS, tep_get_all_get_params(array('action')))); ?></div>

<div id="orderTabs" style="overflow: auto;">
  <ul>
    <li><?php echo '<a href="' . substr(OSCOM::link(FILENAME_ORDERS, tep_get_all_get_params()), strlen($base_url)) . '#section_summary_content">Summary</a>'; ?></li>
    <li><?php echo '<a href="' . substr(OSCOM::link(FILENAME_ORDERS, tep_get_all_get_params()), strlen($base_url)) . '#section_products_content">Products</a>'; ?></li>
    <li><?php echo '<a href="' . substr(OSCOM::link(FILENAME_ORDERS, tep_get_all_get_params()), strlen($base_url)) . '#section_status_history_content">Status History</a>'; ?></li>
  </ul>

  <div id="section_summary_content" style="padding: 10px;">
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="33%" valign="top">
          <fieldset style="border: 0; height: 100%;">
            <legend style="margin-left: -20px; font-weight: bold;"><?php echo ENTRY_CUSTOMER; ?></legend>

            <p><?php echo tep_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />'); ?></p>
            <p><?php echo $order->customer['telephone'] . '<br />' . '<a href="mailto:' . $order->customer['email_address'] . '"><u>' . $order->customer['email_address'] . '</u></a>'; ?></p>
          </fieldset>
        </td>
        <td width="33%" valign="top">
          <fieldset style="border: 0; height: 100%;">
            <legend style="margin-left: -20px; font-weight: bold;"><?php echo ENTRY_SHIPPING_ADDRESS; ?></legend>

            <p><?php echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />'); ?></p>
          </fieldset>
        </td>
        <td width="33%" valign="top">
          <fieldset style="border: 0; height: 100%;">
            <legend style="margin-left: -20px; font-weight: bold;"><?php echo ENTRY_BILLING_ADDRESS; ?></legend>

            <p><?php echo tep_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />'); ?></p>
          </fieldset>
        </td>
      </tr>
      <tr>
        <td width="33%" valign="top">
          <fieldset style="border: 0; height: 100%;">
            <legend style="margin-left: -20px; font-weight: bold;"><?php echo ENTRY_PAYMENT_METHOD; ?></legend>

            <p><?php echo $order->info['payment_method']; ?></p>

<?php
    if (tep_not_null($order->info['cc_type']) || tep_not_null($order->info['cc_owner']) || tep_not_null($order->info['cc_number'])) {
?>

            <table border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td><?php echo ENTRY_CREDIT_CARD_TYPE; ?></td>
                <td><?php echo $order->info['cc_type']; ?></td>
              </tr>
              <tr>
                <td><?php echo ENTRY_CREDIT_CARD_OWNER; ?></td>
                <td><?php echo $order->info['cc_owner']; ?></td>
              </tr>
              <tr>
                <td><?php echo ENTRY_CREDIT_CARD_NUMBER; ?></td>
                <td><?php echo $order->info['cc_number']; ?></td>
              </tr>
              <tr>
                <td><?php echo ENTRY_CREDIT_CARD_EXPIRES; ?></td>
                <td><?php echo $order->info['cc_expires']; ?></td>
              </tr>
            </table>

<?php
    }
?>
          </fieldset>
        </td>
        <td width="33%" valign="top">
          <fieldset style="border: 0; height: 100%;">
            <legend style="margin-left: -20px; font-weight: bold;"><?php echo ENTRY_STATUS; ?></legend>

            <p><?php echo $order->info['status'] . '<br />' . (empty($order->info['last_modified']) ? tep_datetime_short($order->info['date_purchased']) : tep_datetime_short($order->info['last_modified'])); ?></p>
          </fieldset>
        </td>
        <td width="33%" valign="top">
          <fieldset style="border: 0; height: 100%;">
            <legend style="margin-left: -20px; font-weight: bold;"><?php echo ENTRY_TOTAL; ?></legend>

            <p><?php echo $order->info['total']; ?></p>
          </fieldset>
        </td>
      </tr>
    </table>
  </div>

  <div id="section_products_content" style="padding: 10px;">
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr class="dataTableHeadingRow">
        <td class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
        <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
        <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TAX; ?></td>
        <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></td>
        <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></td>
        <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?></td>
        <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></td>
      </tr>
<?php
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      echo '      <tr class="dataTableRow">' . "\n" .
           '        <td class="dataTableContent" valign="top" align="right">' . $order->products[$i]['qty'] . '&nbsp;x</td>' . "\n" .
           '        <td class="dataTableContent" valign="top">' . $order->products[$i]['name'];

      if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
        for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
          echo '<br /><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
          if ($order->products[$i]['attributes'][$j]['price'] != '0') echo ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')';
          echo '</i></small></nobr>';
        }
      }

      echo '        </td>' . "\n" .
           '        <td class="dataTableContent" valign="top">' . $order->products[$i]['model'] . '</td>' . "\n" .
           '        <td class="dataTableContent" align="right" valign="top">' . tep_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n" .
           '        <td class="dataTableContent" align="right" valign="top"><strong>' . $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" .
           '        <td class="dataTableContent" align="right" valign="top"><strong>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax'], true), true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" .
           '        <td class="dataTableContent" align="right" valign="top"><strong>' . $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" .
           '        <td class="dataTableContent" align="right" valign="top"><strong>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax'], true) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n";
      echo '      </tr>' . "\n";
    }
?>
      <tr>
        <td align="right" colspan="8"><table border="0" cellspacing="0" cellpadding="2">
<?php
    foreach ( $order->totals as $ot ) {
      echo '          <tr>' . "\n" .
           '            <td align="right" class="smallText">' . $ot['title'] . '</td>' . "\n" .
           '            <td align="right" class="smallText">' . $ot['text'] . '</td>' . "\n" .
           '          </tr>' . "\n";
    }
?>
        </table></td>
      </tr>
    </table>
  </div>

  <div id="section_status_history_content">
    <?php echo HTML::form('status', OSCOM::link(FILENAME_ORDERS, tep_get_all_get_params(array('action')) . 'action=update_order')); ?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><?php echo ENTRY_STATUS; ?></td>
        <td><?php echo HTML::selectField('status', $orders_statuses, $order->info['orders_status']); ?></td>
      </tr>
      <tr>
        <td valign="top"><?php echo ENTRY_ADD_COMMENT; ?></td>
        <td><?php echo HTML::textareaField('comments', '60', '6', null, 'style="width: 100%;"'); ?></td>
      </tr>
      <tr>
        <td><?php echo ENTRY_NOTIFY_CUSTOMER; ?></td>
        <td><?php echo HTML::checkboxField('notify', '', true); ?></td>
      </tr>
        <td><?php echo ENTRY_NOTIFY_COMMENTS; ?></td>
        <td><?php echo HTML::checkboxField('notify_comments', '', true); ?></td>
      </tr>
      <tr>
        <td colspan="2" align="right"><?php echo HTML::button(IMAGE_UPDATE, 'fa fa-save', null, 'primary'); ?></td>
      </tr>
    </table>

    </form>

    <br />

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr class="dataTableHeadingRow">
        <td class="dataTableHeadingContent" align="center"><strong><?php echo TABLE_HEADING_DATE_ADDED; ?></strong></td>
        <td class="dataTableHeadingContent" align="center"><strong><?php echo TABLE_HEADING_STATUS; ?></strong></td>
        <td class="dataTableHeadingContent" align="center"><strong><?php echo TABLE_HEADING_COMMENTS; ?></strong></td>
        <td class="dataTableHeadingContent" align="right"><strong><?php echo TABLE_HEADING_CUSTOMER_NOTIFIED; ?></strong></td>
      </tr>

<?php
    $Qhistory = $OSCOM_Db->get('orders_status_history', [
      'orders_status_id',
      'date_added',
      'customer_notified',
      'comments'
    ], [
      'orders_id' => $oID
    ], 'date_added desc');

    if ($Qhistory->fetch() !== false) {
      do {
        echo '      <tr class="dataTableRow">' . "\n" .
             '        <td class="dataTableContent" valign="top">' . tep_datetime_short($Qhistory->value('date_added')) . '</td>' . "\n" .
             '        <td class="dataTableContent" valign="top">' . $orders_status_array[$Qhistory->valueInt('orders_status_id')] . '</td>' . "\n" .
             '        <td class="dataTableContent" valign="top">' . nl2br(HTML::output($Qhistory->value('comments'))) . '&nbsp;</td>' . "\n" .
             '        <td class="dataTableContent" valign="top" align="right">';

        if ($Qhistory->valueInt('customer_notified') === 1) {
          echo HTML::image(DIR_WS_ICONS . 'tick.gif', ICON_TICK);
        } else {
          echo HTML::image(DIR_WS_ICONS . 'cross.gif', ICON_CROSS);
        }

        echo '        </td>' . "\n" .
             '      </tr>' . "\n";
      } while ($Qhistory->fetch());
    } else {
        echo '      <tr class="dataTableRow">' . "\n" .
             '        <td class="dataTableContent" colspan="5">' . TEXT_NO_ORDER_HISTORY . '</td>' . "\n" .
             '      </tr>' . "\n";
    }
?>

    </table>
  </div>

<?php
    echo $OSCOM_Hooks->output('Orders', 'PageTab', 'display');
?>

</div>

<script>
$(function() {
  $('#orderTabs').tabs();
});
</script>

<?php
  } else {
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td align="right"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr><?php echo HTML::form('orders', OSCOM::link(FILENAME_ORDERS), 'get', null, ['session_id' => true]); ?>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_SEARCH . ' ' . HTML::inputField('oID', '', 'size="12"') . HTML::hiddenField('action', 'edit'); ?></td>
              </form></tr>
              <tr><?php echo HTML::form('status', OSCOM::link(FILENAME_ORDERS), 'get', null, ['session_id' => true]); ?>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_STATUS . ' ' . HTML::selectField('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)), $orders_statuses), '', 'onchange="this.form.submit();"'); ?></td>
              </form></tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    if (isset($_GET['cID'])) {
      $cID = HTML::sanitize($_GET['cID']);
      $Qorders = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS o.orders_id, o.customers_name, o.customers_id, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from :table_orders o left join :table_orders_total ot on (o.orders_id = ot.orders_id), :table_orders_status s where o.customers_id = :customers_id and o.orders_status = s.orders_status_id and s.language_id = :language_id and ot.class = "ot_total" order by orders_id desc limit :page_set_offset, :page_set_max_results');
      $Qorders->bindInt(':customers_id', $_GET['cID']);
    } elseif (isset($_GET['status']) && is_numeric($_GET['status']) && ($_GET['status'] > 0)) {
      $status = HTML::sanitize($_GET['status']);
      $Qorders = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from :table_orders o left join :table_orders_total ot on (o.orders_id = ot.orders_id), :table_orders_status s where o.orders_status = s.orders_status_id and s.language_id = :language_id and s.orders_status_id = :orders_status_id and ot.class = "ot_total" order by o.orders_id desc limit :page_set_offset, :page_set_max_results');
      $Qorders->bindInt(':orders_status_id', $status);
    } else {
      $Qorders = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from :table_orders o left join :table_orders_total ot on (o.orders_id = ot.orders_id), :table_orders_status s where o.orders_status = s.orders_status_id and s.language_id = :language_id and ot.class = "ot_total" order by o.orders_id desc limit :page_set_offset, :page_set_max_results');
    }
    $Qorders->bindInt(':language_id', $_SESSION['languages_id']);
    $Qorders->setPageSet(MAX_DISPLAY_SEARCH_RESULTS, tep_get_all_get_params(array('page', 'oID', 'action')));
    $Qorders->execute();

    while ($Qorders->fetch()) {
    if ((!isset($_GET['oID']) || (isset($_GET['oID']) && ((int)$_GET['oID'] === $Qorders->valueInt('orders_id')))) && !isset($oInfo)) {
        $oInfo = new objectInfo($Qorders->toArray());
      }

      if (isset($oInfo) && is_object($oInfo) && ($Qorders->valueInt('orders_id') === (int)$oInfo->orders_id)) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_ORDERS, tep_get_all_get_params(array('oID')) . 'oID=' . $Qorders->valueInt('orders_id')) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . OSCOM::link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $Qorders->valueInt('orders_id') . '&action=edit') . '">' . HTML::image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;' . $Qorders->value('customers_name'); ?></td>
                <td class="dataTableContent" align="right"><?php echo strip_tags($Qorders->value('order_total')); ?></td>
                <td class="dataTableContent" align="center"><?php echo tep_datetime_short($Qorders->value('date_purchased')); ?></td>
                <td class="dataTableContent" align="right"><?php echo $Qorders->value('orders_status_name'); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($oInfo) && is_object($oInfo) && ($Qorders->valueInt('orders_id') === (int)$oInfo->orders_id)) { echo HTML::image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . OSCOM::link(FILENAME_ORDERS, tep_get_all_get_params(array('oID')) . 'oID=' . $Qorders->valueInt('orders_id')) . '">' . HTML::image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qorders->getPageSetLabel(TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></td>
                    <td class="smallText" align="right"><?php echo $Qorders->getPageSetLinks(); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_ORDER . '</strong>');

      $contents = array('form' => HTML::form('orders', OSCOM::link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=deleteconfirm')));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO . '<br /><br /><strong>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</strong>');
      $contents[] = array('text' => '<br />' . HTML::checkboxField('restock') . ' ' . TEXT_INFO_RESTOCK_PRODUCT_QUANTITY);
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_DELETE, 'fa fa-trash', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id)));
      break;
    default:
      if (isset($oInfo) && is_object($oInfo)) {
        $heading[] = array('text' => '<strong>[' . $oInfo->orders_id . ']&nbsp;&nbsp;' . tep_datetime_short($oInfo->date_purchased) . '</strong>');

        $contents[] = array('align' => 'center', 'text' => HTML::button(IMAGE_EDIT, 'fa fa-edit', OSCOM::link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit')) . HTML::button(IMAGE_DELETE, 'fa fa-trash', OSCOM::link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=delete')));
        $contents[] = array('align' => 'center', 'text' => HTML::button(IMAGE_ORDERS_INVOICE, 'fa fa-file-o', OSCOM::link(FILENAME_ORDERS_INVOICE, 'oID=' . $oInfo->orders_id), null, array('newwindow' => true)) . HTML::button(IMAGE_ORDERS_PACKINGSLIP, 'fa fa-file', OSCOM::link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $oInfo->orders_id), null, array('newwindow' => true)));
        $contents[] = array('text' => '<br />' . TEXT_DATE_ORDER_CREATED . ' ' . tep_date_short($oInfo->date_purchased));
        if (tep_not_null($oInfo->last_modified)) $contents[] = array('text' => TEXT_DATE_ORDER_LAST_MODIFIED . ' ' . tep_date_short($oInfo->last_modified));
        $contents[] = array('text' => '<br />' . TEXT_INFO_PAYMENT_METHOD . ' '  . $oInfo->payment_method);
      }
      break;
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table>
<?php
  }

  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
