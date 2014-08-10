<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $expected_query = tep_db_query("select p.products_id, pd.products_name, products_date_available as date_expected from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where to_days(products_date_available) >= to_days(now()) and p.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by " . EXPECTED_PRODUCTS_FIELD . " " . EXPECTED_PRODUCTS_SORT . " limit " . MAX_DISPLAY_UPCOMING_PRODUCTS);
  if (tep_db_num_rows($expected_query) > 0) {
?>
  <div class="clearfix"></div>
  <div class="table-responsive">
    <table class="table table-condensed">
      <thead>
        <tr>
          <th><?php echo TABLE_HEADING_UPCOMING_PRODUCTS; ?></th>
          <th class="text-right"><?php echo TABLE_HEADING_DATE_EXPECTED; ?></th>
        </tr>
      </thead>
      <tbody>
        <?php
        while ($expected = tep_db_fetch_array($expected_query)) {
          echo '        <tr>' . "\n" .
               '          <td><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $expected['products_id']) . '">' . $expected['products_name'] . '</a></td>' . "\n" .
               '          <td class="text-right">' . tep_date_short($expected['date_expected']) . '</td>' . "\n" .
               '        </tr>' . "\n";
        }
        ?>
      </tbody>
    </table>
  </div>

<?php
  }
?>
