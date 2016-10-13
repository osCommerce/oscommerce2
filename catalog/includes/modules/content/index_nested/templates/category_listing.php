<?php
use OSC\OM\HTML;
use OSC\OM\OSCOM;
?>
<div class="col-sm-<?php echo $content_width; ?> category-listing">
  <div itemscope itemtype="http://schema.org/ItemList">
    <meta itemprop="itemListOrder" content="http://schema.org/ItemListUnordered" />
    <meta itemprop="name" content="<?php echo $category['categories_name']; ?>" />

    <?php
    foreach ($categories as $c) {
      $cPath_new = tep_get_path($c['categories_id']);
      echo '<div class="col-sm-' . $category_width . '">';
      echo '  <div class="text-center">';
      echo '    <a href="' . OSCOM::link('index.php', $cPath_new) . '">' . HTML::image(OSCOM::linkImage($c['categories_image']), $c['categories_name'], SUBCATEGORY_IMAGE_WIDTH, SUBCATEGORY_IMAGE_HEIGHT) . '</a>';
      echo '    <div class="caption text-center">';
      echo '      <h5><a href="' . OSCOM::link('index.php', $cPath_new) . '"><span itemprop="itemListElement">' . $c['categories_name'] . '</span></a></h5>';
      echo '    </div>';
      echo '  </div>';
      echo '</div>';
    }
    ?>
  </div>
</div>
