<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  if ($messageStack->size('header') > 0) {
    echo '<div class="grid-container">' . $messageStack->output('header') . '</div>';
  }
?>

<header id="header" class="grid-container">
  <h1 id="storeLogo" class="grid-50"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image(DIR_WS_IMAGES . 'store_logo.png', STORE_NAME) . '</a>'; ?></h1>

  <nav id="headerShortcuts" role="navigation" class="grid-50">
    <?php echo tep_draw_button(HEADER_TITLE_CART_CONTENTS . ($cart->count_contents() > 0 ? ' (' . $cart->count_contents() . ')' : ''), 'cart', tep_href_link(FILENAME_SHOPPING_CART)); ?>

    <?php echo tep_draw_button(HEADER_TITLE_CHECKOUT, 'triangle-1-e', tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL')); ?>

    <?php echo tep_draw_button(HEADER_TITLE_MY_ACCOUNT, 'person', tep_href_link(FILENAME_ACCOUNT, '', 'SSL')); ?>

<?php
  if (tep_session_is_registered('customer_id')) {
    echo tep_draw_button(HEADER_TITLE_LOGOFF, null, tep_href_link(FILENAME_LOGOFF, '', 'SSL'));
  }
?>

  </nav>

  <nav id="breadcrumb" role="navigation" class="grid-100 ui-widget infoBoxContainer">
    <ol class="ui-widget-header infoBoxHeading"><li><?php echo $breadcrumb->trail('</li><li>'); ?></li></ol>
  </nav>

</header>

<?php
  if (isset($HTTP_GET_VARS['error_message']) && tep_not_null($HTTP_GET_VARS['error_message'])) {
?>
<div class="grid-container">
  <div class="grid-100 headerError"><?php echo htmlspecialchars(stripslashes(urldecode($HTTP_GET_VARS['error_message']))); ?></div>
</div>
<?php
  }

  if (isset($HTTP_GET_VARS['info_message']) && tep_not_null($HTTP_GET_VARS['info_message'])) {
?>
<div class="grid-container">
  <div class="grid-100 headerInfo"><?php echo htmlspecialchars(stripslashes(urldecode($HTTP_GET_VARS['info_message']))); ?></div>
</div>
<?php
  }
?>
