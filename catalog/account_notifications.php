<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  if (!isset($_SESSION['customer_id'])) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link('login.php', '', 'SSL'));
  }

// needs to be included earlier to set the success message in the messageStack
  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/account_notifications.php');

  $global_query = tep_db_query("select global_product_notifications from customers_info where customers_info_id = '" . (int)$customer_id . "'");
  $global = tep_db_fetch_array($global_query);

  if (isset($_POST['action']) && ($_POST['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
    if (isset($_POST['product_global']) && is_numeric($_POST['product_global'])) {
      $product_global = tep_db_prepare_input($_POST['product_global']);
    } else {
      $product_global = '0';
    }

    (array)$products = $_POST['products'];

    if ($product_global != $global['global_product_notifications']) {
      $product_global = (($global['global_product_notifications'] == '1') ? '0' : '1');

      tep_db_query("update customers_info set global_product_notifications = '" . (int)$product_global . "' where customers_info_id = '" . (int)$customer_id . "'");
    } elseif (sizeof($products) > 0) {
      $products_parsed = array();
      reset($products);
      foreach ($products as $value) {
        if (is_numeric($value)) {
          $products_parsed[] = $value;
        }
      }

      if (sizeof($products_parsed) > 0) {
        $check_query = tep_db_query("select count(*) as total from products_notifications where customers_id = '" . (int)$_SESSION['customer_id'] . "' and products_id not in (" . implode(',', $products_parsed) . ")");
        $check = tep_db_fetch_array($check_query);

        if ($check['total'] > 0) {
          tep_db_query("delete from products_notifications where customers_id = '" . (int)$_SESSION['customer_id'] . "' and products_id not in (" . implode(',', $products_parsed) . ")");
        }
      }
    } else {
      $check_query = tep_db_query("select count(*) as total from products_notifications where customers_id = '" . (int)$_SESSION['customer_id'] . "'");
      $check = tep_db_fetch_array($check_query);

      if ($check['total'] > 0) {
        tep_db_query("delete from products_notifications where customers_id = '" . (int)$_SESSION['customer_id'] . "'");
      }
    }

    $messageStack->add_session('account', SUCCESS_NOTIFICATIONS_UPDATED, 'success');

    tep_redirect(tep_href_link('account.php', '', 'SSL'));
  }

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link('account.php', '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link('account_notifications.php', '', 'SSL'));

  require('includes/template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php echo tep_draw_form('account_notifications', tep_href_link('account_notifications.php', '', 'SSL'), 'post', 'class="form-horizontal" role="form"', true) . tep_draw_hidden_field('action', 'process'); ?>

<div class="contentContainer">
  <div class="alert alert-info">
    <?php echo MY_NOTIFICATIONS_DESCRIPTION; ?>
  </div>

  <div class="contentText">
    <div class="form-group">
      <label class="control-label col-sm-4"><?php echo GLOBAL_NOTIFICATIONS_TITLE; ?></label>
      <div class="col-sm-8">
        <div class="checkbox">
          <label>
            <?php echo tep_draw_checkbox_field('product_global', '1', (($global['global_product_notifications'] == '1') ? true : false)); ?>
            <?php if (tep_not_null(GLOBAL_NOTIFICATIONS_DESCRIPTION)) echo ' ' . GLOBAL_NOTIFICATIONS_DESCRIPTION; ?>
          </label>
        </div>
      </div>
    </div>
  </div>

<?php
  if ($global['global_product_notifications'] != '1') {
?>

  <div class="contentText">

<?php
    $products_check_query = tep_db_query("select count(*) as total from products_notifications where customers_id = '" . (int)$_SESSION['customer_id'] . "'");
    $products_check = tep_db_fetch_array($products_check_query);
    if ($products_check['total'] > 0) {
?>

    <div class="clearfix"></div>
    <div class="alert alert-warning"><?php echo NOTIFICATIONS_DESCRIPTION; ?></div>

    <div class="contentText">
      <div class="form-group">
        <label class="control-label col-sm-4"><?php echo MY_NOTIFICATIONS_TITLE; ?></label>
        <div class="col-sm-8">

<?php
      $counter = 0;
      $products_query = tep_db_query("select pd.products_id, pd.products_name from products_description pd, products_notifications pn where pn.customers_id = '" . (int)$_SESSION['customer_id'] . "' and pn.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by pd.products_name");
      while ($products = tep_db_fetch_array($products_query)) {
?>
      <div class="checkbox">
        <label>
          <?php echo tep_draw_checkbox_field('products[' . $counter . ']', $products['products_id'], true) . $products['products_name']; ?>
        </label>
      </div>
<?php
        $counter++;
      }
?>

        </div>
      </div>
    </div>

<?php
    } else {
?>

    <div class="alert alert-warning">
      <?php echo NOTIFICATIONS_NON_EXISTING; ?>
    </div>

<?php
    }
?>

  </div>

<?php
  }
?>

  <div class="row">
    <div class="col-sm-6 text-right pull-right"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', null, 'primary', null, 'btn-success'); ?></div>
    <div class="col-sm-6"><?php echo tep_draw_button(IMAGE_BUTTON_BACK, 'glyphicon glyphicon-chevron-left', tep_href_link('account.php', '', 'SSL')); ?></div>
  </div>
</div>

</form>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
