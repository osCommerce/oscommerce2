<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/


  $expected_query = tep_db_query("select p.products_id, pd.products_name, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, p.products_price) as products_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$current_category_id . "' and p.products_id != '" . (int)$HTTP_GET_VARS['products_id'] . "' order by pd.products_name DESC LIMIT " . MAX_DISPLAY_SEARCH_RESULTS);

  if (tep_db_num_rows($expected_query) > 0) {
?>
  <div class="clear"></div>
  <br>
  <div class="ui-widget infoBoxContainer">
    <div class="ui-widget-header ui-corner-top infoBoxHeading">
      <span><?php echo TABLE_HEADING_LIKE_PRODUCTS; ?></span>
    </div>

    <div class="ui-widget-content ui-corner-bottom">
      <table border="0" width="100%" cellspacing="0" cellpadding="2" class="productListTable">
<?php
    while ($expected = tep_db_fetch_array($expected_query)) {
      echo '        <tr>' . "\n" .
           '          <td><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $expected['products_id']) . '">' . $expected['products_name'] . '</a></td>' . "\n" .
           '          <td align="right">' . $currencies->format($expected['products_price'],tep_get_tax_rate($product_info['products_tax_class_id'])) . '</td>' . "\n" .
           '        </tr>' . "\n";
    }
?>

      </table>
    </div>
  </div>

<?php
  }
?>
