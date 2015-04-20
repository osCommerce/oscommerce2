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
  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/account_newsletters.php');

  $Qnewsletter = $OSCOM_Db->prepare('select customers_newsletter from :table_customers where customers_id = :customers_id');
  $Qnewsletter->bindInt(':customers_id', $_SESSION['customer_id']);
  $Qnewsletter->execute();

  if (isset($_POST['action']) && ($_POST['action'] == 'process') && isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken'])) {
    if (isset($_POST['newsletter_general']) && is_numeric($_POST['newsletter_general'])) {
      $newsletter_general = tep_db_prepare_input($_POST['newsletter_general']);
    } else {
      $newsletter_general = '0';
    }

    if ($newsletter_general != $Qnewsletter->value('customers_newsletter')) {
      $newsletter_general = (($Qnewsletter->value('customers_newsletter') == '1') ? '0' : '1');

      $OSCOM_Db->save('customers', ['customers_newsletter' => (int)$newsletter_general], ['customers_id' => $_SESSION['customer_id']]);
    }

    $messageStack->add_session('account', SUCCESS_NEWSLETTER_UPDATED, 'success');

    tep_redirect(tep_href_link('account.php', '', 'SSL'));
  }

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link('account.php', '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link('account_newsletters.php', '', 'SSL'));

  require('includes/template_top.php');
?>

<div class="page-header">
  <h1><?php echo HEADING_TITLE; ?></h1>
</div>

<?php echo tep_draw_form('account_newsletter', tep_href_link('account_newsletters.php', '', 'SSL'), 'post', 'class="form-horizontal" role="form"', true) . tep_draw_hidden_field('action', 'process'); ?>

<div class="contentContainer">

  <div class="contentText">
    <div class="form-group">
      <label class="control-label col-sm-4"><?php echo MY_NEWSLETTERS_GENERAL_NEWSLETTER; ?></label>
      <div class="col-sm-8">
        <div class="checkbox">
          <label>
            <?php echo tep_draw_checkbox_field('newsletter_general', '1', (($Qnewsletter->value('customers_newsletter') == '1') ? true : false)); ?>
            <?php if (tep_not_null(MY_NEWSLETTERS_GENERAL_NEWSLETTER_DESCRIPTION)) echo ' ' . MY_NEWSLETTERS_GENERAL_NEWSLETTER_DESCRIPTION; ?>
          </label>
        </div>
      </div>
    </div>
  </div>

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
