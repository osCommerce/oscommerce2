<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  chdir('../../../../../');
  require('includes/application_top.php');

  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot();
    OSCOM::redirect('login.php');
  }

  if ( defined('MODULE_PAYMENT_INSTALLED') && tep_not_null(MODULE_PAYMENT_INSTALLED) && in_array('sage_pay_direct.php', explode(';', MODULE_PAYMENT_INSTALLED)) ) {
    if ( !class_exists('sage_pay_direct') ) {
      $OSCOM_Language->loadDefinitions('modules/payment/sage_pay_direct');

      include('includes/modules/payment/sage_pay_direct.php');
    }

    $sage_pay_direct = new sage_pay_direct();

    if ( !$sage_pay_direct->enabled ) {
      OSCOM::redirect('account.php');
    }
  } else {
    OSCOM::redirect('account.php');
  }

  $OSCOM_Language->loadDefinitions('modules/content/account/cm_account_sage_pay_cards');
  require('includes/modules/content/account/cm_account_sage_pay_cards.php');
  $sage_pay_cards = new cm_account_sage_pay_cards();

  if ( !$sage_pay_cards->isEnabled() ) {
    OSCOM::redirect('account.php');
  }

  if ( isset($_GET['action']) ) {
    if ( ($_GET['action'] == 'delete') && isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['formid']) && ($_GET['formid'] == md5($_SESSION['sessiontoken']))) {
      $Qtoken = $OSCOM_Db->get('customers_sagepay_tokens', ['id', 'sagepay_token'], ['id' => $_GET['id'], 'customers_id' => $_SESSION['customer_id']]);

      if ($Qtoken->fetch() !== false) {
        $sage_pay_direct->deleteCard($Qtoken->value('sagepay_token'), $Qtoken->valueInt('id'));

        $messageStack->add_session('cards', MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_SUCCESS_DELETED, 'success');
      }
    }

    OSCOM::redirect('ext/modules/content/account/sage_pay/cards.php');
  }

  $breadcrumb->add(MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_NAVBAR_TITLE_1, OSCOM::link('account.php'));
  $breadcrumb->add(MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_NAVBAR_TITLE_2, OSCOM::link('ext/modules/content/account/sage_pay/cards.php'));

  require($oscTemplate->getFile('template_top.php'));
?>

<h1><?php echo MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_HEADING_TITLE; ?></h1>

<?php
  if ($messageStack->size('cards') > 0) {
    echo $messageStack->output('cards');
  }
?>

<div class="contentContainer">
  <?php echo MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_TEXT_DESCRIPTION; ?>

  <h2><?php echo MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_SAVED_CARDS_TITLE; ?></h2>

  <div class="contentText">

<?php
  $Qtokens = $OSCOM_Db->get('customers_sagepay_tokens', ['id', 'card_type', 'number_filtered', 'expiry_date'], ['customers_id' => $_SESSION['customer_id']], 'date_added');

  if ($Qtokens->fetch() !== false) {
    do {
?>

    <div>
      <span style="float: right;"><?php echo HTML::button(OSCOM::getDef('small_image_button_delete'), 'glyphicon glyphicon-trash', OSCOM::link('ext/modules/content/account/sage_pay/cards.php', 'action=delete&id=' . $Qtokens->valueInt('id') . '&formid=' . md5($_SESSION['sessiontoken']))); ?></span>
      <p><strong><?php echo $Qtokens->valueProtected('card_type'); ?></strong>&nbsp;&nbsp;****<?php echo $Qtokens->valueProtected('number_filtered') . '&nbsp;&nbsp;' . HTML::outputProtected(substr($Qtokens->value('expiry_date'), 0, 2) . '/' . substr($Qtokens->value('expiry_date'), 2)); ?></p>
    </div>

<?php
    } while ($Qtokens->fetch());
  } else {
?>

    <div style="background-color: #FEEFB3; border: 1px solid #9F6000; margin: 10px 0px; padding: 5px 10px; border-radius: 10px;">
      <?php echo MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_TEXT_NO_CARDS; ?>
    </div>

<?php
  }
?>

  </div>

  <div class="buttonSet">
    <?php echo HTML::button(OSCOM::getDef('image_button_back'), 'glyphicon glyphicon-chevron-left', OSCOM::link('account.php')); ?>
  </div>
</div>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
