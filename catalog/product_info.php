<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\HTTP;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  if (!isset($_GET['products_id'])) {
    HTTP::redirect(OSCOM::link('index.php'));
  }

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/product_info.php');

  $product_exists = true;

  $Qproduct = $OSCOM_Db->prepare('select p.products_id, pd.products_name, pd.products_description, p.products_model, p.products_quantity, p.products_image, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id from :table_products p, :table_products_description pd where p.products_id = :products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id');
  $Qproduct->bindInt(':products_id', $_GET['products_id']);
  $Qproduct->bindInt(':language_id', $_SESSION['languages_id']);
  $Qproduct->execute();

  $product_exists = ($Qproduct->fetch() !== false);

  if ($product_exists === false) {
    header('HTTP/1.0 404 Not Found');
  } elseif (!empty($Qproduct->value('products_model'))) {
    // add the products model to the breadcrumb trail
    $breadcrumb->add($Qproduct->value('products_model'), OSCOM::link('product_info.php', 'cPath=' . $cPath . '&products_id=' . $Qproduct->valueInt('products_id')));
  }

  require('includes/template_top.php');

  if ($product_exists === false) {
?>

<div class="contentContainer">
  <div class="contentText">
    <div class="alert alert-warning">
      <?php echo TEXT_PRODUCT_NOT_FOUND; ?>
    </div>
  </div>

  <div class="text-right">
    <?php echo HTML::button(IMAGE_BUTTON_CONTINUE, 'glyphicon glyphicon-chevron-right', OSCOM::link('index.php'), null, null, 'btn-default btn-block'); ?>
  </div>
</div>

<?php
  } else {
    $Qupdate = $OSCOM_Db->prepare('update :table_products_description set products_viewed = products_viewed + 1 where products_id = :products_id and language_id = :language_id');
    $Qupdate->bindInt(':products_id', $Qproduct->valueInt('products_id'));
    $Qupdate->bindInt(':language_id', $_SESSION['languages_id']);
    $Qupdate->execute();

    if ($new_price = tep_get_products_special_price($Qproduct->valueInt('products_id'))) {
      $products_price = '<del>' . $currencies->display_price($Qproduct->valueDecimal('products_price'), tep_get_tax_rate($Qproduct->valueInt('products_tax_class_id'))) . '</del> <span class="productSpecialPrice" itemprop="price">' . $currencies->display_price($new_price, tep_get_tax_rate($Qproduct->valueInt('products_tax_class_id'))) . '</span>';
    } else {
      $products_price = '<span itemprop="price">' . $currencies->display_price($Qproduct->valueDecimal('products_price'), tep_get_tax_rate($Qproduct->valueInt('products_tax_class_id'))) . '</span>';
    }

    if ($Qproduct->value('products_date_available') > date('Y-m-d H:i:s')) {
      $products_price .= '<link itemprop="availability" href="http://schema.org/PreOrder" />';
    } elseif ((STOCK_CHECK == 'true') && ($Qproduct->valueInt('products_quantity') < 1)) {
      $products_price .= '<link itemprop="availability" href="http://schema.org/OutOfStock" />';
    } else {
      $products_price .= '<link itemprop="availability" href="http://schema.org/InStock" />';
    }

    $products_price .= '<meta itemprop="priceCurrency" content="' . tep_output_string($_SESSION['currency']) . '" />';

    $products_name = '<a href="' . OSCOM::link('product_info.php', 'products_id=' . $Qproduct->valueInt('products_id')) . '" itemprop="url"><span itemprop="name">' . $Qproduct->value('products_name') . '</span></a>';

    if ( !empty($Qproduct->value('products_model')) ) {
      $products_name .= ' <small>[<span itemprop="model">' . $Qproduct->value('products_model') . '</span>]</small>';
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
    if ( !empty($Qproduct->value('products_image')) ) {
      echo '    ' . HTML::image(DIR_WS_IMAGES . $Qproduct->value('products_image'), null, null, null, 'itemprop="image" style="display:none;"');

      $photoset_layout = '1';

      $Qpi = $OSCOM_Db->get('products_images', ['image', 'htmlcontent'], ['products_id' => $Qproduct->valueInt('products_id')], 'sort_order');
      $pi = $Qpi->fetchAll();

      $pi_total = count($pi);

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

        foreach ($pi as $image) {
          $pi_counter++;

          if (!empty($image['htmlcontent'])) {
            $pi_html[] = '<div id="piGalDiv_' . $pi_counter . '">' . $image['htmlcontent'] . '</div>';
          }

          echo '      ' . HTML::image(DIR_WS_IMAGES . $image['image'], '', '', '', 'id="piGalImg_' . $pi_counter . '"') . "\n";
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
      <?php echo HTML::image(DIR_WS_IMAGES . $Qproduct->value('products_image'), $Qproduct->value('products_name')); ?>
    </div>

<?php
      }
    }
?>

<div itemprop="description">
  <?php echo $Qproduct->value('products_description'); ?>
</div>

<?php echo HTML::form('cart_quantity', OSCOM::link('product_info.php', tep_get_all_get_params(array('action')) . 'action=add_product', $request_type), 'post', 'class="form-horizontal" role="form"'); ?>

<?php
    $Qpa = $OSCOM_Db->prepare('select distinct popt.products_options_id, popt.products_options_name from :table_products_options popt, :table_products_attributes patrib where patrib.products_id = :products_id and patrib.options_id = popt.products_options_id and popt.language_id = :language_id order by popt.products_options_name');
    $Qpa->bindInt(':products_id', $Qproduct->valueInt('products_id'));
    $Qpa->bindInt(':language_id', $_SESSION['languages_id']);
    $Qpa->execute();

    if ($Qpa->fetch() !== false) {
?>

    <div class="page-header">
      <h4><?php echo TEXT_PRODUCT_OPTIONS; ?></h4>
    </div>

    <div class="row">
      <div class="col-sm-6">
<?php
      do {
        $products_options_array = array();

        $Qpo = $OSCOM_Db->prepare('select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix from :table_products_attributes pa, :table_products_options_values pov where pa.products_id = :products_id and pa.options_id = :options_id and pa.options_values_id = pov.products_options_values_id and pov.language_id = :language_id');
        $Qpo->bindInt(':products_id', $Qproduct->valueInt('products_id'));
        $Qpo->bindInt(':options_id', $Qpa->valueInt('products_options_id'));
        $Qpo->bindInt(':language_id', $_SESSION['languages_id']);
        $Qpo->execute();

        while ($Qpo->fetch()) {
          $products_options_array[] = array('id' => $Qpo->valueInt('products_options_values_id'), 'text' => $Qpo->value('products_options_values_name'));

          if ($Qpo->valueDecimal('options_values_price') != 0) {
            $products_options_array[count($products_options_array)-1]['text'] .= ' (' . $Qpo->value('price_prefix') . $currencies->display_price($Qpo->valueDecimal('options_values_price'), tep_get_tax_rate($Qproduct->valueInt('products_tax_class_id'))) .') ';
          }
        }

        if (is_string($_GET['products_id']) && isset($_SESSION['cart']->contents[$_GET['products_id']]['attributes'][$Qpa->value('products_options_id')])) {
          $selected_attribute = $_SESSION['cart']->contents[$_GET['products_id']]['attributes'][$Qpa->value('products_options_id')];
        } else {
          $selected_attribute = false;
        }
?>
      <div class="form-group">
        <label class="control-label col-xs-3"><?php echo $Qpa->value('products_options_name') . ':'; ?></label>
        <div class="col-xs-9">
          <?php echo HTML::selectField('id[' . $Qpa->valueInt('products_options_id') . ']', $products_options_array, $selected_attribute, '', true); ?>
        </div>
      </div>
    <?php
      } while ($Qpa->fetch());
?>
      </div>
    </div>

<?php
    }
?>

    <div class="clearfix"></div>

<?php
    if ($Qproduct->value('products_date_available') > date('Y-m-d H:i:s')) {
?>

    <div class="alert alert-info"><?php echo sprintf(TEXT_DATE_AVAILABLE, tep_date_long($Qproduct->value('products_date_available'))); ?></div>

<?php
    }

    $has_rating = false;

    $Qr = $OSCOM_Db->prepare('select count(*) as count, avg(reviews_rating) as avgrating from :table_reviews r, :table_reviews_description rd where r.products_id = :products_id and r.reviews_id = rd.reviews_id and rd.languages_id = :languages_id and reviews_status = 1');
    $Qr->bindInt(':products_id', $Qproduct->valueInt('products_id'));
    $Qr->bindInt(':languages_id', $_SESSION['languages_id']);
    $Qr->execute();

    if ($Qr->fetch() !== false) {
      $has_rating = true;

      echo '<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"><meta itemprop="ratingValue" content="' . $Qr->value('avgrating') . '" /><meta itemprop="ratingCount" content="' . $Qr->value('count') . '" /></span>';
    }
?>

  <div class="row">
    <div class="col-sm-6 text-right pull-right"><?php echo HTML::hiddenField('products_id', $Qproduct->valueInt('products_id')) . HTML::button(IMAGE_BUTTON_IN_CART, 'glyphicon glyphicon-shopping-cart', null, 'primary', null, 'btn-success'); ?></div>
    <div class="col-sm-6"><?php echo HTML::button(IMAGE_BUTTON_REVIEWS . (($has_rating === true) ? ' (' . $Qr->value('count') . ')' : ''), 'glyphicon glyphicon-comment', OSCOM::link('product_reviews.php', tep_get_all_get_params())); ?></div>
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
    
    if ( $Qproduct->valueInt('manufacturers_id') > 0 ) {
      $Qm = $OSCOM_Db->get('manufacturers', 'manufacturers_name', ['manufacturers_id' => $Qproduct->valueInt('manufacturers_id')]);

      if ( $Qm->fetch() !== false ) {
        echo '<span itemprop="manufacturer" itemscope itemtype="http://schema.org/Organization"><meta itemprop="name" content="' . $Qm->valueProtected('manufacturers_name') . '" /></span>';
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
