<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  $xx_mins_ago = (time() - 900);

  require('includes/application_top.php');

  require('includes/classes/currencies.php');
  $currencies = new currencies();

// remove entries that have expired
  $Qclean = $OSCOM_Db->prepare('delete from :table_whos_online where time_last_click < :last_click');
  $Qclean->bindValue(':last_click', $xx_mins_ago);
  $Qclean->execute();

  if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
  }

  require('includes/template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ONLINE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_CUSTOMER_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_FULL_NAME; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_IP_ADDRESS; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ENTRY_TIME; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_LAST_CLICK; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LAST_PAGE_URL; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $Qonline = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS customer_id, full_name, ip_address, time_entry, time_last_click, last_page_url, session_id from :table_whos_online order by time_last_click desc limit :page_set_offset, :page_set_max_results');
  $Qonline->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
  $Qonline->execute();

  while ($Qonline->fetch()) {
    $time_online = (time() - $Qonline->value('time_entry'));

    if ((!isset($_GET['info']) || (isset($_GET['info']) && ($_GET['info'] == $Qonline->value('session_id')))) && !isset($info)) {
      $info = new ObjectInfo($Qonline->toArray());
    }

    if (isset($info) && ($Qonline->value('session_id') == $info->session_id)) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_WHOS_ONLINE, 'page=' . $_GET['page'] . '&info=' . $Qonline->value('session_id')) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo gmdate('H:i:s', $time_online); ?></td>
                <td class="dataTableContent" align="center"><?php echo $Qonline->valueInt('customer_id'); ?></td>
                <td class="dataTableContent"><?php echo $Qonline->valueProtected('full_name'); ?></td>
                <td class="dataTableContent" align="center"><?php echo $Qonline->value('ip_address'); ?></td>
                <td class="dataTableContent"><?php echo date('H:i:s', $Qonline->value('time_entry')); ?></td>
                <td class="dataTableContent" align="center"><?php echo date('H:i:s', $Qonline->value('time_last_click')); ?></td>
                <td class="dataTableContent"><?php if (preg_match('/^(.*)osCsid=[A-Z0-9,-]+[&]*(.*)/i', $Qonline->value('last_page_url'), $array)) { echo $array[1] . $array[2]; } else { echo $Qonline->value('last_page_url'); } ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($info) && is_object($info) && ($Qonline->value('session_id') == $info->session_id)) { echo HTML::image(OSCOM::linkImage('icon_arrow_right.gif'), ''); } else { echo '<a href="' . OSCOM::link(FILENAME_WHOS_ONLINE, 'page=' . $_GET['page'] . '&info=' . $Qonline->value('session_id')) . '">' . HTML::image(OSCOM::linkImage('icon_info.gif'), IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="9"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qonline->getPageSetLabel(TEXT_DISPLAY_NUMBER_OF_CUSTOMERS); ?></td>
                    <td class="smallText" align="right"><?php echo $Qonline->getPageSetLinks(); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  if (isset($info)) {
    $heading[] = array('text' => '<strong>' . TABLE_HEADING_SHOPPING_CART . '</strong>');

    if ( $info->customer_id > 0 ) {
      $Qproducts = $OSCOM_Db->get([
        'customers_basket cb',
        'products_description pd'
      ], [
        'cb.customers_basket_quantity',
        'cb.products_id',
        'pd.products_name'
      ], [
        'cb.customers_id' => (int)$info->customer_id,
        'cb.products_id' => [
          'rel' => 'pd.products_id'
        ],
        'pd.language_id' => $_SESSION['languages_id']
      ]);

      if ($Qproducts->fetch() !== false) {
        $shoppingCart = new shoppingCart();

        do {
          $contents[] = [
            'text' => $Qproducts->valueInt('customers_basket_quantity') . ' x ' . $Qproducts->value('products_name')
          ];

          $attributes = [];

          if (strpos($Qproducts->value('products_id'), '{') !== false) {
            $combos = [];
            preg_match_all('/(\{[0-9]+\}[0-9]+){1}/', $Qproducts->value('products_id'), $combos);

            foreach ($combos[0] as $combo) {
              $att = [];
              preg_match('/\{([0-9]+)\}([0-9]+)/', $combo, $att);

              $attributes[$att[1]] = $att[2];
            }
          }

          $shoppingCart->add_cart(tep_get_prid($Qproducts->value('products_id')), $Qproducts->valueInt('customers_basket_quantity'), $attributes);
        } while ($Qproducts->fetch());

        $contents[] = array('align' => 'right', 'text'  => TEXT_SHOPPING_CART_SUBTOTAL . ' ' . $currencies->format($shoppingCart->show_total()));
      } else {
        $contents[] = array('text' => '&nbsp;');
      }
    } else {
      $contents[] = array('text' => 'N/A');
    }
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
