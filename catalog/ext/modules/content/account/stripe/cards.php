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

  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  if ( defined('MODULE_PAYMENT_INSTALLED') && tep_not_null(MODULE_PAYMENT_INSTALLED) && in_array('stripe.php', explode(';', MODULE_PAYMENT_INSTALLED)) ) {
    if ( !class_exists('stripe') ) {
      include(DIR_WS_LANGUAGES . $language . '/modules/payment/stripe.php');
      include(DIR_WS_MODULES . 'payment/stripe.php');
    }

    $stripe = new stripe();

    if ( !$stripe->enabled ) {
      tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
    }
  } else {
    tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  }

  require(DIR_WS_LANGUAGES . $language . '/modules/content/account/cm_account_stripe_cards.php');
  require('includes/modules/content/account/cm_account_stripe_cards.php');
  $stripe_cards = new cm_account_stripe_cards();

  if ( !$stripe_cards->isEnabled() ) {
    tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  }

  if ( isset($HTTP_GET_VARS['action']) ) {
    if ( ($HTTP_GET_VARS['action'] == 'delete') && isset($HTTP_GET_VARS['id']) && is_numeric($HTTP_GET_VARS['id']) && isset($HTTP_GET_VARS['formid']) && ($HTTP_GET_VARS['formid'] == md5($sessiontoken))) {
      $token_query = tep_db_query("select id, stripe_token from customers_stripe_tokens where id = '" . (int)$HTTP_GET_VARS['id'] . "' and customers_id = '" . (int)$customer_id . "'");

      if ( tep_db_num_rows($token_query) ) {
        $token = tep_db_fetch_array($token_query);

        list($customer, $card) = explode(':|:', $token['stripe_token'], 2);

        $stripe->deleteCard($card, $customer, $token['id']);

        $messageStack->add_session('cards', MODULE_CONTENT_ACCOUNT_STRIPE_CARDS_SUCCESS_DELETED, 'success');
      }
    }

    tep_redirect(tep_href_link('ext/modules/content/account/stripe/cards.php', '', 'SSL'));
  }

  $breadcrumb->add(MODULE_CONTENT_ACCOUNT_STRIPE_CARDS_NAVBAR_TITLE_1, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add(MODULE_CONTENT_ACCOUNT_STRIPE_CARDS_NAVBAR_TITLE_2, tep_href_link('ext/modules/content/account/stripe/cards.php', '', 'SSL'));

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<h1><?php echo MODULE_CONTENT_ACCOUNT_STRIPE_CARDS_HEADING_TITLE; ?></h1>

<?php
  if ($messageStack->size('cards') > 0) {
    echo $messageStack->output('cards');
  }
?>

<div class="contentContainer">
  <?php echo MODULE_CONTENT_ACCOUNT_STRIPE_CARDS_TEXT_DESCRIPTION; ?>

  <h2><?php echo MODULE_CONTENT_ACCOUNT_STRIPE_CARDS_SAVED_CARDS_TITLE; ?></h2>

  <div class="contentText">

<?php
  $tokens_query = tep_db_query("select id, card_type, number_filtered, expiry_date from customers_stripe_tokens where customers_id = '" . (int)$customer_id . "' order by date_added");

  if ( tep_db_num_rows($tokens_query) > 0 ) {
    while ( $tokens = tep_db_fetch_array($tokens_query) ) {
?>

    <div>
      <span style="float: right;"><?php echo tep_draw_button(SMALL_IMAGE_BUTTON_DELETE, 'trash', tep_href_link('ext/modules/content/account/stripe/cards.php', 'action=delete&id=' . (int)$tokens['id'] . '&formid=' . md5($sessiontoken), 'SSL')); ?></span>
      <p><strong><?php echo tep_output_string_protected($tokens['card_type']); ?></strong>&nbsp;&nbsp;****<?php echo tep_output_string_protected($tokens['number_filtered']) . '&nbsp;&nbsp;' . tep_output_string_protected(substr($tokens['expiry_date'], 0, 2) . '/' . substr($tokens['expiry_date'], 2)); ?></p>
    </div>

<?php
    }
  } else {
?>

    <div style="background-color: #FEEFB3; border: 1px solid #9F6000; margin: 10px 0px; padding: 5px 10px; border-radius: 10px;">
      <?php echo MODULE_CONTENT_ACCOUNT_STRIPE_CARDS_TEXT_NO_CARDS; ?>
    </div>

<?php
  }
?>

  </div>

  <div class="buttonSet">
    <?php echo tep_draw_button(IMAGE_BUTTON_BACK, 'triangle-1-w', tep_href_link(FILENAME_ACCOUNT, '', 'SSL')); ?>
  </div>
</div>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
