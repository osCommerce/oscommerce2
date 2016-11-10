<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  if ($messageStack->size('product_action') > 0) {
    echo $messageStack->output('product_action');
  }
?>

  <div class="contentText">

<?php
  if ( ($Qlisting->getPageSetTotalRows() > 0) && ( (PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3') ) ) {
?>
<div class="row">
  <div class="col-sm-6 pagenumber hidden-xs">
    <?php echo $Qlisting->getPageSetLabel(OSCOM::getDef('text_display_number_of_products')); ?>
  </div>
  <div class="col-sm-6">
    <div class="pull-right pagenav"><?php echo $Qlisting->getPageSetLinks(tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></div>
    <span class="pull-right"><?php echo OSCOM::getDef('text_result_page'); ?></span>
  </div>
</div>
<?php
  }

  if ($Qlisting->getPageSetTotalRows() > 0) { ?>
    <div class="well well-sm">
      <div class="btn-group btn-group-sm pull-right">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
          <?php echo OSCOM::getDef('text_sort_by'); ?><span class="caret"></span>
        </button>

        <ul class="dropdown-menu text-left">
          <?php
          for ($col=0, $n=sizeof($column_list); $col<$n; $col++) {
            switch ($column_list[$col]) {
              case 'PRODUCT_LIST_MODEL':
              $lc_text = OSCOM::getDef('table_heading_model');
              break;
              case 'PRODUCT_LIST_NAME':
              $lc_text = OSCOM::getDef('table_heading_products');
              break;
              case 'PRODUCT_LIST_MANUFACTURER':
              $lc_text = OSCOM::getDef('table_heading_manufacturer');
              break;
              case 'PRODUCT_LIST_PRICE':
              $lc_text = OSCOM::getDef('table_heading_price');
              break;
              case 'PRODUCT_LIST_QUANTITY':
              $lc_text = OSCOM::getDef('table_heading_quantity');
              break;
              case 'PRODUCT_LIST_WEIGHT':
              $lc_text = OSCOM::getDef('table_heading_weight');
              break;
              case 'PRODUCT_LIST_IMAGE':
              $lc_text = OSCOM::getDef('table_heading_image');
              break;
              case 'PRODUCT_LIST_BUY_NOW':
              $lc_text = OSCOM::getDef('table_heading_buy_now');
              break;
              case 'PRODUCT_LIST_ID':
              $lc_text = OSCOM::getDef('table_heading_latest_added');
              break;
            }

            if ( ($column_list[$col] != 'PRODUCT_LIST_BUY_NOW') && ($column_list[$col] != 'PRODUCT_LIST_IMAGE') ) {
              $lc_text = tep_create_sort_heading($_GET['sort'], $col+1, $lc_text);
	            echo '        <li>' . $lc_text . '</li>';
            }
          }
		      ?>
        </ul>
      </div>

    <?php
    if ( (defined('MODULE_HEADER_TAGS_GRID_LIST_VIEW_STATUS') && MODULE_HEADER_TAGS_GRID_LIST_VIEW_STATUS == 'True') && (strpos(MODULE_HEADER_TAGS_GRID_LIST_VIEW_PAGES, basename($PHP_SELF)) !== false) ) {
      ?>
      <strong><?php echo OSCOM::getDef('text_view'); ?></strong>
      <div class="btn-group">
        <a href="#" id="list" class="btn btn-default btn-sm"><span class="fa fa-th-list"></span><?php echo OSCOM::getDef('text_view_list'); ?></a>
        <a href="#" id="grid" class="btn btn-default btn-sm"><span class="fa fa-th"></span><?php echo OSCOM::getDef('text_view_grid'); ?></a>
      </div>
      <?php
    }
    ?>
    <div class="clearfix"></div>
  </div>

  <?php
  $prod_list_contents = NULL;

  while ($Qlisting->fetch()) {
    $prod_list_contents .= '<div class="item list-group-item col-sm-4" itemprop="itemListElement" itemscope="" itemtype="http://schema.org/Product">';
	  $prod_list_contents .= '  <div class="productHolder equal-height">';
    if (isset($_GET['manufacturers_id'])  && tep_not_null($_GET['manufacturers_id'])) {
      $prod_list_contents .= '    <a href="' . OSCOM::link('product_info.php', 'manufacturers_id=' . $_GET['manufacturers_id'] . '&products_id=' . $Qlisting->valueInt('products_id')) . '">' . HTML::image(OSCOM::linkImage($Qlisting->value('products_image')), $Qlisting->value('products_name'), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'itemprop="image"', NULL, 'img-responsive thumbnail group list-group-image') . '</a>';
    } else {
      $prod_list_contents .= '    <a href="' . OSCOM::link('product_info.php', ($cPath ? 'cPath=' . $cPath . '&' : '') . 'products_id=' . $Qlisting->valueInt('products_id')) . '">' . HTML::image(OSCOM::linkImage($Qlisting->value('products_image')), $Qlisting->value('products_name'), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'itemprop="image"', NULL, 'img-responsive thumbnail group list-group-image') . '</a>';
    }
    $prod_list_contents .= '    <div class="caption">';
    $prod_list_contents .= '      <h2 class="group inner list-group-item-heading">';
    if (isset($_GET['manufacturers_id']) && tep_not_null($_GET['manufacturers_id'])) {
      $prod_list_contents .= '    <a itemprop="url" href="' . OSCOM::link('product_info.php', 'manufacturers_id=' . $_GET['manufacturers_id'] . '&products_id=' . $Qlisting->valueInt('products_id')) . '"><span itemprop="name">' . $Qlisting->value('products_name') . '</span></a>';
    } else {
      $prod_list_contents .= '    <a itemprop="url" href="' . OSCOM::link('product_info.php', ($cPath ? 'cPath=' . $cPath . '&' : '') . 'products_id=' . $Qlisting->valueInt('products_id')) . '"><span itemprop="name">' . $Qlisting->value('products_name') . '</span></a>';
    }
    $prod_list_contents .= '      </h2>';

    $prod_list_contents .= '      <p class="group inner list-group-item-text" itemprop="description">' . strip_tags($Qlisting->value('products_description'), '<br>') . '&hellip;</p><div class="clearfix"></div>';

    $extra_list_contents = NULL;
	  if ( (PRODUCT_LIST_MANUFACTURER > 0) && tep_not_null($Qlisting->valueInt('manufacturers_id')) ) {
      $extra_list_contents .= '<dt>' . OSCOM::getDef('table_heading_manufacturer') . '</dt>';
      $extra_list_contents .= '<dd><a href="' . OSCOM::link('index.php', 'manufacturers_id=' . $Qlisting->valueInt('manufacturers_id')) . '">' . $Qlisting->value('manufacturers_name') . '</a></dd>';
    }
	  if ( (PRODUCT_LIST_MODEL > 0) && tep_not_null($Qlisting->value('products_model')) ) {
      $extra_list_contents .= '<dt>' . OSCOM::getDef('table_heading_model') . '</dt>';
      $extra_list_contents .= '<dd>' . $Qlisting->value('products_model') . '</dd>';
    }
	  if ( (PRODUCT_LIST_QUANTITY > 0) && (tep_get_products_stock($Qlisting->valueInt('products_id')) > 0) ) {
      $extra_list_contents .= '<dt>' . OSCOM::getDef('table_heading_quantity') . '</dt>';
      $extra_list_contents .= '<dd>' . tep_get_products_stock($Qlisting->valueInt('products_id')) . '</dd>';
    }
	  if (PRODUCT_LIST_WEIGHT > 0) {
      $extra_list_contents .= '<dt>' . OSCOM::getDef('table_heading_weight') . '</dt>';
      $extra_list_contents .= '<dd>' . $Qlisting->value('products_weight') . '</dd>';
    }

    if (tep_not_null($extra_list_contents)) {
       $prod_list_contents .= '    <dl class="dl-horizontal list-group-item-text">';
       $prod_list_contents .=  $extra_list_contents;
       $prod_list_contents .= '    </dl>';
    }

	  if ( (PRODUCT_LIST_PRICE > 0) || (PRODUCT_LIST_BUY_NOW > 0) ) {
      $prod_list_contents .= '      <div class="row">';

      if (PRODUCT_LIST_PRICE > 0) {
        if (tep_not_null($Qlisting->valueDecimal('specials_new_products_price'))) {
          $prod_list_contents .= '      <div class="col-xs-6" itemprop="offers" itemscope itemtype="http://schema.org/Offer"><meta itemprop="priceCurrency" content="' . HTML::output($_SESSION['currency']) . '" /><div class="btn-group" role="group"><button type="button" class="btn btn-default"><del>' .  $currencies->display_price($Qlisting->valueDecimal('products_price'), tep_get_tax_rate($Qlisting->valueInt('products_tax_class_id'))) . '</del></span>&nbsp;&nbsp;<span class="productSpecialPrice" itemprop="price" content="' . $currencies->display_raw($Qlisting->valueDecimal('specials_new_products_price'), tep_get_tax_rate($Qlisting->valueInt('products_tax_class_id'))) . '">' . $currencies->display_price($Qlisting->valueDecimal('specials_new_products_price'), tep_get_tax_rate($Qlisting->valueInt('products_tax_class_id'))) . '</span></button></div></div>';
        } else {
          $prod_list_contents .= '      <div class="col-xs-6" itemprop="offers" itemscope itemtype="http://schema.org/Offer"><meta itemprop="priceCurrency" content="' . HTML::output($_SESSION['currency']) . '" /><div class="btn-group" role="group"><button type="button" class="btn btn-default"><span itemprop="price" content="' . $currencies->display_raw($Qlisting->valueDecimal('products_price'), tep_get_tax_rate($Qlisting->valueInt('products_tax_class_id'))) . '">' . $currencies->display_price($Qlisting->valueDecimal('products_price'), tep_get_tax_rate($Qlisting->valueInt('products_tax_class_id'))) . '</span></button></div></div>';
        }
      }

      if (PRODUCT_LIST_BUY_NOW > 0) {
        $prod_list_contents .= '       <div class="col-xs-6 text-right">' . HTML::button(OSCOM::getDef('image_button_buy_now'), 'fa fa-shopping-cart', OSCOM::link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'sort', 'cPath')) . 'action=buy_now&products_id=' . $Qlisting->valueInt('products_id')), null, 'btn-success btn-sm') . '</div>';
      }
      $prod_list_contents .= '      </div>';
    }

    $prod_list_contents .= '    </div>';
    $prod_list_contents .= '  </div>';
    $prod_list_contents .= '</div>';

  }

  echo '<div id="products" class="row list-group" itemtype="http://schema.org/ItemList">';
  echo '  <meta itemprop="numberOfItems" content="' . (int)$Qlisting->getPageSetTotalRows() . '" />';
  echo $prod_list_contents;
  echo '</div>';
} else {
?>

  <div class="alert alert-info"><?php echo OSCOM::getDef('text_no_products'); ?></div>

<?php
}

if ( ($Qlisting->getPageSetTotalRows() > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3')) ) {
  ?>
<div class="row">
  <div class="col-sm-6 pagenumber hidden-xs">
    <?php echo $Qlisting->getPageSetLabel(OSCOM::getDef('text_display_number_of_products')); ?>
  </div>
  <div class="col-sm-6">
    <div class="pull-right pagenav"><?php echo $Qlisting->getPageSetLinks(tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></div>
    <span class="pull-right"><?php echo OSCOM::getDef('text_result_page'); ?></span>
  </div>
</div>
  <?php
  }
?>

</div>
