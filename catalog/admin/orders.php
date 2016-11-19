<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\DateTime;
  use OSC\OM\HTML;
  use OSC\OM\Mail;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  require('includes/application_top.php');

  $OSCOM_Hooks = Registry::get('Hooks');

  require('includes/classes/currencies.php');
  $currencies = new currencies();

  $orders_statuses = [];
  $orders_status_array = [];

  $Qstatus = $OSCOM_Db->get('orders_status', [
    'orders_status_id',
    'orders_status_name'
  ], [
    'language_id' => $OSCOM_Language->getId()
  ]);

  while ($Qstatus->fetch()) {
    $orders_statuses[] = [
      'id' => $Qstatus->valueInt('orders_status_id'),
      'text' => $Qstatus->value('orders_status_name')
    ];

    $orders_status_array[$Qstatus->valueInt('orders_status_id')] = $Qstatus->value('orders_status_name');
  }

  include('includes/classes/order.php');

  if (isset($_GET['oID']) && is_numeric($_GET['oID']) && ($_GET['oID'] > 0)) {
    $oID = HTML::sanitize($_GET['oID']);

    $Qorders = $OSCOM_Db->get('orders', 'orders_id', ['orders_id' => (int)$oID]);

    if ($Qorders->fetch()) {
      $order = new order($Qorders->valueInt('orders_id'));
    } else {
      $OSCOM_MessageStack->add(OSCOM::getDef('error_order_does_not_exist', ['order_id' => $oID]), 'error');
    }
  }

  if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  $OSCOM_Hooks->call('Orders', 'PreAction');

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
              $notify_comments = OSCOM::getDef('email_text_comments_update', ['comments' => $comments]) . "\n\n";
            }

            $email = STORE_NAME . "\n" . OSCOM::getDef('email_separator') . "\n" . OSCOM::getDef('email_text_order_number') . ' ' . $oID . "\n" . OSCOM::getDef('email_text_invoice_url') . ' ' . OSCOM::link('Shop/' . FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $oID) . "\n" . OSCOM::getDef('email_text_date_ordered') . ' ' . DateTime::toLong($Qcheck->value('date_purchased')) . "\n\n" . $notify_comments . OSCOM::getDef('email_text_status_update', ['status' => $orders_status_array[$status]]) . "\n";

            $orderEmail = new Mail($Qcheck->value('customers_email_address'), $Qcheck->value('customers_name'), STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, OSCOM::getDef('email_text_subject'));
            $orderEmail->setBody($email);
            $orderEmail->send();

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
         $OSCOM_MessageStack->add(OSCOM::getDef('success_order_updated'), 'success');
        } else {
          $OSCOM_MessageStack->add(OSCOM::getDef('warning_order_not_updated'), 'warning');
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

  $OSCOM_Hooks->call('Orders', 'Action');

  $show_listing = true;

  require($oscTemplate->getFile('template_top.php'));
?>

<h2><i class="fa fa-shopping-cart"></i> <a href="<?= OSCOM::link('orders.php'); ?>"><?= OSCOM::getDef('heading_title'); ?></a></h2>

<?php
  if (!empty($action)) {
    if (($action == 'edit') && isset($order)) {
      $show_listing = false;
?>

<h3><?= '#' . $order->info['id'] . ' (' . strip_tags($order->info['total']) . ')'; ?></h3>

<div style="text-align: right; padding-bottom: 15px;"><?= HTML::button(OSCOM::getDef('image_back'), 'fa fa-chevron-left', OSCOM::link(FILENAME_ORDERS, tep_get_all_get_params(array('action'))), null, 'btn-info') . HTML::button(OSCOM::getDef('image_orders_invoice'), 'fa fa-file-text-o', OSCOM::link(FILENAME_ORDERS_INVOICE, 'oID=' . $_GET['oID']), array('newwindow' => true), 'btn-primary') . HTML::button(OSCOM::getDef('image_orders_packingslip'), 'fa fa-clipboard', OSCOM::link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $_GET['oID']), array('newwindow' => true), 'btn-primary'); ?></div>

<div id="orderTabs">
  <ul class="nav nav-tabs">
    <li class="active"><a data-target="#section_summary_content" data-toggle="tab"><?= 'Summary'; ?></a></li>
    <li><a data-target="#section_products_content" data-toggle="tab"><?= 'Products'; ?></a></li>
    <li><a data-target="#section_status_history_content" data-toggle="tab"><?= 'History'; ?></a></li>
  </ul>

  <div class="tab-content">
    <div id="section_summary_content" class="tab-pane active oscom-m-top-15">
      <div class="row">
        <div class="col-sm-4">
          <div class="panel panel-info">
            <div class="panel-heading">
              <h3 class="panel-title"><?= OSCOM::getDef('entry_customer'); ?></h3>
            </div>

            <div class="panel-body">
              <p><?= tep_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />'); ?></p>
              <p><?= '<i class="fa fa-fw fa-phone"></i> ' . $order->customer['telephone'] . '<br /><i class="fa fa-fw fa-envelope-o"></i> ' . '<a href="mailto:' . $order->customer['email_address'] . '"><u>' . $order->customer['email_address'] . '</u></a>'; ?></p>
            </div>
          </div>
        </div>

        <div class="col-sm-4">
          <div class="panel panel-info">
            <div class="panel-heading">
              <h3 class="panel-title"><?= OSCOM::getDef('entry_shipping_address'); ?></h3>
            </div>

            <div class="panel-body">
              <p><?= tep_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />'); ?></p>
            </div>
          </div>
        </div>

        <div class="col-sm-4">
          <div class="panel panel-info">
            <div class="panel-heading">
              <h3 class="panel-title"><?= OSCOM::getDef('entry_billing_address'); ?></h3>
            </div>

            <div class="panel-body">
              <p><?= tep_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />'); ?></p>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-sm-4">
          <div class="panel panel-info">
            <div class="panel-heading">
              <h3 class="panel-title"><?= OSCOM::getDef('entry_payment_method'); ?></h3>
            </div>

            <div class="panel-body">
              <p><?= $order->info['payment_method']; ?></p>

<?php
      if (tep_not_null($order->info['cc_type']) || tep_not_null($order->info['cc_owner']) || tep_not_null($order->info['cc_number'])) {
?>

              <table class="oscom-table table oscom-table-borderless table-condensed">
                <tbody>
                  <tr>
                    <td><?= OSCOM::getDef('entry_credit_card_type'); ?></td>
                    <td><?= $order->info['cc_type']; ?></td>
                  </tr>
                  <tr>
                    <td><?= OSCOM::getDef('entry_credit_card_owner'); ?></td>
                    <td><?= $order->info['cc_owner']; ?></td>
                  </tr>
                  <tr>
                    <td><?= OSCOM::getDef('entry_credit_card_number'); ?></td>
                    <td><?= $order->info['cc_number']; ?></td>
                  </tr>
                  <tr>
                    <td><?= OSCOM::getDef('entry_credit_card_expires'); ?></td>
                    <td><?= $order->info['cc_expires']; ?></td>
                  </tr>
                </tbody>
              </table>

<?php
      }
?>
            </div>
          </div>
        </div>

        <div class="col-sm-4">
          <div class="panel panel-info">
            <div class="panel-heading">
              <h3 class="panel-title"><?= OSCOM::getDef('entry_status'); ?></h3>
            </div>

            <div class="panel-body">
              <p><?= $order->info['status'] . '<br />' . (empty($order->info['last_modified']) ? DateTime::toShort($order->info['date_purchased'], true) : DateTime::toShort($order->info['last_modified'], true)); ?></p>
            </div>
          </div>
        </div>

        <div class="col-sm-4">
          <div class="panel panel-info">
            <div class="panel-heading">
              <h3 class="panel-title"><?= OSCOM::getDef('entry_total'); ?></h3>
            </div>

            <div class="panel-body">
              <p><?= strip_tags($order->info['total']); ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div id="section_products_content" class="tab-pane">
      <table class="oscom-table table table-hover">
        <thead>
          <tr class="info">
            <th colspan="2"><?= OSCOM::getDef('table_heading_products'); ?></th>
            <th><?= OSCOM::getDef('table_heading_products_model'); ?></th>
            <th class="text-right"><?= OSCOM::getDef('table_heading_tax'); ?></th>
            <th class="text-right"><?= OSCOM::getDef('table_heading_price_excluding_tax'); ?></th>
            <th class="text-right"><?= OSCOM::getDef('table_heading_price_including_tax'); ?></th>
            <th class="text-right"><?= OSCOM::getDef('table_heading_total_excluding_tax'); ?></th>
            <th class="text-right"><?= OSCOM::getDef('table_heading_total_including_tax'); ?></th>
          </tr>
        </thead>
        <tbody>

<?php
      for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
        echo '          <tr>' . "\n" .
             '            <td class="text-right" valign="top">' . $order->products[$i]['qty'] . '&nbsp;x</td>' . "\n" .
             '            <td valign="top">' . $order->products[$i]['name'];

        if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
          for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
            echo '<br /><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
            if ($order->products[$i]['attributes'][$j]['price'] != '0') echo ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')';
            echo '</i></small></nobr>';
          }
        }

        echo '</td>' . "\n" .
             '            <td valign="top">' . $order->products[$i]['model'] . '</td>' . "\n" .
             '            <td class="text-right" valign="top">' . tep_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n" .
             '            <td class="text-right" valign="top"><strong>' . $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" .
             '            <td class="text-right" valign="top"><strong>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax'], true), true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" .
             '            <td class="text-right" valign="top"><strong>' . $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" .
             '            <td class="text-right" valign="top"><strong>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax'], true) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</strong></td>' . "\n" .
             '          </tr>' . "\n";
      }
