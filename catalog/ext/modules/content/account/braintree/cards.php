<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

chdir('../../../../../');
require('includes/application_top.php');

if (!tep_session_is_registered('customer_id')) {
  $navigation->set_snapshot();
  tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
}

if (!class_exists('braintree_cc', false)) {
  include(DIR_FS_CATALOG . 'includes/modules/payment/braintree_cc.php');
}

$pm = new braintree_cc();

if (($pm->enabled !== true) || (OSCOM_APP_PAYPAL_BRAINTREE_CC_CC_TOKENS == '0')) {
  tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
}

if (!class_exists('cm_account_braintree_cards', false)) {
  include(DIR_FS_CATALOG . 'includes/modules/content/account/cm_account_braintree_cards.php');
}

$cm = new cm_account_braintree_cards();

if (!$cm->isEnabled()) {
  tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
}

if (isset($HTTP_GET_VARS['action'])) {
  if ( ($HTTP_GET_VARS['action'] == 'delete') && isset($HTTP_GET_VARS['id']) && is_numeric($HTTP_GET_VARS['id']) && isset($HTTP_GET_VARS['formid']) && ($HTTP_GET_VARS['formid'] == md5($sessiontoken))) {
    $token_query = tep_db_query("select id, braintree_token from customers_braintree_tokens where id = '" . (int)$HTTP_GET_VARS['id'] . "' and customers_id = '" . (int)$customer_id . "'");

    if (tep_db_num_rows($token_query)) {
      $token = tep_db_fetch_array($token_query);

      $pm->deleteCard($token['braintree_token'], $token['id']);

      $messageStack->add_session('cards', $cm->_app->getDef('account_braintree_cards_success_deleted'), 'success');
    }
  }

  tep_redirect(tep_href_link('ext/modules/content/account/braintree/cards.php', '', 'SSL'));
}

$breadcrumb->add($cm->_app->getDef('account_braintree_cards_navbar_title_1'), tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add($cm->_app->getDef('account_braintree_cards_navbar_title_2'), tep_href_link('ext/modules/content/account/braintree/cards.php', '', 'SSL'));

require(DIR_WS_INCLUDES . 'template_top.php');
?>

<h1><?php echo $cm->_app->getDef('account_braintree_cards_heading_title'); ?></h1>

<?php
if ($messageStack->size('cards') > 0) {
  echo $messageStack->output('cards');
}
?>

<div class="contentContainer">
  <?php echo $cm->_app->getDef('account_braintree_cards_text_description'); ?>

  <h2><?php echo $cm->_app->getDef('account_braintree_cards_saved_cards_title'); ?></h2>

  <div class="contentText">

<?php
$tokens_query = tep_db_query("select id, card_type, number_filtered, expiry_date from customers_braintree_tokens where customers_id = '" . (int)$customer_id . "' order by date_added");

if ( tep_db_num_rows($tokens_query) > 0 ) {
  while ( $tokens = tep_db_fetch_array($tokens_query) ) {
?>

    <div>
      <span style="float: right;"><?php echo tep_draw_button(SMALL_IMAGE_BUTTON_DELETE, 'trash', tep_href_link('ext/modules/content/account/braintree/cards.php', 'action=delete&id=' . (int)$tokens['id'] . '&formid=' . md5($sessiontoken), 'SSL')); ?></span>
      <p><strong><?php echo tep_output_string_protected($tokens['card_type']); ?></strong>&nbsp;&nbsp;****<?php echo tep_output_string_protected($tokens['number_filtered']) . '&nbsp;&nbsp;' . tep_output_string_protected(substr($tokens['expiry_date'], 0, 2) . '/' . substr($tokens['expiry_date'], 2)); ?></p>
    </div>

<?php
  }
} else {
?>

    <div style="background-color: #FEEFB3; border: 1px solid #9F6000; margin: 10px 0px; padding: 5px 10px; border-radius: 10px;">
      <?php echo $cm->_app->getDef('account_braintree_cards_text_no_cards'); ?>
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
