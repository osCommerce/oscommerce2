<?php
use OSC\OM\OSCOM;
?>
<div id="headerShortcuts" class="col-sm-<?php echo $content_width; ?> text-right">
  <div class="btn-group">
<?php
  echo tep_draw_button(MODULE_CONTENT_HEADER_BUTTONS_TITLE_CART_CONTENTS . ($_SESSION['cart']->count_contents() > 0 ? ' (' . $_SESSION['cart']->count_contents() . ')' : ''), 'glyphicon glyphicon-shopping-cart', OSCOM::link('shopping_cart.php')) .
       tep_draw_button(MODULE_CONTENT_HEADER_BUTTONS_TITLE_CHECKOUT, 'glyphicon glyphicon-credit-card', OSCOM::link('checkout_shipping.php', '', 'SSL')) .
       tep_draw_button(MODULE_CONTENT_HEADER_BUTTONS_TITLE_MY_ACCOUNT, 'glyphicon glyphicon-user', OSCOM::link('account.php', '', 'SSL'));

  if (isset($_SESSION['customer_id'])) {
    echo tep_draw_button(MODULE_CONTENT_HEADER_BUTTONS_TITLE_LOGOFF, 'glyphicon glyphicon-log-out', OSCOM::link('logoff.php', '', 'SSL'));
  }
?>
  </div>
</div>

