<?php
use OSC\OM\OSCOM;

if ($_SESSION['cart']->count_contents() > 0) {
  ?>
  <li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo sprintf(MODULE_NAVBAR_SHOPPING_CART_CONTENTS, $_SESSION['cart']->count_contents()); ?></a>
    <ul class="dropdown-menu">
      <li><?php echo '<a href="' . OSCOM::link('shopping_cart.php') . '">' . sprintf(MODULE_NAVBAR_SHOPPING_CART_HAS_CONTENTS, $_SESSION['cart']->count_contents(), $currencies->format($_SESSION['cart']->show_total())) . '</a>'; ?></li>
      <li role="separator" class="divider"></li>
      <?php
      foreach ($_SESSION['cart']->get_products() as $k => $v) {
        echo '<li>' . sprintf(MODULE_NAVBAR_SHOPPING_CART_PRODUCT, $v['id'], $v['quantity'], $v['name']) . '</li>';
      }
      ?>
      <li role="separator" class="divider"></li>
      <li><?php echo '<a href="' . OSCOM::link('shopping_cart.php') . '">' . MODULE_NAVBAR_SHOPPING_CART_VIEW_CART . '</a>'; ?></li>
    </ul>
  </li>
  <?php
  echo '<li><a href="' . OSCOM::link('checkout_shipping.php') . '">' . MODULE_NAVBAR_SHOPPING_CART_CHECKOUT . '</a></li>';
}
else {
  echo '<li><p class="navbar-text">' . MODULE_NAVBAR_SHOPPING_CART_NO_CONTENTS . '</p></li>';
}
?>