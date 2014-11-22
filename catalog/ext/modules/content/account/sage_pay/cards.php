<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../../');
  require('includes/application_top.php');

  if (!isset($_SESSION['customer_id'])) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link('login.php', '', 'SSL'));
  }

  if ( defined('MODULE_PAYMENT_INSTALLED') && tep_not_null(MODULE_PAYMENT_INSTALLED) && in_array('sage_pay_direct.php', explode(';', MODULE_PAYMENT_INSTALLED)) ) {
    if ( !class_exists('sage_pay_direct') ) {
      include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/sage_pay_direct.php');
      include(DIR_WS_MODULES . 'payment/sage_pay_direct.php');
    }

    $sage_pay_direct = new sage_pay_direct();

    if ( !$sage_pay_direct->enabled ) {
      tep_redirect(tep_href_link('account.php', '', 'SSL'));
    }
  } else {
    tep_redirect(tep_href_link('account.php', '', 'SSL'));
  }

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/content/account/cm_account_sage_pay_cards.php');
  require('includes/modules/content/account/cm_account_sage_pay_cards.php');
  $sage_pay_cards = new cm_account_sage_pay_cards();

  if ( !$sage_pay_cards->isEnabled() ) {
    tep_redirect(tep_href_link('account.php', '', 'SSL'));
  }

  if ( isset($_GET['action']) ) {
    if ( ($_GET['action'] == 'delete') && isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['formid']) && ($_GET['formid'] == md5($_SESSION['sessiontoken']))) {
      $token_query = tep_db_query("select id, sagepay_token from customers_sagepay_tokens where id = '" . (int)$_GET['id'] . "' and customers_id = '" . (int)$customer_id . "'");

      if ( tep_db_num_rows($token_query) ) {
        $token = tep_db_fetch_array($token_query);

        $sage_pay_direct->deleteCard($token['sagepay_token'], $token['id']);

        $messageStack->add_session('cards', MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_SUCCESS_DELETED, 'success');
      }
    }

    tep_redirect(tep_href_link('ext/modules/content/account/sage_pay/cards.php', '', 'SSL'));
  }

  $breadcrumb->add(MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_NAVBAR_TITLE_1, tep_href_link('account.php', '', 'SSL'));
  $breadcrumb->add(MODULE_CONTENT_ACCOUNT_SAGE_PAY_CARDS_NAVBAR_TITLE_2, tep_href_link('ext/modules/content/account/sage_pay/cards.php', '', 'SSL'));

  require(DIR_WS_INCLUDES . 'template_top.php');
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
  $tokens_query = tep_db_query("select id, card_type, number_filtered, expiry_date from customers_sagepay_tokens where customers_id = '" . (int)$customer_id . "' order by date_added");

  if ( tep_db_num_rows($tokens_query) > 0 ) {
    while ( $tokens = tep_db_fetch_array($tokens_query) ) {
?>

    <div>
      <span style="float: right;"><?php echo tep_draw_button(SMALL_IMAGE_BUTTON_DELETE, 'glyphicon glyphicon-trash', tep_href_link('ext/modules/content/account/sage_pay/cards.php', 'action=delete&id=' . (int)$tokens['id'] . '&formid=' . md5($_SESSION['sessiontoken']), 'SSL')); ?></span>
      <p><strong><?php echo tep_output_string_protected($tokens['card_type']); ?></strong>&nbsp;&nbsp;****<?php echo tep_output_string_protected($tokens['number_filtered']) . '&nbsp;&nbsp;' . tep_output_string_protected(substr($tokens['expiry_date'], 0, 2) . '/' . substr($tokens['expiry_date'], 2)); ?></p>
    </div>

<?php
    }
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
    <?php echo tep_draw_button(IMAGE_BUTTON_BACK, 'glyphicon glyphicon-chevron-left', tep_href_link('account.php', '', 'SSL')); ?>
  </div>
</div>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
