<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

use OSC\OM\OSCOM;
?>
<div class="col-sm-<?php echo $content_width; ?> upcoming-products">

  <table class="table table-striped table-condensed">
    <tbody>
      <tr>
        <th><?php echo MODULE_CONTENT_UPCOMING_PRODUCTS_TABLE_HEADING_PRODUCTS; ?></th>
        <th class="text-right"><?php echo MODULE_CONTENT_UPCOMING_PRODUCTS_TABLE_HEADING_DATE_EXPECTED; ?></th>
      </tr>
      <?php
      foreach ($products as $product) {
        echo '<tr>';
        echo '  <td><a href="' . OSCOM::link('product_info.php', 'products_id=' . (int)$product['products_id']) . '">' . $product['products_name'] . '</a></td>';
        echo '  <td class="text-right">' . tep_date_short($product['date_expected']) . '</td>';
        echo '</tr>';
      }
      ?>
    </tbody>
  </table>

</div>
