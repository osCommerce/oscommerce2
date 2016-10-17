<?php
use OSC\OM\HTML;
use OSC\OM\OSCOM;
?>
<div id="headerShortcuts" class="col-sm-<?php echo $content_width; ?> text-right buttons">
  <div class="btn-group">
<?php
  echo HTML::button(MODULE_CONTENT_HEADER_BUTTONS_TITLE_CART_CONTENTS . ($_SESSION['cart']->count_contents() > 0 ? ' (' . $_SESSION['cart']->count_contents() . ')' : ''), 'fa fa-shopping-cart', OSCOM::link('shopping_cart.php')) .
       HTML::button(MODULE_CONTENT_HEADER_BUTTONS_TITLE_CHECKOUT, 'fa fa-credit-card', OSCOM::link('checkout_shipping.php', '', 'SSL')) .
       HTML::button(MODULE_CONTENT_HEADER_BUTTONS_TITLE_MY_ACCOUNT, 'fa fa-user', OSCOM::link('account.php', '', 'SSL'));

  if (isset($_SESSION['customer_id'])) {
    echo HTML::button(MODULE_CONTENT_HEADER_BUTTONS_TITLE_LOGOFF, 'fa fa-sign-out', OSCOM::link('logoff.php', '', 'SSL'));
  }
?>
  </div>
</div>

