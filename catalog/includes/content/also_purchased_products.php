<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  if (isset($_GET['products_id'])) {
    $Qorders = $OSCOM_Db->prepare('select p.products_id, p.products_image, pd.products_name from :table_orders_products opa, :table_orders_products opb, :table_orders o, :table_products p left join :table_products_description pd on p.products_id = pd.products_id where opa.products_id = :products_id and opa.orders_id = opb.orders_id and opb.products_id != opa.products_id and opb.products_id = p.products_id and opb.orders_id = o.orders_id and p.products_status = 1 and pd.language_id = :language_id group by p.products_id order by o.date_purchased desc limit :limit');
    $Qorders->bindInt(':products_id', $_GET['products_id']);
    $Qorders->bindInt(':language_id', $OSCOM_Language->getId());
    $Qorders->bindInt(':limit', MAX_DISPLAY_ALSO_PURCHASED);
    $Qorders->setCache('products-also_purchased-p' . (int)$_GET['products_id'] . '-lang' . $OSCOM_Language->getId(), 3600);
    $Qorders->execute();

    $orders = $Qorders->fetchAll();

    if (count($orders) >= MIN_DISPLAY_ALSO_PURCHASED) {
      $also_pur_prods_content = NULL;

      foreach ($orders as $o) {
        $also_pur_prods_content .= '<div class="col-sm-6 col-md-4">';
        $also_pur_prods_content .= '  <div class="thumbnail">';
        $also_pur_prods_content .= '    <a href="' . OSCOM::link('product_info.php', 'products_id=' . $o['products_id']) . '">' . HTML::image(OSCOM::linkImage($o['products_image']), $o['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>';
        $also_pur_prods_content .= '    <div class="caption">';
        $also_pur_prods_content .= '      <h5 class="text-center"><a href="' . OSCOM::link('product_info.php', 'products_id=' . $o['products_id']) . '"><span itemprop="itemListElement">' . $o['products_name'] . '</span></a></h5>';
        $also_pur_prods_content .= '    </div>';
        $also_pur_prods_content .= '  </div>';
        $also_pur_prods_content .= '</div>';
      }

?>

  <br />
  <div itemscope itemtype="http://schema.org/ItemList">
    <meta itemprop="itemListOrder" content="http://schema.org/ItemListUnordered" />
    <meta itemprop="numberOfItems" content="<?php echo count($orders); ?>" />

    <h3 itemprop="name"><?php echo TEXT_ALSO_PURCHASED_PRODUCTS; ?></h3>

    <div class="row">
      <?php echo $also_pur_prods_content; ?>
    </div>

  </div>

<?php
    }
  }
?>
