<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  if ( (!isset($new_products_category_id)) || ($new_products_category_id == '0') ) {
    $Qnew = $OSCOM_Db->prepare('select p.products_id, p.products_image, p.products_tax_class_id, pd.products_name, if(s.status, s.specials_new_products_price, p.products_price) as products_price from :table_products p left join :table_specials s on p.products_id = s.products_id, products_description pd where p.products_status = "1" and p.products_id = pd.products_id and pd.language_id = :language_id order by p.products_date_added desc, pd.products_name limit :limit');
    $Qnew->bindInt(':language_id', $OSCOM_Language->getId());
    $Qnew->bindInt(':limit', MAX_DISPLAY_NEW_PRODUCTS);
    $Qnew->execute();
  } else {
    $Qnew = $OSCOM_Db->prepare('select distinct p.products_id, p.products_image, p.products_tax_class_id, pd.products_name, if(s.status, s.specials_new_products_price, p.products_price) as products_price from :table_products p left join :table_specials s on p.products_id = s.products_id, :table_products_description pd, :table_products_to_categories p2c, :table_categories c where p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and c.parent_id = :parent_id and p.products_status = "1" and p.products_id = pd.products_id and pd.language_id = :language_id order by p.products_date_added desc, pd.products_name limit :limit');
    $Qnew->bindInt(':parent_id', $new_products_category_id);
    $Qnew->bindInt(':language_id', $OSCOM_Language->getId());
    $Qnew->bindInt(':limit', MAX_DISPLAY_NEW_PRODUCTS);
    $Qnew->execute();
  }

  if ($Qnew->fetch() !== false) {
    echo '<h3>' . sprintf(TABLE_HEADING_NEW_PRODUCTS, strftime('%B')) . '</h3>';

    echo '<div class="row" itemtype="http://schema.org/ItemList">';
    echo '  <meta itemprop="numberOfItems" content="' . count($Qnew->fetchAll()) . '" />';
    do {
      include('includes/modules/templates/new_products.php');
    } while ($Qnew->fetch());
    echo '</div>';
  }
?>
