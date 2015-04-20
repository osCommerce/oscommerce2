<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  $Qupcoming = $OSCOM_Db->prepare('select p.products_id, pd.products_name, products_date_available as date_expected from :table_products p, :table_products_description pd where to_days(p.products_date_available) >= to_days(now()) and p.products_id = pd.products_id and pd.language_id = :language_id order by ' . EXPECTED_PRODUCTS_FIELD . ' ' . EXPECTED_PRODUCTS_SORT . ' limit :limit');
  $Qupcoming->bindInt(':language_id', $_SESSION['languages_id']);
  $Qupcoming->bindInt(':limit', MAX_DISPLAY_UPCOMING_PRODUCTS);
  $Qupcoming->execute();

  if ($Qupcoming->fetch() !== false) {
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
  do {
    echo '        <tr>' . "\n" .
         '          <td><a href="' . tep_href_link('product_info.php', 'products_id=' . $Qupcoming->valueInt('products_id')) . '">' . $Qupcoming->value('products_name') . '</a></td>' . "\n" .
         '          <td class="text-right">' . tep_date_short($Qupcoming->value('date_expected')) . '</td>' . "\n" .
         '        </tr>' . "\n";
  } while ($Qupcoming->fetch());
?>
      </tbody>
    </table>
  </div>

<?php
  }
?>
