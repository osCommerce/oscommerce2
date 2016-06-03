<?php
use OSC\OM\OSCOM;
?>
<div class="panel panel-default">
  <div class="panel-heading"><a href="<?php echo OSCOM::link('shopping_cart.php'); ?>"><?php echo MODULE_BOXES_SHOPPING_CART_BOX_TITLE; ?></a></div>
  <div class="panel-body">
    <ul class="shoppingCartList">
      <?php echo $cart_contents_string; ?>
    </ul>
  </div>
</div>
