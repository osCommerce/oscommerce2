<?php
use OSC\OM\OSCOM;

if ($_SESSION['cart']->count_contents() > 0) {
  ?>
  <li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo OSCOM::getDef('module_navbar_shopping_cart_contents', ['count_contents' => $_SESSION['cart']->count_contents()]); ?></a>
    <ul class="dropdown-menu">
<li><?php echo '<a href="' . OSCOM::link('shopping_cart.php') . '">' . OSCOM::getDef('module_navbar_shopping_cart_has_contents', ['count_contents' => $_SESSION['cart']->count_contents(), 'show_total'=> $currencies->format($_SESSION['cart']->show_total())]) . '</a>'; ?></li>
      <li role="separator" class="divider"></li>
      <?php
      foreach ($_SESSION['cart']->get_products() as $k => $v) {
        echo '<li>' .
             OSCOM::getDef('module_navbar_shopping_cart_product', [
               'product_url' => OSCOM::link('product_info.php', 'products_id=' . $v['id']),
               'product_quantity' => $v['quantity'],
               'product_name' => $v['name']
             ]) .
             '</li>';
      }
      ?>
      <li role="separator" class="divider"></li>
      <li><?php echo '<a href="' . OSCOM::link('shopping_cart.php') . '">' . OSCOM::getDef('module_navbar_shopping_cart_view_cart') . '</a>'; ?></li>
    </ul>
  </li>
  <?php
  echo '<li><a href="' . OSCOM::link('checkout_shipping.php') . '">' . OSCOM::getDef('module_navbar_shopping_cart_checkout') . '</a></li>';
}
else {
  echo '<li><p class="navbar-text">' . OSCOM::getDef('module_navbar_shopping_cart_no_contents') . '</p></li>';
}
?>