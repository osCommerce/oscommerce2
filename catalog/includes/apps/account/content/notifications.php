<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<h1><?php echo HEADING_TITLE_NOTIFICATIONS; ?></h1>

<?php echo tep_draw_form('account_notifications', tep_href_link('account', 'notifications&process', 'SSL'), 'post', '', true); ?>

<div class="contentContainer">
  <h2><?php echo MY_NOTIFICATIONS_TITLE; ?></h2>

  <div class="contentText">
    <?php echo MY_NOTIFICATIONS_DESCRIPTION; ?>
  </div>

  <h2><?php echo GLOBAL_NOTIFICATIONS_TITLE; ?></h2>

  <div class="contentText">
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="30"><?php echo tep_draw_checkbox_field('product_global', '1', (($global['global_product_notifications'] == '1') ? true : false), 'onclick="checkBox(\'product_global\')"'); ?></td>
        <td><strong><?php echo GLOBAL_NOTIFICATIONS_TITLE; ?></strong><br /><?php echo GLOBAL_NOTIFICATIONS_DESCRIPTION; ?></td>
      </tr>
    </table>
  </div>

<?php
  if ($global['global_product_notifications'] != '1') {
?>

  <h2><?php echo NOTIFICATIONS_TITLE; ?></h2>

  <div class="contentText">

<?php
    $products_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_NOTIFICATIONS . " where customers_id = '" . (int)$_SESSION['customer_id'] . "'");
    $products_check = tep_db_fetch_array($products_check_query);
    if ($products_check['total'] > 0) {
?>

    <div><?php echo NOTIFICATIONS_DESCRIPTION; ?></div>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">

<?php
      $counter = 0;
      $products_query = tep_db_query("select pd.products_id, pd.products_name from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_NOTIFICATIONS . " pn where pn.customers_id = '" . (int)$_SESSION['customer_id'] . "' and pn.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by pd.products_name");
      while ($products = tep_db_fetch_array($products_query)) {
?>

      <tr>
        <td width="30"><?php echo tep_draw_checkbox_field('products[' . $counter . ']', $products['products_id'], true, 'onclick="checkBox(\'products[' . $counter . ']\')"'); ?></td>
        <td><strong><?php echo $products['products_name']; ?></strong></td>
      </tr>

<?php
        $counter++;
      }
?>

    </table>

<?php
    } else {
?>

    <div>
      <?php echo NOTIFICATIONS_NON_EXISTING; ?>
    </div>

<?php
    }
?>

  </div>

<?php
  }
?>

  <div class="buttonSet">
    <span class="buttonAction"><?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'triangle-1-e', null, 'primary'); ?></span>

    <?php echo tep_draw_button(IMAGE_BUTTON_BACK, 'triangle-1-w', tep_href_link('account', '', 'SSL')); ?>
  </div>
</div>

</form>
