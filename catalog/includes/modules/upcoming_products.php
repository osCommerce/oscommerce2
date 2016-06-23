<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;

  $Qupcoming = $OSCOM_Db->prepare('select p.products_id, pd.products_name, products_date_available as date_expected from :table_products p, :table_products_description pd where to_days(p.products_date_available) >= to_days(now()) and p.products_id = pd.products_id and pd.language_id = :language_id order by ' . EXPECTED_PRODUCTS_FIELD . ' ' . EXPECTED_PRODUCTS_SORT . ' limit :limit');
  $Qupcoming->bindInt(':language_id', $_SESSION['languages_id']);
  $Qupcoming->bindInt(':limit', MAX_DISPLAY_UPCOMING_PRODUCTS);
  $Qupcoming->execute();

  if ($Qupcoming->fetch() !== false) {
?>

  <div class="panel panel-info">
    <div class="panel-heading">
      <div class="pull-left">
        <?php echo TABLE_HEADING_UPCOMING_PRODUCTS; ?>
      </div>
      <div class="pull-right">
        <?php echo TABLE_HEADING_DATE_EXPECTED; ?>
      </div>
      <div class="clearfix"></div>
    </div>

    <div class="panel-body">
<?php
  do {
    echo '<div class="pull-left"><a href="' . OSCOM::link('product_info.php', 'products_id=' . $Qupcoming->valueInt('products_id')) . '">' . $Qupcoming->value('products_name') . '</a></div>' . "\n" .
         '<div class="pull-right">' . tep_date_short($Qupcoming->value('date_expected')) . '</div>' . "\n" .
         '<div class="clearfix"></div>' . "\n";
  } while ($Qupcoming->fetch());
?>

    </div>
  </div>

<?php
  }
?>
