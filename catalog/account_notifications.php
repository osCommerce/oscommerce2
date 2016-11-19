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

  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot();
    OSCOM::redirect('login.php');
  }

  $OSCOM_Language->loadDefinitions('account_notifications');

  $Qglobal = $OSCOM_Db->prepare('select global_product_notifications from :table_customers_info where customers_info_id = :customers_info_id');
  $Qglobal->bindInt(':customers_info_id', $_SESSION['customer_id']);
  $Qglobal->execute();

  if (isset($_POST['action']) && ($_POST['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
    if (isset($_POST['product_global']) && is_numeric($_POST['product_global']) && in_array($_POST['product_global'], ['0', '1'])) {
      $product_global = (int)$_POST['product_global'];
    } else {
      $product_global = 0;
    }

    (array)$products = $_POST['products'];

    if ($product_global !== $Qglobal->valueInt('global_product_notifications')) {
      $product_global = ($Qglobal->valueInt('global_product_notifications') === 1) ? 0 : 1;

      $OSCOM_Db->save('customers_info', ['global_product_notifications' => $product_global], ['customers_info_id' => $_SESSION['customer_id']]);
    } elseif (sizeof($products) > 0) {
      $products_parsed = array();
      foreach ($products as $value) {
        if (is_numeric($value) && !in_array($value, $products_parsed)) {
          $products_parsed[] = $value;
        }
      }

      if (sizeof($products_parsed) > 0) {
        $products_id_in = array_map(function($k) {
          return ':products_id_' . $k;
        }, array_keys($products_parsed));

        $Qcheck = $OSCOM_Db->prepare('select products_id from :table_products_notifications where customers_id = :customers_id and products_id not in (' . implode(', ', $products_id_in) . ') limit 1');
        $Qcheck->bindInt(':customers_id', $_SESSION['customer_id']);

        foreach ($products_parsed as $k => $v) {
          $Qcheck->bindInt(':products_id_' . $k, $v);
        }

        $Qcheck->execute();

        if ($Qcheck->fetch() !== false) {
          $Qdelete = $OSCOM_Db->prepare('delete from :table_products_notifications where customers_id = :customers_id and products_id not in (' . implode(', ', $products_id_in) . ')');
          $Qdelete->bindInt(':customers_id', $_SESSION['customer_id']);

          foreach ($products_parsed as $k => $v) {
            $Qdelete->bindInt(':products_id_' . $k, $v);
          }

          $Qdelete->execute();
        }
      }
    } else {
      $Qcheck = $OSCOM_Db->prepare('select products_id from :table_products_notifications where customers_id = :customers_id limit 1');
      $Qcheck->bindInt(':customers_id', $_SESSION['customer_id']);
      $Qcheck->execute();

      if ($Qcheck->fetch() !== false) {
        $OSCOM_Db->delete('products_notifications', ['customers_id' => $_SESSION['customer_id']]);
      }
    }

    $messageStack->add_session('account', OSCOM::getDef('success_notifications_updated'), 'success');

    OSCOM::redirect('account.php');
  }

  $breadcrumb->add(OSCOM::getDef('navbar_title_1'), OSCOM::link('account.php'));
  $breadcrumb->add(OSCOM::getDef('navbar_title_2'), OSCOM::link('account_notifications.php'));

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo OSCOM::getDef('heading_title'); ?></h1>
</div>

<?php echo HTML::form('account_notifications', OSCOM::link('account_notifications.php'), 'post', 'class="form-horizontal"', ['tokenize' => true, 'action' => 'process']); ?>

<div class="contentContainer">

  <div class="alert alert-info">
    <?php echo OSCOM::getDef('my_notifications_description'); ?>
  </div>

  <div class="contentText">
    <div class="form-group">
      <label class="control-label col-sm-4"><?php echo OSCOM::getDef('global_notifications_title'); ?></label>
      <div class="col-sm-8">
        <div class="checkbox">
          <label>
            <?php echo HTML::checkboxField('product_global', '1', (($Qglobal->valueInt('global_product_notifications') === 1) ? true : false)); ?>
            <?php if (tep_not_null(OSCOM::getDef('global_notifications_description'))) echo ' ' . OSCOM::getDef('global_notifications_description'); ?>
          </label>
        </div>
      </div>
    </div>
  </div>

<?php
  if ($Qglobal->valueInt('global_product_notifications') !== 1) {
?>

  <div class="contentText">

<?php
    $Qcheck = $OSCOM_Db->prepare('select products_id from :table_products_notifications where customers_id = :customers_id limit 1');
    $Qcheck->bindInt(':customers_id', $_SESSION['customer_id']);
    $Qcheck->execute();

    if ($Qcheck->fetch() !== false) {
?>

    <div class="clearfix"></div>
    <div class="alert alert-warning"><?php echo OSCOM::getDef('notifications_description'); ?></div>

    <div class="contentText">
      <div class="form-group">
        <label class="control-label col-sm-4"><?php echo OSCOM::getDef('my_notifications_title'); ?></label>
        <div class="col-sm-8">

<?php
      $Qproducts = $OSCOM_Db->prepare('select pd.products_id, pd.products_name from :table_products_description pd, :table_products_notifications pn where pn.customers_id = :customers_id and pn.products_id = pd.products_id and pd.language_id = :language_id order by pd.products_name');
      $Qproducts->bindInt(':customers_id', $_SESSION['customer_id']);
      $Qproducts->bindInt(':language_id', $OSCOM_Language->getId());
      $Qproducts->execute();

      while ($Qproducts->fetch()) {
?>
      <div class="checkbox">
        <label>
          <?php echo HTML::checkboxField('products[]', $Qproducts->valueInt('products_id'), true) . $Qproducts->value('products_name'); ?>
        </label>
      </div>
<?php
      }
?>

        </div>
      </div>
    </div>

<?php
    } else {
?>

    <div class="alert alert-warning">
      <?php echo OSCOM::getDef('notifications_non_existing'); ?>
    </div>

<?php
    }
?>

  </div>

<?php
  }
?>

  <div class="buttonSet row">
    <div class="col-xs-6"><?php echo HTML::button(OSCOM::getDef('image_button_back'), 'fa fa-angle-left', OSCOM::link('account.php')); ?></div>
    <div class="col-xs-6 text-right"><?php echo HTML::button(OSCOM::getDef('image_button_continue'), 'fa fa-angle-right', null, null, 'btn-success'); ?></div>
  </div>
</div>

</form>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
