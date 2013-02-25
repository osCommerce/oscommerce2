<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE_NEWSLETTERS; ?></h1>

<?php echo osc_draw_form('account_newsletter', osc_href_link('account', 'newsletters&process', 'SSL'), 'post', '', true); ?>

<div class="contentContainer">
  <h2><?php echo MY_NEWSLETTERS_TITLE; ?></h2>

  <div class="contentText">
    <table border="0" cellspacing="2" cellpadding="2">
      <tr>
        <td><?php echo osc_draw_checkbox_field('newsletter_general', '1', (($newsletter['customers_newsletter'] == '1') ? true : false), 'onclick="checkBox(\'newsletter_general\')"'); ?></td>
        <td><strong><?php echo MY_NEWSLETTERS_GENERAL_NEWSLETTER; ?></strong><br /><?php echo MY_NEWSLETTERS_GENERAL_NEWSLETTER_DESCRIPTION; ?></td>
      </tr>
    </table>
  </div>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo osc_draw_button(IMAGE_BUTTON_CONTINUE, 'triangle-1-e', null, 'primary'); ?></span>

    <?php echo osc_draw_button(IMAGE_BUTTON_BACK, 'triangle-1-w', osc_href_link('account', '', 'SSL')); ?>
  </div>
</div>

</form>
