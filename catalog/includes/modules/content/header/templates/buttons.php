<div id="headerShortcuts" class="col-sm-<?php echo $content_width; ?> text-right">
  <div class="btn-group">
<?php
  echo osc_draw_button(HEADER_TITLE_CART_CONTENTS . ($_SESSION['cart']->count_contents() > 0 ? ' (' . $_SESSION['cart']->count_contents() . ')' : ''), 'glyphicon glyphicon-shopping-cart', osc_href_link(FILENAME_SHOPPING_CART)) .
       osc_draw_button(HEADER_TITLE_CHECKOUT, 'glyphicon glyphicon-credit-card', osc_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL')) .
       osc_draw_button(HEADER_TITLE_MY_ACCOUNT, 'glyphicon glyphicon-user', osc_href_link(FILENAME_ACCOUNT, '', 'SSL'));

  if (isset($_SESSION['customer_id'])) {
    echo osc_draw_button(HEADER_TITLE_LOGOFF, 'glyphicon glyphicon-log-out', osc_href_link(FILENAME_LOGOFF, '', 'SSL'));
  }
?>
  </div>
</div>

