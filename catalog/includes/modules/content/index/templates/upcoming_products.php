<?php
use OSC\OM\DateTime;
use OSC\OM\OSCOM;
?>
<div class="col-sm-<?php echo $content_width; ?> upcoming-products">

  <table class="table table-striped table-condensed">
    <tbody>
      <tr>
        <th><?php echo OSCOM::getDef('module_content_upcoming_products_table_heading_products'); ?></th>
        <th class="text-right"><?php echo OSCOM::getDef('module_content_upcoming_products_table_heading_date_expected'); ?></th>
      </tr>
      <?php
      foreach ($products as $product) {
        echo '<tr>';
        echo '  <td><a href="' . OSCOM::link('product_info.php', 'products_id=' . (int)$product['products_id']) . '">' . $product['products_name'] . '</a></td>';
        echo '  <td class="text-right">' . DateTime::toShort($product['date_expected']) . '</td>';
        echo '</tr>';
      }
      ?>
    </tbody>
  </table>

</div>
