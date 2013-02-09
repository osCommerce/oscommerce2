<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  $oscTemplate->buildBlocks();

  if (!$oscTemplate->hasBlocks('boxes_column_left')) {
    $oscTemplate->setGridContentWidth($oscTemplate->getGridContentWidth() + $oscTemplate->getGridColumnWidth());
  }

  if (!$oscTemplate->hasBlocks('boxes_column_right')) {
    $oscTemplate->setGridContentWidth($oscTemplate->getGridContentWidth() + $oscTemplate->getGridColumnWidth());
  }
?>
<!doctype html>

<html <?php echo HTML_PARAMS; ?>>

<head>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />

<title><?php echo tep_output_string_protected($oscTemplate->getTitle()); ?></title>

<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>" />

<link rel="icon" type="image/png" href="{publiclink}images/oscommerce_icon.png{publiclink}" />

<meta name="generator" content="osCommerce Online Merchant" />

<script type="text/javascript" src="ext/jquery/jquery-1.9.1.min.js"></script>

<script type="text/javascript" src="ext/bootstrap/js/bootstrap.min.js"></script>
<link rel="stylesheet" type="text/css" href="public/template/gosling/css/general.css" />






<link rel="stylesheet" type="text/css" href="ext/jquery/ui/redmond/jquery-ui-1.8.23.css" />
<script type="text/javascript" src="ext/jquery/ui/jquery-ui-1.8.23.min.js"></script>

<?php
  if (tep_not_null(JQUERY_DATEPICKER_I18N_CODE)) {
?>
<script type="text/javascript" src="ext/jquery/ui/i18n/jquery.ui.datepicker-<?php echo JQUERY_DATEPICKER_I18N_CODE; ?>.js"></script>
<script type="text/javascript">
$.datepicker.setDefaults($.datepicker.regional['<?php echo JQUERY_DATEPICKER_I18N_CODE; ?>']);
</script>
<?php
  }
?>

<script type="text/javascript" src="ext/jquery/bxGallery/jquery.bxGallery.1.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="ext/jquery/fancybox/jquery.fancybox-1.3.4.css" />
<script type="text/javascript" src="ext/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>

<?php
  echo $oscTemplate->getBlocks('header_tags');
?>

</head>
<body>

<div id="bodyWrapper" class="container-fluid">

<?php
  if ($messageStack->size('header') > 0) {
    echo '<div class="row-fluid">' . $messageStack->output('header') . '</div>';
  }
?>

  <div id="header" class="row-fluid">
    <div id="storeLogo"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image(DIR_WS_IMAGES . 'store_logo.png', STORE_NAME) . '</a>'; ?></div>

    <div id="headerShortcuts">
<?php
  echo tep_draw_button(HEADER_TITLE_CART_CONTENTS . ($_SESSION['cart']->count_contents() > 0 ? ' (' . $_SESSION['cart']->count_contents() . ')' : ''), 'cart', tep_href_link(FILENAME_SHOPPING_CART)) .
       tep_draw_button(HEADER_TITLE_CHECKOUT, 'triangle-1-e', tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL')) .
       tep_draw_button(HEADER_TITLE_MY_ACCOUNT, 'person', tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));

  if (isset($_SESSION['customer_id'])) {
    echo tep_draw_button(HEADER_TITLE_LOGOFF, null, tep_href_link(FILENAME_LOGOFF, '', 'SSL'));
  }
?>
    </div>
  </div>

<script>
$('#headerShortcuts').buttonset();
</script>

  <div class="row-fluid ui-widget infoBoxContainer">
    <div class="ui-widget-header infoBoxHeading"><?php echo '&nbsp;&nbsp;' . $breadcrumb->trail(' &raquo; '); ?></div>
  </div>

<?php
  if (isset($_GET['error_message']) && tep_not_null($_GET['error_message'])) {
?>

  <table border="0" width="100%" cellspacing="0" cellpadding="2">
    <tr class="headerError">
      <td class="headerError"><?php echo htmlspecialchars(urldecode($_GET['error_message'])); ?></td>
    </tr>
  </table>

<?php
  }

  if (isset($_GET['info_message']) && tep_not_null($_GET['info_message'])) {
?>

  <table border="0" width="100%" cellspacing="0" cellpadding="2">
    <tr class="headerInfo">
      <td class="headerInfo"><?php echo htmlspecialchars(urldecode($_GET['info_message'])); ?></td>
    </tr>
  </table>

<?php
  }
?>

  <div class="row-fluid">

<?php
  if ( $oscTemplate->hasBlocks('boxes_column_left') ) {
?>

    <div id="columnLeft" class="span<?php echo $oscTemplate->getGridColumnWidth(); ?>">
      <?php echo $oscTemplate->getBlocks('boxes_column_left'); ?>
    </div>

<?php
  }
?>

    <div id="bodyContent" class="span<?php echo $oscTemplate->getGridContentWidth(); ?>">
      <?php require($OSCOM_APP->getContentFile(true)); ?>
    </div>

<?php
  if ( $oscTemplate->hasBlocks('boxes_column_right') ) {
?>

    <div id="columnRight" class="span<?php echo $oscTemplate->getGridColumnWidth(); ?>">
      <?php echo $oscTemplate->getBlocks('boxes_column_right'); ?>
    </div>

<?php
  }
?>

    <div class="footer span12">
      <p align="center"><?php echo FOOTER_TEXT_BODY; ?></p>
    </div>

<?php
  if ($banner = tep_banner_exists('dynamic', '468x50')) {
?>

    <div class="span12" style="text-align: center; padding-bottom: 20px;">
      <?php echo tep_display_banner('static', $banner); ?>
    </div>

<?php
  }
?>

  </div>
</div>

<script>
$('.productListTable tr:nth-child(even)').addClass('alt');
</script>

<?php
  echo $oscTemplate->getBlocks('footer_scripts');
?>

</body>
</html>
