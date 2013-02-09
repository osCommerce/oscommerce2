<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  $category_query = tep_db_query("select cd.categories_name, c.categories_image from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$current_category_id . "' and cd.categories_id = '" . (int)$current_category_id . "' and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
  $category = tep_db_fetch_array($category_query);
?>

<h1><?php echo $category['categories_name']; ?></h1>

<div class="contentContainer">
  <div class="contentText">
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
<?php
  if (isset($cPath) && strpos('_', $cPath)) {
// check to see if there are deeper categories within the current category
    $category_links = array_reverse($cPath_array);
    for($i=0, $n=sizeof($category_links); $i<$n; $i++) {
      $categories_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
      $categories = tep_db_fetch_array($categories_query);
      if ($categories['total'] < 1) {
        // do nothing, go through the loop
      } else {
        $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by sort_order, cd.categories_name");
        break; // we've found the deepest category the customer is in
      }
    }
  } else {
    $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by sort_order, cd.categories_name");
  }

  $number_of_categories = tep_db_num_rows($categories_query);

  $rows = 0;
  while ($categories = tep_db_fetch_array($categories_query)) {
    $rows++;
    $cPath_new = tep_get_path($categories['categories_id']);
    $width = (int)(100 / MAX_DISPLAY_CATEGORIES_PER_ROW) . '%';
    echo '        <td align="center" class="smallText" width="' . $width . '" valign="top"><a href="' . tep_href_link(FILENAME_DEFAULT, $cPath_new) . '">' . tep_image(DIR_WS_IMAGES . $categories['categories_image'], $categories['categories_name'], SUBCATEGORY_IMAGE_WIDTH, SUBCATEGORY_IMAGE_HEIGHT) . '<br />' . $categories['categories_name'] . '</a></td>' . "\n";
    if ((($rows / MAX_DISPLAY_CATEGORIES_PER_ROW) == floor($rows / MAX_DISPLAY_CATEGORIES_PER_ROW)) && ($rows != $number_of_categories)) {
      echo '      </tr>' . "\n";
      echo '      <tr>' . "\n";
    }
  }

// needed for the new products module shown below
  $new_products_category_id = $current_category_id;
?>
      </tr>
    </table>

    <br />

<?php include(DIR_WS_MODULES . FILENAME_NEW_PRODUCTS); ?>

  </div>
</div>
