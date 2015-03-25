<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $listing_split = new splitPageResults($listing_sql, MAX_DISPLAY_SEARCH_RESULTS, 'p.products_id');
?>

<?php
  if ($messageStack->size('product_action') > 0) {
    echo $messageStack->output('product_action');
  }
?>

  <div class="contentText">

<?php
  if ( ($listing_split->number_of_rows > 0) && ( (PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3') ) ) {
?>
<div class="row">
  <div class="col-sm-6 pagenumber hidden-xs">
    <?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?>
  </div>
  <div class="col-sm-6">
    <div class="pull-right pagenav"><?php echo $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></div>
    <span class="pull-right"><?php echo TEXT_RESULT_PAGE; ?></span>
  </div>
</div>
<?php
  }

  if ($listing_split->number_of_rows > 0) { ?>
    <div class="well well-sm">
      <div class="btn-group btn-group-sm pull-right">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><?php echo TEXT_SORT_BY; ?><span class="caret"></span></button>

        <ul class="dropdown-menu text-left">
          <?php
          $lc_show_model = false;
          $lc_show_manu = false;
          $lc_show_qty = false;
          $lc_show_lbs = false;
          for ($col=0, $n=sizeof($column_list); $col<$n; $col++) {
            switch ($column_list[$col]) {
              case 'PRODUCT_LIST_MODEL':
              $lc_text = TABLE_HEADING_MODEL;
		          $lc_show_model = true;
              break;
              case 'PRODUCT_LIST_NAME':
              $lc_text = TABLE_HEADING_PRODUCTS;
              break;
              case 'PRODUCT_LIST_MANUFACTURER':
              $lc_text = TABLE_HEADING_MANUFACTURER;
		          $lc_show_manu = true;
              break;
              case 'PRODUCT_LIST_PRICE':
              $lc_text = TABLE_HEADING_PRICE;
              break;
              case 'PRODUCT_LIST_QUANTITY':
              $lc_text = TABLE_HEADING_QUANTITY;
              $lc_show_qty = true;
              break;
              case 'PRODUCT_LIST_WEIGHT':
              $lc_text = TABLE_HEADING_WEIGHT;
              $lc_show_lbs = true;
              break;
              case 'PRODUCT_LIST_IMAGE':
              $lc_text = TABLE_HEADING_IMAGE;
              break;
              case 'PRODUCT_LIST_BUY_NOW':
              $lc_text = TABLE_HEADING_BUY_NOW;
              break;
              case 'PRODUCT_LIST_ID':
              $lc_text = TABLE_HEADING_LATEST_ADDED;
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
    if ( (defined('MODULE_HEADER_TAGS_GRID_LIST_VIEW_STATUS')) && (MODULE_HEADER_TAGS_GRID_LIST_VIEW_STATUS == 'True') ) {
      ?>
      <strong><?php echo TEXT_VIEW; ?></strong>
      <div class="btn-group">
        <a href="#" id="list" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-th-list"></span><?php echo TEXT_VIEW_LIST; ?></a>
        <a href="#" id="grid" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-th"></span><?php echo TEXT_VIEW_GRID; ?></a>
      </div>
      <?php
    }
    ?>
    <div class="clearfix"></div>
  </div>

  <?php
  $listing_query = tep_db_query($listing_split->sql_query);

  $prod_list_contents = NULL;

  while ($listing = tep_db_fetch_array($listing_query)) {
    $prod_list_contents .= '<div class="item list-group-item col-sm-4">';
	  $prod_list_contents .= '  <div class="productHolder equal-height">';
    if (isset($_GET['manufacturers_id'])  && tep_not_null($_GET['manufacturers_id'])) {
      $prod_list_contents .= '    <a href="' . tep_href_link('product_info.php', 'manufacturers_id=' . $_GET['manufacturers_id'] . '&products_id=' . (int)$listing['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $listing['products_image'], $listing['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, NULL, NULL, 'img-responsive thumbnail group list-group-image') . '</a>';
    } else {
      $prod_list_contents .= '    <a href="' . tep_href_link('product_info.php', ($cPath ? 'cPath=' . $cPath . '&' : '') . 'products_id=' . (int)$listing['products_id']) . '">' . tep_image(DIR_WS_IMAGES . $listing['products_image'], $listing['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, NULL, NULL, 'img-responsive thumbnail group list-group-image') . '</a>';
    }
    $prod_list_contents .= '    <div class="caption">';
    $prod_list_contents .= '      <h2 class="group inner list-group-item-heading">';
    if (isset($_GET['manufacturers_id']) && tep_not_null($_GET['manufacturers_id'])) {
      $prod_list_contents .= '    <a href="' . tep_href_link('product_info.php', 'manufacturers_id=' . $_GET['manufacturers_id'] . '&products_id=' . (int)$listing['products_id']) . '">' . $listing['products_name'] . '</a>';
    } else {
      $prod_list_contents .= '    <a href="' . tep_href_link('product_info.php', ($cPath ? 'cPath=' . $cPath . '&' : '') . 'products_id=' . (int)$listing['products_id']) . '">' . $listing['products_name'] . '</a>';
    }
    $prod_list_contents .= '      </h2>';

    $prod_list_contents .= '      <p class="group inner list-group-item-text">' . strip_tags($listing['products_description'], '<br>') . '&hellip;</p><div class="clearfix"></div>';

    $extra_list_contents = NULL;
	  if (($lc_show_manu == true) && ($listing['manufacturers_id'] !=  0))                  $extra_list_contents .= '<dt>' . TABLE_HEADING_MANUFACTURER . '</dt><dd><a href="' . tep_href_link('index.php', 'manufacturers_id=' . (int)$listing['manufacturers_id']) . '">' . $listing['manufacturers_name'] . '</a></dd>';
	  if ( ($lc_show_model == true) && tep_not_null($listing['products_model']))            $extra_list_contents .= '<dt>' . TABLE_HEADING_MODEL . '</dt><dd>' . $listing['products_model'] . '</dd>';
	  if (($lc_show_qty == true) && (tep_get_products_stock($listing['products_id'])!= 0) ) $extra_list_contents .= '<dt>' . TABLE_HEADING_QUANTITY . '</dt><dd>' . tep_get_products_stock($listing['products_id']) . '</dd>';
	  if (($lc_show_lbs == true) && ($listing['products_weight'] != 0))                     $extra_list_contents .= '<dt>' . TABLE_HEADING_WEIGHT . '</dt><dd>' . $listing['products_weight'] . '</dd>';

    if (tep_not_null($extra_list_contents)) {
       $prod_list_contents .= '    <dl class="dl-horizontal list-group-item-text">';
       $prod_list_contents .=  $extra_list_contents;
       $prod_list_contents .= '    </dl>';
    }

	  $prod_list_contents .= '      <div class="row">';
    if (tep_not_null($listing['specials_new_products_price'])) {
      $prod_list_contents .= '      <div class="col-xs-6"><div class="btn-group" role="group"><button type="button" class="btn btn-default"><del>' .  $currencies->display_price($listing['products_price'], tep_get_tax_rate($listing['products_tax_class_id'])) . '</del></span>&nbsp;&nbsp;<span class="productSpecialPrice">' . $currencies->display_price($listing['specials_new_products_price'], tep_get_tax_rate($listing['products_tax_class_id'])) . '</button></div></div>';
    } else {
      $prod_list_contents .= '      <div class="col-xs-6"><div class="btn-group" role="group"><button type="button" class="btn btn-default">' . $currencies->display_price($listing['products_price'], tep_get_tax_rate($listing['products_tax_class_id'])) . '</button></div></div>';
    }
    $prod_list_contents .= '       <div class="col-xs-6 text-right">' . tep_draw_button(IMAGE_BUTTON_BUY_NOW, 'glyphicon glyphicon-shopping-cart', tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'sort', 'cPath')) . 'action=buy_now&products_id=' . (int)$listing['products_id']), NULL, NULL, 'btn-success btn-sm') . '</div>';
    $prod_list_contents .= '      </div>';

    $prod_list_contents .= '    </div>';
    $prod_list_contents .= '  </div>';
    $prod_list_contents .= '</div>';

  }

  echo '<div id="products" class="row list-group">' . $prod_list_contents . '</div>';

} else {
?>

  <div class="alert alert-info"><?php echo TEXT_NO_PRODUCTS; ?></div>

<?php
}

if ( ($listing_split->number_of_rows > 0) && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3')) ) {
  ?>
<div class="row">
  <div class="col-sm-6 pagenumber hidden-xs">
    <?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?>
  </div>
  <div class="col-sm-6">
    <div class="pull-right pagenav"><?php echo $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></div>
    <span class="pull-right"><?php echo TEXT_RESULT_PAGE; ?></span>
  </div>
</div>
  <?php
  }
?>

</div>
