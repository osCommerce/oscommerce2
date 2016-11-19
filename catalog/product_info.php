<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\DateTime;
  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  require('includes/application_top.php');

  if (!isset($_GET['products_id'])) {
    OSCOM::redirect('index.php');
  }

  $OSCOM_Language->loadDefinitions('product_info');

  $product_exists = true;

  $Qproduct = $OSCOM_Db->prepare('select p.products_id, pd.products_name, pd.products_description, p.products_model, p.products_quantity, p.products_image, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id from :table_products p, :table_products_description pd where p.products_id = :products_id and p.products_status = 1 and p.products_id = pd.products_id and pd.language_id = :language_id');
  $Qproduct->bindInt(':products_id', $_GET['products_id']);
  $Qproduct->bindInt(':language_id', $OSCOM_Language->getId());
  $Qproduct->execute();

  $product_exists = ($Qproduct->fetch() !== false);

  if ($product_exists === false) {
    header('HTTP/1.0 404 Not Found');
  } elseif (!empty($Qproduct->value('products_model'))) {
    // add the products model to the breadcrumb trail
    $breadcrumb->add($Qproduct->value('products_model'), OSCOM::link('product_info.php', 'cPath=' . $cPath . '&products_id=' . $Qproduct->valueInt('products_id')));
  }

  require($oscTemplate->getFile('template_top.php'));

  if ($product_exists === false) {
?>

<div class="contentContainer">
  <div class="contentText">
    <div class="alert alert-warning"><?php echo OSCOM::getDef('text_product_not_found'); ?></div>
  </div>

  <div class="pull-right">
    <?php echo HTML::button(OSCOM::getDef('image_button_continue'), 'fa fa-angle-right', OSCOM::link('index.php')); ?>
  </div>
</div>

<?php
  } else {
    $Qupdate = $OSCOM_Db->prepare('update :table_products_description set products_viewed = products_viewed + 1 where products_id = :products_id and language_id = :language_id');
    $Qupdate->bindInt(':products_id', $Qproduct->valueInt('products_id'));
    $Qupdate->bindInt(':language_id', $OSCOM_Language->getId());
    $Qupdate->execute();

    if ($new_price = tep_get_products_special_price($Qproduct->valueInt('products_id'))) {
      $products_price = '<del>' . $currencies->display_price($Qproduct->valueDecimal('products_price'), tep_get_tax_rate($Qproduct->valueInt('products_tax_class_id'))) . '</del> <span class="productSpecialPrice" itemprop="price" content="' . $currencies->display_raw($new_price, tep_get_tax_rate($Qproduct->valueInt('products_tax_class_id'))) . '">' . $currencies->display_price($new_price, tep_get_tax_rate($Qproduct->valueInt('products_tax_class_id'))) . '</span>';
    } else {
      $products_price = '<span itemprop="price" content="' . $currencies->display_raw($Qproduct->valueDecimal('products_price'), tep_get_tax_rate($Qproduct->valueInt('products_tax_class_id'))) . '">' . $currencies->display_price($Qproduct->valueDecimal('products_price'), tep_get_tax_rate($Qproduct->valueInt('products_tax_class_id'))) . '</span>';
    }

    if ($Qproduct->value('products_date_available') > date('Y-m-d H:i:s')) {
      $products_price .= '<link itemprop="availability" href="http://schema.org/PreOrder" />';
    } elseif ((STOCK_CHECK == 'true') && ($Qproduct->valueInt('products_quantity') < 1)) {
      $products_price .= '<link itemprop="availability" href="http://schema.org/OutOfStock" />';
    } else {
      $products_price .= '<link itemprop="availability" href="http://schema.org/InStock" />';
    }

    $products_price .= '<meta itemprop="priceCurrency" content="' . HTML::output($_SESSION['currency']) . '" />';

    $products_name = '<a href="' . OSCOM::link('product_info.php', 'products_id=' . $Qproduct->valueInt('products_id')) . '" itemprop="url"><span itemprop="name">' . $Qproduct->value('products_name') . '</span></a>';

    if ( !empty($Qproduct->value('products_model')) ) {
      $products_name .= '<br /><small>[<span itemprop="model">' . $Qproduct->value('products_model') . '</span>]</small>';
    }
?>

<?php echo HTML::form('cart_quantity', OSCOM::link('product_info.php', tep_get_all_get_params(array('action')) . 'action=add_product'), 'post', 'class="form-horizontal" role="form"'); ?>

<div itemscope itemtype="http://schema.org/Product">

<div class="page-header">
  <div class="row">
    <h1 class="col-sm-8"><?php echo $products_name; ?></h1>
    <h2 class="col-sm-4 text-right-not-xs" itemprop="offers" itemscope itemtype="http://schema.org/Offer"><?php echo $products_price; ?></h2>
  </div>
</div>

<?php
  if ($messageStack->size('product_action') > 0) {
    echo $messageStack->output('product_action');
  }
?>

<div class="contentContainer">
  <div class="contentText">

<?php
    if ( !empty($Qproduct->value('products_image')) ) {

      echo HTML::image(OSCOM::linkImage($Qproduct->value('products_image')), null, null, null, 'itemprop="image" style="display:none;"');

      $photoset_layout = (int)MODULE_HEADER_TAGS_PRODUCT_COLORBOX_LAYOUT;

      $Qpi = $OSCOM_Db->get('products_images', ['image', 'htmlcontent'], ['products_id' => $Qproduct->valueInt('products_id')], 'sort_order');
      $pi = $Qpi->fetchAll();

      $pi_total = count($pi);

      if ($pi_total > 0) {
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

          echo HTML::image(OSCOM::linkImage($image['image']), '', '', '', 'id="piGalImg_' . $pi_counter . '"') . "\n";
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
      <?php echo HTML::image(OSCOM::linkImage($Qproduct->value('products_image')), $Qproduct->value('products_name')); ?>
    </div>

<?php
      }
    }
?>

<div itemprop="description">
  <?php echo $Qproduct->value('products_description'); ?>
</div>

<?php
    $Qpa = $OSCOM_Db->prepare('select distinct popt.products_options_id, popt.products_options_name from :table_products_options popt, :table_products_attributes patrib where patrib.products_id = :products_id and patrib.options_id = popt.products_options_id and popt.language_id = :language_id order by popt.products_options_name');
    $Qpa->bindInt(':products_id', $Qproduct->valueInt('products_id'));
    $Qpa->bindInt(':language_id', $OSCOM_Language->getId());
    $Qpa->execute();

    if ($Qpa->fetch() !== false) {
?>

    <h4><?php echo OSCOM::getDef('text_product_options'); ?></h4>

    <p>
<?php
      do {
        $products_options_array = array();

        $Qpo = $OSCOM_Db->prepare('select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix from :table_products_attributes pa, :table_products_options_values pov where pa.products_id = :products_id and pa.options_id = :options_id and pa.options_values_id = pov.products_options_values_id and pov.language_id = :language_id');
        $Qpo->bindInt(':products_id', $Qproduct->valueInt('products_id'));
        $Qpo->bindInt(':options_id', $Qpa->valueInt('products_options_id'));
        $Qpo->bindInt(':language_id', $OSCOM_Language->getId());
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
      <strong><?php echo $Qpa->value('products_options_name') . ':'; ?></strong><br /><?php echo HTML::selectField('id[' . $Qpa->valueInt('products_options_id') . ']', $products_options_array, $selected_attribute, 'style="width: 200px;"'); ?><br />
<?php
      } while ($Qpa->fetch());
?>
    </p>

<?php
    }
?>

    <div class="clearfix"></div>

<?php
    if ($Qproduct->value('products_date_available') > date('Y-m-d H:i:s')) {
?>

    <div class="alert alert-info"><?php echo OSCOM::getDef('text_date_available', ['products_date_available' => DateTime::toLong($Qproduct->value('products_date_available'))]); ?></div>

<?php
    }
?>

  </div>

<?php
    $has_rating = false;

    $Qr = $OSCOM_Db->prepare('select count(*) as count, avg(reviews_rating) as avgrating from :table_reviews r, :table_reviews_description rd where r.products_id = :products_id and r.reviews_id = rd.reviews_id and rd.languages_id = :languages_id and reviews_status = 1');
    $Qr->bindInt(':products_id', $Qproduct->valueInt('products_id'));
    $Qr->bindInt(':languages_id', $OSCOM_Language->getId());
    $Qr->execute();

    if ($Qr->fetch() !== false) {
      $has_rating = true;

      echo '<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"><meta itemprop="ratingValue" content="' . $Qr->value('avgrating') . '" /><meta itemprop="ratingCount" content="' . $Qr->value('count') . '" /></span>';
    }
?>

  <div class="buttonSet row">
    <div class="col-xs-6"><?php echo HTML::button(OSCOM::getDef('image_button_reviews') . (($has_rating === true) ? ' (' . $Qr->value('count') . ')' : ''), 'fa fa-commenting', OSCOM::link('product_reviews.php', tep_get_all_get_params())); ?></div>
    <div class="col-xs-6 text-right"><?php echo HTML::hiddenField('products_id', $Qproduct->valueInt('products_id')) . HTML::button(OSCOM::getDef('image_button_in_cart'), 'fa fa-shopping-cart', null, null, 'btn-success'); ?></div>
  </div>

  <div class="row">
    <?php echo $oscTemplate->getContent('product_info'); ?>
  </div>

<?php
    include('includes/content/also_purchased_products.php');

    if ( $Qproduct->valueInt('manufacturers_id') > 0 ) {
      $Qm = $OSCOM_Db->get('manufacturers', 'manufacturers_name', ['manufacturers_id' => $Qproduct->valueInt('manufacturers_id')]);

      if ( $Qm->fetch() !== false ) {
        echo '<span itemprop="manufacturer" itemscope itemtype="http://schema.org/Organization"><meta itemprop="name" content="' . $Qm->valueProtected('manufacturers_name') . '" /></span>';
      }
    }
?>

</div>

</div>

</form>

<?php
  }

  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
