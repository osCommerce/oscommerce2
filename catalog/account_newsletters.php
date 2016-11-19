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

  $OSCOM_Language->loadDefinitions('account_newsletters');

  $Qnewsletter = $OSCOM_Db->prepare('select customers_newsletter from :table_customers where customers_id = :customers_id');
  $Qnewsletter->bindInt(':customers_id', $_SESSION['customer_id']);
  $Qnewsletter->execute();

  if (isset($_POST['action']) && ($_POST['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
    $newsletter_general = (isset($_POST['newsletter_general']) && ($_POST['newsletter_general'] == '1')) ? 1 : 0;

    if ($newsletter_general !== $Qnewsletter->valueInt('customers_newsletter')) {
      $newsletter_general = ($Qnewsletter->valueInt('customers_newsletter') === 1) ? 0 : 1;

      $OSCOM_Db->save('customers', ['customers_newsletter' => $newsletter_general], ['customers_id' => $_SESSION['customer_id']]);
    }

    $messageStack->add_session('account', OSCOM::getDef('success_newsletter_updated'), 'success');

    OSCOM::redirect('account.php');
  }

  $breadcrumb->add(OSCOM::getDef('navbar_title_1'), OSCOM::link('account.php'));
  $breadcrumb->add(OSCOM::getDef('navbar_title_2'), OSCOM::link('account_newsletters.php'));

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="page-header">
  <h1><?php echo OSCOM::getDef('heading_title'); ?></h1>
</div>

<?php echo HTML::form('account_newsletter', OSCOM::link('account_newsletters.php'), 'post', 'class="form-horizontal"', ['tokenize' => true, 'action' => 'process']); ?>

<div class="contentContainer">
  <div class="contentText">
    <div class="form-group">
      <label class="control-label col-sm-4"><?php echo OSCOM::getDef('my_newsletters_general_newsletter'); ?></label>
      <div class="col-sm-8">
        <div class="checkbox">
          <label>
            <?php echo HTML::checkboxField('newsletter_general', '1', (($Qnewsletter->value('customers_newsletter') == '1') ? true : false)); ?>
            <?php if (tep_not_null(OSCOM::getDef('my_newsletters_general_newsletter_description'))) echo ' ' . OSCOM::getDef('my_newsletters_general_newsletter_description'); ?>
          </label>
        </div>
      </div>
    </div>
  </div>

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
