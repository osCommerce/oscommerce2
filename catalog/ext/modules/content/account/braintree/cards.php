<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  chdir('../../../../../');
  require('includes/application_top.php');

  if (!isset($_SESSION['customer_id'])) {
    $_SESSION['navigation']->set_snapshot();
    OSCOM::redirect('login.php', '', 'SSL');
  }

  if ( defined('MODULE_PAYMENT_INSTALLED') && tep_not_null(MODULE_PAYMENT_INSTALLED) && in_array('braintree_cc.php', explode(';', MODULE_PAYMENT_INSTALLED)) ) {
    if ( !class_exists('braintree_cc') ) {
      include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/braintree_cc.php');
      include(DIR_WS_MODULES . 'payment/braintree_cc.php');
    }

    $braintree_cc = new braintree_cc();

    if ( !$braintree_cc->enabled ) {
      OSCOM::redirect('account.php', '', 'SSL');
    }
  } else {
    OSCOM::redirect('account.php', '', 'SSL');
  }

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/content/account/cm_account_braintree_cards.php');
  require('includes/modules/content/account/cm_account_braintree_cards.php');
  $braintree_cards = new cm_account_braintree_cards();

  if ( !$braintree_cards->isEnabled() ) {
    OSCOM::redirect('account.php', '', 'SSL');
  }

  if ( isset($_GET['action']) ) {
    if ( ($_GET['action'] == 'delete') && isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['formid']) && ($_GET['formid'] == md5($_SESSION['sessiontoken']))) {
      $Qtoken = $OSCOM_Db->get('customers_braintree_tokens', ['id', 'braintree_token'], ['id' => $_GET['id'], 'customers_id' => $_SESSION['customer_id']]);

      if ($Qtoken->fetch() !== false) {
        $braintree_cc->deleteCard($Qtoken->value('braintree_token'), $Qtoken->valueInt('id'));

        $messageStack->add_session('cards', MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_SUCCESS_DELETED, 'success');
      }
    }

    OSCOM::redirect('ext/modules/content/account/braintree/cards.php', '', 'SSL');
  }

  $breadcrumb->add(MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_NAVBAR_TITLE_1, OSCOM::link('account.php', '', 'SSL'));
  $breadcrumb->add(MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_NAVBAR_TITLE_2, OSCOM::link('ext/modules/content/account/braintree/cards.php', '', 'SSL'));

  require('includes/template_top.php');
?>

<h1><?php echo MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_HEADING_TITLE; ?></h1>

<?php
  if ($messageStack->size('cards') > 0) {
    echo $messageStack->output('cards');
  }
?>

<div class="contentContainer">
  <?php echo MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_TEXT_DESCRIPTION; ?>

  <h2><?php echo MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_SAVED_CARDS_TITLE; ?></h2>

  <div class="contentText">

<?php
  $Qtokens = $OSCOM_Db->get('customers_braintree_tokens', ['id', 'card_type', 'number_filtered', 'expiry_date'], ['customers_id' => $_SESSION['customer_id']], 'date_added');

  if ($Qtokens->fetch() !== false) {
    do {
?>

    <div>
      <span style="float: right;"><?php echo HTML::button(SMALL_IMAGE_BUTTON_DELETE, 'glyphicon glyphicon-trash', OSCOM::link('ext/modules/content/account/braintree/cards.php', 'action=delete&id=' . $Qtokens->valueInt('id') . '&formid=' . md5($_SESSION['sessiontoken']), 'SSL')); ?></span>
      <p><strong><?php echo $Qtokens->valueProtected('card_type'); ?></strong>&nbsp;&nbsp;****<?php echo $Qtokens->valueProtected('number_filtered') . '&nbsp;&nbsp;' . HTML::outputProtected(substr($Qtokens->value('expiry_date'), 0, 2) . '/' . substr($Qtokens->value('expiry_date'), 2)); ?></p>
    </div>

<?php
    } while ($Qtokens->fetch());
  } else {
?>

    <div style="background-color: #FEEFB3; border: 1px solid #9F6000; margin: 10px 0px; padding: 5px 10px; border-radius: 10px;">
      <?php echo MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_TEXT_NO_CARDS; ?>
    </div>

<?php
  }
?>

  </div>

  <div class="buttonSet">
    <?php echo HTML::button(IMAGE_BUTTON_BACK, 'glyphicon glyphicon-chevron-left', OSCOM::link('account.php', '', 'SSL')); ?>
  </div>
</div>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
