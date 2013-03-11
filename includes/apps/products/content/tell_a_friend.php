<?php
/**
 * osCommerce Online Merchant
 *
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo sprintf(HEADING_TITLE_TELL_A_FRIEND, $Qp->value('products_name')); ?></h1>

<?php
  if ( $OSCOM_MessageStack->exists('friend') ) {
    echo $OSCOM_MessageStack->get('friend');
  }
?>

<?php echo osc_draw_form('email_friend', osc_href_link('products', 'tell_a_friend&process&id=' . $_GET['id']), 'post', '', true); ?>

<div class="contentContainer">
  <div>
    <span class="inputRequirement" style="float: right;"><?php echo FORM_REQUIRED_INFORMATION; ?></span>
    <h2><?php echo FORM_TITLE_CUSTOMER_DETAILS; ?></h2>
  </div>

  <div class="contentText">
    <table border="0" cellspacing="2" cellpadding="2" width="100%">
      <tr>
        <td class="fieldKey"><?php echo FORM_FIELD_CUSTOMER_NAME; ?></td>
        <td class="fieldValue"><?php echo osc_output_string_protected($from_name); ?></td>
      </tr>
      <tr>
        <td class="fieldKey"><?php echo FORM_FIELD_CUSTOMER_EMAIL; ?></td>
        <td class="fieldValue"><?php echo osc_output_string_protected($from_email_address); ?></td>
      </tr>
    </table>
  </div>

  <h2><?php echo FORM_TITLE_FRIEND_DETAILS; ?></h2>

  <div class="contentText">
    <table border="0" cellspacing="2" cellpadding="2" width="100%">
      <tr>
        <td class="fieldKey"><?php echo FORM_FIELD_FRIEND_NAME; ?></td>
        <td class="fieldValue"><?php echo osc_draw_input_field('to_name') . '&nbsp;<span class="inputRequirement">' . ENTRY_FIRST_NAME_TEXT . '</span>'; ?></td>
      </tr>
      <tr>
        <td class="fieldKey"><?php echo FORM_FIELD_FRIEND_EMAIL; ?></td>
        <td class="fieldValue"><?php echo osc_draw_input_field('to_email_address') . '&nbsp;<span class="inputRequirement">' . ENTRY_EMAIL_ADDRESS_TEXT . '</span>'; ?></td>
      </tr>
    </table>
  </div>

  <h2><?php echo FORM_TITLE_FRIEND_MESSAGE; ?></h2>

  <div class="contentText">
    <table border="0" cellspacing="2" cellpadding="2" width="100%">
      <tr>
        <td class="fieldValue"><?php echo osc_draw_textarea_field('message', 'soft', 40, 8); ?></td>
      </tr>
    </table>
  </div>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo osc_draw_button(IMAGE_BUTTON_CONTINUE, 'ok-sign', null, 'success'); ?></span>

    <?php echo osc_draw_button(IMAGE_BUTTON_BACK, 'arrow-left', osc_href_link('products', 'id=' . $_GET['id'])); ?>
  </div>
</div>

</form>