?>

        </tbody>
      </table>

      <table class="oscom-table table oscom-table-borderless table-condensed">
        <tbody>

<?php
      foreach ( $order->totals as $ot ) {
        echo '          <tr>' . "\n" .
             '            <td class="text-right">' . $ot['title'] . '</td>' . "\n" .
             '            <td class="text-right">' . strip_tags($ot['text']) . '</td>' . "\n" .
             '          </tr>' . "\n";
      }
?>

        </tbody>
      </table>
    </div>

    <div id="section_status_history_content" class="tab-pane oscom-m-top-15">
      <?= HTML::form('status', OSCOM::link(FILENAME_ORDERS, tep_get_all_get_params(array('action')) . 'action=update_order')); ?>

        <div class="form-group">
          <label for="inputOrderStatus" class="control-label"><?= OSCOM::getDef('entry_status'); ?></label>

          <?= HTML::selectField('status', $orders_statuses, $order->info['orders_status'], 'id="inputOrderStatus" class="form-control"'); ?>
        </div>

        <div class="form-group">
          <label for="inputOrderComment" class="control-label"><?= OSCOM::getDef('entry_add_comment'); ?></label>

          <?= HTML::textareaField('comments', '60', '6', null, 'id="inputOrderComment" class="form-control"'); ?>
        </div>

        <div class="form-group">
          <div class="checkbox">
            <label>
              <?= HTML::checkboxField('notify', '', true) . ' ' . OSCOM::getDef('entry_notify_customer'); ?>
            </label>
          </div>
        </div>

        <div class="form-group">
          <div class="checkbox">
            <label>
              <?= HTML::checkboxField('notify_comments', '', true) . ' ' . OSCOM::getDef('entry_notify_comments'); ?>
            </label>
          </div>
        </div>

        <div class="form-group">
          <?= HTML::button(OSCOM::getDef('image_update'), 'fa fa-save', null, null, 'btn-success'); ?>
        </div>
      </form>

      <table class="oscom-table table table-hover">
        <thead>
          <tr class="info">
            <th><?= OSCOM::getDef('table_heading_date_added'); ?></th>
            <th><?= OSCOM::getDef('table_heading_status'); ?></th>
            <th><?= OSCOM::getDef('table_heading_comments'); ?></th>
            <th class="text-right"><?= OSCOM::getDef('table_heading_customer_notified'); ?></th>
          </tr>
        </thead>
        <tbody>

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
          echo '          <tr>' . "\n" .
               '            <td valign="top">' . DateTime::toShort($Qhistory->value('date_added'), true) . '</td>' . "\n" .
               '            <td valign="top">' . $orders_status_array[$Qhistory->valueInt('orders_status_id')] . '</td>' . "\n" .
               '            <td valign="top">' . nl2br(HTML::output($Qhistory->value('comments'))) . '&nbsp;</td>' . "\n" .
               '            <td class="text-right" valign="top">';

          if ($Qhistory->valueInt('customer_notified') === 1) {
            echo HTML::image(OSCOM::linkImage('icons/tick.gif'), OSCOM::getDef('icon_tick'));
          } else {
            echo HTML::image(OSCOM::linkImage('icons/cross.gif'), OSCOM::getDef('icon_cross'));
          }

          echo '</td>' . "\n" .
               '          </tr>' . "\n";
        } while ($Qhistory->fetch());
      } else {
          echo '          <tr>' . "\n" .
               '            <td colspan="4">' . OSCOM::getDef('text_no_order_history') . '</td>' . "\n" .
               '          </tr>' . "\n";
      }
