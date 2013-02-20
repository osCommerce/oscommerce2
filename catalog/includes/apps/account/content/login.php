<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE_LOGIN; ?></h1>

<?php
  if ($messageStack->size('login') > 0) {
    echo $messageStack->output('login');
  }
?>

<div class="contentContainer" style="width: 45%; float: left;">
  <h2><?php echo HEADING_NEW_CUSTOMER; ?></h2>

  <div class="contentText">
    <p><?php echo TEXT_NEW_CUSTOMER; ?></p>
    <p><?php echo TEXT_NEW_CUSTOMER_INTRODUCTION; ?></p>

    <p align="right"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'triangle-1-e', tep_href_link('account', 'create', 'SSL')); ?></p>
  </div>
</div>

<div class="contentContainer" style="width: 45%; float: left; border-left: 1px dashed #ccc; padding-left: 3%; margin-left: 3%;">
  <h2><?php echo HEADING_RETURNING_CUSTOMER; ?></h2>

  <div class="contentText">
    <p><?php echo TEXT_RETURNING_CUSTOMER; ?></p>

    <?php echo tep_draw_form('login', tep_href_link('account', 'login&process', 'SSL'), 'post', '', true); ?>

    <table border="0" cellspacing="0" cellpadding="2" width="100%">
      <tr>
        <td class="fieldKey"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
        <td class="fieldValue"><?php echo tep_draw_input_field('email_address'); ?></td>
      </tr>
      <tr>
        <td class="fieldKey"><?php echo ENTRY_PASSWORD; ?></td>
        <td class="fieldValue"><?php echo tep_draw_password_field('password'); ?></td>
      </tr>
    </table>

    <p><?php echo '<a href="' . tep_href_link('account', 'password&forgotten', 'SSL') . '">' . TEXT_PASSWORD_FORGOTTEN . '</a>'; ?></p>

    <p align="right"><?php echo tep_draw_button(IMAGE_BUTTON_LOGIN, 'key', null, 'primary'); ?></p>

    </form>
  </div>
</div>
