<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE_PASSWORD_FORGOTTEN; ?></h1>

<?php
  if ($messageStack->size('password_forgotten') > 0) {
    echo $messageStack->output('password_forgotten');
  }
?>

<?php echo tep_draw_form('password_forgotten', tep_href_link('account', 'password&forgotten&process', 'SSL'), 'post', '', true); ?>

<div class="contentContainer">
  <div class="contentText">
    <div><?php echo TEXT_MAIN_PASSWORD_FORGOTTEN; ?></div>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="fieldKey"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
        <td class="fieldValue"><?php echo tep_draw_input_field('email_address'); ?></td>
      </tr>
    </table>
  </div>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'triangle-1-e', null, 'primary'); ?></span>

    <?php echo tep_draw_button(IMAGE_BUTTON_BACK, 'triangle-1-w', tep_href_link('account', 'login', 'SSL')); ?>
  </div>
</div>

</form>