?>

        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $OSCOM_Hooks->output('Orders', 'Page', null, 'display'); ?>

<?php
    } else {
      $heading = $contents = [];

      switch ($action) {
        case 'delete':
          if (isset($order)) {
            $heading[] = array('text' => OSCOM::getDef('text_info_heading_delete_order'));

            $contents = array('form' => HTML::form('orders', OSCOM::link('orders.php', tep_get_all_get_params(array('action')) . '&action=deleteconfirm')));
            $contents[] = array('text' => OSCOM::getDef('text_info_delete_intro') . '<br /><br /><strong>#' . $order->info['id'] . '</strong> ' . HTML::outputProtected($order->customer['name']) . ' (' . strip_tags($order->info['total']) . ')');
            $contents[] = array('text' => HTML::checkboxField('restock') . ' ' . OSCOM::getDef('text_info_restock_product_quantity'));
            $contents[] = array('text' => HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash', null, null, 'btn-danger') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link('orders.php', tep_get_all_get_params(array('action'))), null, 'btn-link'));
          }
          break;
      }

      if (tep_not_null($heading) && tep_not_null($contents)) {
        $show_listing = false;

        echo HTML::panel($heading, $contents, ['type' => 'info']);
      }
    }
  }

  if ($show_listing === true) {
    echo HTML::form('orders', OSCOM::link('orders.php'), 'get', 'class="form-inline"', ['session_id' => true]) .
         HTML::inputField('oID', null, 'placeholder="' . OSCOM::getDef('heading_title_search') . '"') . HTML::hiddenField('action', 'edit') .
         '</form>' .
         HTML::form('status', OSCOM::link('orders.php'), 'get', 'class="form-inline"', ['session_id' => true]) .
         HTML::selectField('status', array_merge(array(array('id' => '', 'text' => OSCOM::getDef('text_all_orders'))), $orders_statuses), '', 'onchange="this.form.submit();"') .
         '</form>';
?>

<table class="oscom-table table table-hover">
  <thead>
    <tr class="info">
      <th><?= OSCOM::getDef('table_heading_customers'); ?></th>
      <th class="text-right"><?= OSCOM::getDef('table_heading_order_total'); ?></th>
      <th></th>
      <th class="text-right"><?= OSCOM::getDef('table_heading_date_purchased'); ?></th>
      <th class="text-right"><?= OSCOM::getDef('table_heading_status'); ?></th>
      <th class="action"></th>
    </tr>
  </thead>
  <tbody>

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

    $Qorders->bindInt(':language_id', $OSCOM_Language->getId());
    $Qorders->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
    $Qorders->execute();

    while ($Qorders->fetch()) {
?>

    <tr>
      <td><?= '<a href="' . OSCOM::link('orders.php', tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $Qorders->valueInt('orders_id') . '&action=edit') . '">' . $Qorders->value('customers_name') . '</a> <small class="text-muted">#' . $Qorders->valueInt('orders_id') . '</small>'; ?></td>
      <td class="text-right"><?= strip_tags($Qorders->value('order_total')) . ' <small class="text-muted">' . $Qorders->value('currency') . '</small>'; ?></td>
      <td><div class="oscom-truncate" style="width: 150px;"><small class="text-muted"><?= $Qorders->value('payment_method'); ?></small></div></td>
      <td class="text-right"><?= DateTime::toShort($Qorders->value('date_purchased'), true); ?></td>
      <td class="text-right"><?= $Qorders->value('orders_status_name'); ?></td>
      <td class="action"><?=
        '<a href="' . OSCOM::link('orders.php', tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $Qorders->valueInt('orders_id') . '&action=edit') . '"><i class="fa fa-pencil" title="' . OSCOM::getDef('image_edit') . '"></i></a>
         <a href="' . OSCOM::link('orders.php', tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $Qorders->valueInt('orders_id') . '&action=delete') . '"><i class="fa fa-trash" title="' . OSCOM::getDef('image_delete') . '"></i></a>
         <a href="' . OSCOM::link('invoice.php', 'oID=' . $Qorders->valueInt('orders_id')) . '" target="_blank"><i class="fa fa-file-text-o" title="' . OSCOM::getDef('image_orders_invoice') . '"></i></a>
         <a href="' . OSCOM::link('packingslip.php', 'oID=' . $Qorders->valueInt('orders_id')) . '" target="_blank"><i class="fa fa-clipboard" title="' . OSCOM::getDef('image_orders_packingslip') . '"></i></a>';
      ?></td>
    </tr>

<?php
    }
?>

  </tbody>
</table>

<div>
  <span class="pull-right"><?= $Qorders->getPageSetLinks(tep_get_all_get_params()); ?></span>
  <?= $Qorders->getPageSetLabel(OSCOM::getDef('text_display_number_of_orders')); ?>
</div>

<?php
  }

  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
