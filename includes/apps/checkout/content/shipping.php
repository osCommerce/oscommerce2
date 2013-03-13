<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */
?>

<script type="text/javascript"><!--
var selected;

function selectRowEffect(object, buttonSelect) {
  if (!selected) {
    if (document.getElementById) {
      selected = document.getElementById('defaultSelected');
    } else {
      selected = document.all['defaultSelected'];
    }
  }

  if (selected) selected.className = 'moduleRow';
  object.className = 'moduleRowSelected';
  selected = object;

// one button is not an array
  if (document.checkout_address.shipping[0]) {
    document.checkout_address.shipping[buttonSelect].checked=true;
  } else {
    document.checkout_address.shipping.checked=true;
  }
}

function rowOverEffect(object) {
  if (object.className == 'moduleRow') object.className = 'moduleRowOver';
}

function rowOutEffect(object) {
  if (object.className == 'moduleRowOver') object.className = 'moduleRow';
}
//--></script>

<h1><?php echo HEADING_TITLE_SHIPPING; ?></h1>

<?php echo osc_draw_form('checkout_address', osc_href_link('checkout', 'shipping&process', 'SSL'), 'post', '', true); ?>

<div class="contentContainer">
  <h2><?php echo TABLE_HEADING_SHIPPING_ADDRESS; ?></h2>

  <div class="contentText">
    <div class="ui-widget infoBoxContainer" style="float: right;">
      <div class="ui-widget-header infoBoxHeading"><?php echo TITLE_SHIPPING_ADDRESS; ?></div>

      <div class="ui-widget-content infoBoxContents">
        <?php echo osc_address_label($OSCOM_Customer->getID(), $_SESSION['sendto'], true, ' ', '<br />'); ?>
      </div>
    </div>

    <?php echo TEXT_CHOOSE_SHIPPING_DESTINATION; ?><br /><br /><?php echo osc_draw_button(IMAGE_BUTTON_CHANGE_ADDRESS, 'home', osc_href_link('checkout', 'shipping&address', 'SSL'), 'info'); ?>
  </div>

  <div style="clear: both;"></div>

<?php
  if (osc_count_shipping_modules() > 0) {
?>

  <h2><?php echo TABLE_HEADING_SHIPPING_METHOD; ?></h2>

<?php
    if (sizeof($quotes) > 1 && sizeof($quotes[0]) > 1) {
?>

  <div class="contentText">
    <div style="float: right;">
      <?php echo '<strong>' . TITLE_PLEASE_SELECT . '</strong>'; ?>
    </div>

    <?php echo TEXT_CHOOSE_SHIPPING_METHOD; ?>
  </div>

<?php
    } elseif ($free_shipping == false) {
?>

  <div class="contentText">
    <?php echo TEXT_ENTER_SHIPPING_INFORMATION; ?>
  </div>

<?php
    }
?>

  <div class="contentText">
    <table border="0" width="100%" cellspacing="0" cellpadding="2">

<?php
    if ($free_shipping == true) {
?>

      <tr>
        <td><strong><?php echo FREE_SHIPPING_TITLE; ?></strong>&nbsp;<?php echo $quotes[$i]['icon']; ?></td>
      </tr>
      <tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, 0)">
        <td style="padding-left: 15px;"><?php echo sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) . osc_draw_hidden_field('shipping', 'free_free'); ?></td>
      </tr>

<?php
    } else {
      $radio_buttons = 0;
      for ($i=0, $n=sizeof($quotes); $i<$n; $i++) {
?>

      <tr>
        <td colspan="3"><strong><?php echo $quotes[$i]['module']; ?></strong>&nbsp;<?php if (isset($quotes[$i]['icon']) && osc_not_null($quotes[$i]['icon'])) { echo $quotes[$i]['icon']; } ?></td>
      </tr>

<?php
        if (isset($quotes[$i]['error'])) {
?>

      <tr>
        <td colspan="3"><?php echo $quotes[$i]['error']; ?></td>
      </tr>

<?php
        } else {
          for ($j=0, $n2=sizeof($quotes[$i]['methods']); $j<$n2; $j++) {
// set the radio button to be checked if it is the method chosen
            $checked = (isset($_SESSION['shipping']) && ($quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'] == $_SESSION['shipping']['id']) ? true : false);

            if ( ($checked == true) || ($n == 1 && $n2 == 1) ) {
              echo '      <tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
            } else {
              echo '      <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
            }
?>

        <td width="75%" style="padding-left: 15px;"><?php echo $quotes[$i]['methods'][$j]['title']; ?></td>

<?php
            if ( ($n > 1) || ($n2 > 1) ) {
?>

        <td><?php echo $currencies->format(osc_add_tax($quotes[$i]['methods'][$j]['cost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0))); ?></td>
        <td align="right"><?php echo osc_draw_radio_field('shipping', $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'], $checked); ?></td>

<?php
            } else {
?>

        <td align="right" colspan="2"><?php echo $currencies->format(osc_add_tax($quotes[$i]['methods'][$j]['cost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0))) . osc_draw_hidden_field('shipping', $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id']); ?></td>

<?php
            }
?>

      </tr>

<?php
            $radio_buttons++;
          }
        }
      }
    }
?>

    </table>
  </div>

<?php
  }
?>

  <h2><?php echo TABLE_HEADING_COMMENTS; ?></h2>

  <div class="contentText">
    <?php echo osc_draw_textarea_field('comments', 'soft', '60', '5', isset($_SESSION['comments']) ? $_SESSION['comments'] : ''); ?>
  </div>

  <div class="contentText">
    <div style="float: left; width: 60%; padding-top: 5px; padding-left: 15%;">
      <div class="progress">
        <div class="bar" style="width: 33%;"></div>
      </div>

      <table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
          <td align="center" width="33%" class="checkoutBarCurrent"><?php echo CHECKOUT_BAR_DELIVERY; ?></td>
          <td align="center" width="33%" class="checkoutBarTo"><?php echo CHECKOUT_BAR_PAYMENT; ?></td>
          <td align="center" width="33%" class="checkoutBarTo"><?php echo CHECKOUT_BAR_CONFIRMATION; ?></td>
        </tr>
      </table>
    </div>

    <div style="float: right;"><?php echo osc_draw_button(IMAGE_BUTTON_CONTINUE, 'ok-sign', null, 'success'); ?></div>
  </div>
</div>

</form>
