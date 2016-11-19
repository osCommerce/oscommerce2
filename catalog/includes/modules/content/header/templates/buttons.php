<?php
use OSC\OM\HTML;
use OSC\OM\OSCOM;
?>
<div id="headerShortcuts" class="col-sm-<?php echo $content_width; ?> text-right buttons">
  <div class="btn-group">
<?php
  echo HTML::button(OSCOM::getDef('module_content_header_buttons_title_cart_contents') . ($_SESSION['cart']->count_contents() > 0 ? ' (' . $_SESSION['cart']->count_contents() . ')' : ''), 'fa fa-shopping-cart', OSCOM::link('shopping_cart.php')) .
       HTML::button(OSCOM::getDef('module_content_header_buttons_title_checkout'), 'fa fa-credit-card', OSCOM::link('checkout_shipping.php')) .
       HTML::button(OSCOM::getDef('module_content_header_buttons_title_my_account'), 'fa fa-user', OSCOM::link('account.php'));

  if (isset($_SESSION['customer_id'])) {
    echo HTML::button(OSCOM::getDef('module_content_header_buttons_title_logoff'), 'fa fa-sign-out', OSCOM::link('logoff.php'));
  }
?>
  </div>
</div>

