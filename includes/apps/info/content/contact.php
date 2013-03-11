<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE_CONTACT; ?></h1>

<?php
  if ($messageStack->size('contact') > 0) {
    echo $messageStack->output('contact');
  }
?>

<?php echo osc_draw_form('contact_us', osc_href_link('info', 'contact&process'), 'post', '', true); ?>

<div class="contentContainer">
  <div class="contentText">
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="fieldKey"><?php echo ENTRY_NAME; ?></td>
        <td class="fieldValue"><?php echo osc_draw_input_field('name'); ?></td>
      </tr>
      <tr>
        <td class="fieldKey"><?php echo ENTRY_EMAIL; ?></td>
        <td class="fieldValue"><?php echo osc_draw_input_field('email'); ?></td>
      </tr>
      <tr>
        <td class="fieldKey" valign="top"><?php echo ENTRY_ENQUIRY; ?></td>
        <td class="fieldValue"><?php echo osc_draw_textarea_field('enquiry', 'soft', 50, 15); ?></td>
      </tr>
    </table>
  </div>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo osc_draw_button(IMAGE_BUTTON_CONTINUE, 'ok-sign', null, 'success'); ?></span>
  </div>
</div>

</form>
