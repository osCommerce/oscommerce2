<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  if (!isset($_GET['products_id'])) {
    tep_redirect(tep_href_link('index.php'));
  }

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/product_info.php');

  $product_info = $OSCOM_Db->prepare('select p.products_id, pd.products_name, pd.products_description, p.products_model, p.products_quantity, p.products_image, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id from :table_products p, :table_products_description pd where p.products_status = 1 and p.products_id = :products_id and pd.products_id = p.products_id and pd.language_id = :languages_id');
  $product_info->bindInt(':products_id', $_GET['products_id']);
  $product_info->bindInt(':languages_id', $_SESSION['languages_id']);
  $product_info->execute();

  if ( $product_info->value('products_model') ) {
    // add the products model to the breadcrumb trail
    $breadcrumb->add($product_info->value('products_model'), tep_href_link('product_info.php', 'cPath=' . $cPath . '&products_id=' . $product_info->value('products_id')));
  }

  if ( $product_info->rowCount() == 0 ) {
    header('HTTP/1.0 404 Not Found');
  }

  require('includes/template_top.php');

  if ( $product_info->rowCount() == 0 ) {
?>

<div class="contentContainer">
  <div class="contentText">
    <div class="alert alert-warning">
      <?php echo TEXT_PRODUCT_NOT_FOUND; ?>
    </div>
  </div>

  <div class="text-right">
    <?php echo tep_draw_button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', tep_href_link('index.php'), null, null, 'btn-default btn-block'); ?>
  </div>
</div>

<?php
  } else {

    $OSCOM_Db->save(':table_products_description', array('products_viewed' => 'products_viewed'+1), array('products_id' => (int)$_GET['products_id'], 'language_id' => (int)$_SESSION['languages_id']));

    if ($new_price = tep_get_products_special_price($product_info->value('products_id'))) {
      $products_price = '<del>' . $currencies->display_price($product_info->value('products_price'), tep_get_tax_rate($product_info->value('products_tax_class_id'))) . '</del> <span class="productSpecialPrice" itemprop="price">' . $currencies->display_price($new_price, tep_get_tax_rate($product_info->value('products_tax_class_id'))) . '</span>';
    } else {
      $products_price = '<span itemprop="price">' . $currencies->display_price($product_info->value('products_price'), tep_get_tax_rate($product_info->value('products_tax_class_id'))) . '</span>';
    }
    
    if ($product_info->value('products_date_available') > date('Y-m-d H:i:s')) {
      $products_price .= '<link itemprop="availability" href="http://schema.org/PreOrder" />';
    } elseif ((STOCK_CHECK == 'true') && ($product_info->value('products_quantity') < 1)) {
      $products_price .= '<link itemprop="availability" href="http://schema.org/OutOfStock" />';
    } else {
      $products_price .= '<link itemprop="availability" href="http://schema.org/InStock" />';
    }

    $products_price .= '<meta itemprop="priceCurrency" content="' . tep_output_string($_SESSION['currency']) . '" />';

    $products_name = '<a href="' . tep_href_link('product_info.php', 'products_id=' . $product_info->value('products_id')) . '" itemprop="url"><span itemprop="name">' . $product_info->value('products_name') . '</span></a>';

    if ( $product_info->value('products_model') ) {
      $products_name .= ' <small>[<span itemprop="model">' . $product_info->value('products_model') . '</span>]</small>';
    }
?>

<div itemscope itemtype="http://schema.org/Product">

<div class="page-header">
  <div class="row">
    <h1 class="col-sm-8"><?php echo $products_name; ?></h1>
    <h1 class="col-sm-4 text-right-not-xs" itemprop="offers" itemscope itemtype="http://schema.org/Offer"><?php echo $products_price; ?></h1>
  </div>
</div>

<div class="contentContainer">
  <div class="contentText">

<?php
    if ( $product_info->value('products_image') ) {

      echo '    ' . tep_image(DIR_WS_IMAGES . $product_info->value('products_image'), NULL, NULL, NULL, 'itemprop="image" style="display:none;"');

      $photoset_layout = '1';

      $Qpi = $OSCOM_Db->prepare('select image, htmlcontent from :table_products_images where products_id = :products_id order by sort_order');
      $Qpi->bindInt(':products_id', $product_info->value('products_id'));
      $Qpi->execute();

      $pi_total = $Qpi->rowCount();

      if ($pi_total > 0) {
        $pi_sub = $pi_total-1;

        while ($pi_sub > 5) {
          $photoset_layout .= 5;
          $pi_sub = $pi_sub-5;
        }

        if ($pi_sub > 0) {
          $photoset_layout .= ($pi_total > 5) ? 5 : $pi_sub;
        }
?>

    <div class="piGal pull-right" data-imgcount="<?php echo $photoset_layout; ?>">

<?php
        $pi_counter = 0;
        $pi_html = array();

        while ( $pi = $Qpi->fetch() ) {
          $pi_counter++;

          if (tep_not_null($pi['htmlcontent'])) {
            $pi_html[] = '<div id="piGalDiv_' . $pi_counter . '">' . $pi['htmlcontent'] . '</div>';
          }

          echo '      ' . tep_image(DIR_WS_IMAGES . $pi['image'], '', '', '', 'id="piGalImg_' . $pi_counter . '"') . "\n";
        }
?>

    </div>

<?php
        if ( !empty($pi_html) ) {
          echo '    <div style="display: none;">' . implode('', $pi_html) . '</div>';
        }
      } else {
?>

    <div class="piGal pull-right">
      <?php echo tep_image(DIR_WS_IMAGES . $product_info->value('products_image'), addslashes($product_info->value('products_name'))); ?>
    </div>

<?php
      }
    }
?>

<div itemprop="description">
  <?php echo stripslashes($product_info->value('products_description')); ?>
</div>

<?php echo tep_draw_form('cart_quantity', tep_href_link('product_info.php', tep_get_all_get_params(array('action')) . 'action=add_product', $request_type), 'post', 'class="form-horizontal" role="form"'); ?>

<?php
    $Qpa = $OSCOM_Db->prepare('select distinct popt.products_options_id, popt.products_options_name from :table_products_options popt, :table_products_attributes patrib where patrib.products_id = :products_id and patrib.options_id = popt.products_options_id and popt.language_id = :languages_id order by popt.products_options_name');
    $Qpa->bindInt(':products_id', $_GET['products_id']);
    $Qpa->bindInt(':languages_id', $_SESSION['languages_id']);
    $Qpa->execute();
    if ( $Qpa->rowCount() ) {
?>

    <div class="page-header">
      <h4><?php echo TEXT_PRODUCT_OPTIONS; ?></h4>
    </div>

    <div class="row">
      <div class="col-sm-6">
<?php
      while ( $products_options_name = $Qpa->fetch() ) {
        $products_options_array = array();
        $Qpo = $OSCOM_Db->prepare('select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix from :table_products_attributes pa, :table_products_options_values pov where pa.products_id = :products_id and pa.options_id = :options_id and pa.options_values_id = pov.products_options_values_id and pov.language_id = :languages_id');
        $Qpo->bindInt(':products_id', $_GET['products_id']);
        $Qpo->bindInt(':options_id', $products_options_name['products_options_id']);
        $Qpo->bindInt(':languages_id', $_SESSION['languages_id']);
        $Qpo->execute();

        while ( $products_options = $Qpo->fetch() ) {
          $products_options_array[] = array('id' => $products_options['products_options_values_id'], 'text' => $products_options['products_options_values_name']);
          if ($products_options['options_values_price'] != '0') {
            $products_options_array[sizeof($products_options_array)-1]['text'] .= ' (' . $products_options['price_prefix'] . $currencies->display_price($products_options['options_values_price'], tep_get_tax_rate($product_info->value('products_tax_class_id'))) .') ';
          }
        }

        if (is_string($_GET['products_id']) && isset($_SESSION['cart']->contents[$_GET['products_id']]['attributes'][$products_options_name['products_options_id']])) {
          $selected_attribute = $_SESSION['cart']->contents[$_GET['products_id']]['attributes'][$products_options_name['products_options_id']];
        } else {
          $selected_attribute = false;
        }
?>
      <div class="form-group">
        <label class="control-label col-xs-3"><?php echo $products_options_name['products_options_name'] . ':'; ?></label>
        <div class="col-xs-9">
          <?php echo tep_draw_pull_down_menu('id[' . $products_options_name['products_options_id'] . ']', $products_options_array, $selected_attribute, '', true); ?>
        </div>
      </div>
    <?php
      }
?>
      </div>
    </div>

<?php
    }
?>

    <div class="clearfix"></div>

<?php
    if ($product_info->value('products_date_available') > date('Y-m-d H:i:s')) {
?>

    <div class="alert alert-info"><?php echo sprintf(TEXT_DATE_AVAILABLE, tep_date_long($product_info->value('products_date_available'))); ?></div>

<?php
    }

    $Qr = $OSCOM_Db->prepare('select count(*) as count, avg(reviews_rating) as avgrating from :table_reviews r, :table_reviews_description rd where r.products_id = :products_id and r.reviews_id = rd.reviews_id and rd.languages_id = :languages_id and reviews_status = 1');
    $Qr->bindInt(':products_id', $_GET['products_id']);
    $Qr->bindInt(':languages_id', $_SESSION['languages_id']);
    $Qr->execute();

    if ( $Qr->rowCount() > 0 ) {
      echo '<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"><meta itemprop="ratingValue" content="' . $Qr->value('avgrating') . '" /><meta itemprop="ratingCount" content="' . $Qr->value('count') . '" /></span>';
    }
?>

  <div class="row">
    <div class="col-sm-6 text-right pull-right"><?php echo tep_draw_hidden_field('products_id', $product_info->value('products_id')) . tep_draw_button(IMAGE_BUTTON_IN_CART, 'glyphicon glyphicon-shopping-cart', null, 'primary', null, 'btn-success'); ?></div>
    <div class="col-sm-6"><?php echo tep_draw_button(IMAGE_BUTTON_REVIEWS . (($Qr->rowCount() > 0) ? ' (' . $Qr->value('count') . ')' : ''), 'glyphicon glyphicon-comment', tep_href_link('product_reviews.php', tep_get_all_get_params())); ?></div>
  </div>

  </form>
  
  <div class="row">
    <?php echo $oscTemplate->getContent('product_info'); ?>
  </div>

<?php
    if ((USE_CACHE == 'true') && empty($SID)) {
      echo tep_cache_also_purchased(3600);
    } else {
      include('includes/modules/also_purchased_products.php');
    }
    
    if ( $product_info->value('manufacturers_id') > 0 ) {
      $Qm = $OSCOM_Db->prepare('select manufacturers_name from :table_manufacturers where manufacturers_id = :manufacturers_id');
      $Qm->bindInt(':manufacturers_id', $product_info->value('manufacturers_id'));
      $Qm->execute();

      if ( $Qm->fetch() !== false ) {
        echo '<span itemprop="manufacturer" itemscope itemtype="http://schema.org/Organization"><meta itemprop="name" content="' . tep_output_string($Qm->value('manufacturers_name')) . '" /></span>';
      }
    }
?>
  </div> <!-- contentText //-->
</div>

</div>

<?php
  }

  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
