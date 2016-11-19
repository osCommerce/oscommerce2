<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\DateTime;
  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if ( ($_GET['flag'] == '0') || ($_GET['flag'] == '1') ) {
          if (isset($_GET['rID'])) {
            tep_set_review_status($_GET['rID'], $_GET['flag']);
          }
        }

        OSCOM::redirect(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $_GET['rID']);
        break;
      case 'update':
        $reviews_id = HTML::sanitize($_GET['rID']);
        $reviews_rating = HTML::sanitize($_POST['reviews_rating']);
        $reviews_text = HTML::sanitize($_POST['reviews_text']);
        $reviews_status = HTML::sanitize($_POST['reviews_status']);

        $OSCOM_Db->save('reviews', [
          'reviews_rating' => $reviews_rating,
          'reviews_status' => $reviews_status,
          'last_modified' => 'now()'
        ], [
          'reviews_id' => (int)$reviews_id
        ]);

        $OSCOM_Db->save('reviews_description', ['reviews_text' => $reviews_text], ['reviews_id' => (int)$reviews_id]);

        OSCOM::redirect(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $reviews_id);
        break;
      case 'deleteconfirm':
        $reviews_id = HTML::sanitize($_GET['rID']);

        $OSCOM_Db->delete('reviews', ['reviews_id' => (int)$reviews_id]);
        $OSCOM_Db->delete('reviews_description', ['reviews_id' => (int)$reviews_id]);

        OSCOM::redirect(FILENAME_REVIEWS, 'page=' . $_GET['page']);
        break;
    }
  }

  require($oscTemplate->getFile('template_top.php'));
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo OSCOM::getDef('heading_title'); ?></td>
          </tr>
        </table></td>
      </tr>
<?php
  if (($action == 'edit') || ($action == 'preview')) {
    $rID = HTML::sanitize($_GET['rID']);

    $Qreviews = $OSCOM_Db->get([
      'reviews r',
      'reviews_description rd'
    ], [
      'r.reviews_id',
      'r.products_id',
      'r.customers_name',
      'r.date_added',
      'r.last_modified',
      'r.reviews_read',
      'rd.reviews_text',
      'r.reviews_rating',
      'r.reviews_status'
    ], [
      'r.reviews_id' => [
        'val' => (int)$rID,
        'ref' => 'rd.reviews_id'
      ]
    ]);

    $Qproducts = $OSCOM_Db->get([
      'products p',
      'products_description pd'
    ], [
      'pd.products_name',
      'p.products_image',
    ], [
      'p.products_id' => [
        'val' => $Qreviews->valueInt('products_id'),
        'ref' => 'pd.products_id'
      ],
      'pd.language_id' => $OSCOM_Language->getId()
    ]);

    $rInfo_array = array_merge($Qreviews->toArray(), $Qproducts->toArray());
    $rInfo = new objectInfo($rInfo_array);

    if ($action == 'edit') {
      if (!isset($rInfo->reviews_status)) $rInfo->reviews_status = '1';
      switch ($rInfo->reviews_status) {
        case '0': $in_status = false; $out_status = true; break;
        case '1':
        default: $in_status = true; $out_status = false;
      }
?>
      <tr><?php echo HTML::form('review', OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $_GET['rID'] . '&action=preview')); ?>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="main" valign="top"><strong><?php echo OSCOM::getDef('entry_product'); ?></strong> <?php echo $rInfo->products_name; ?><br /><strong><?php echo OSCOM::getDef('entry_from'); ?></strong> <?php echo $rInfo->customers_name; ?><br /><br /><strong><?php echo OSCOM::getDef('entry_date'); ?></strong> <?php echo DateTime::toShort($rInfo->date_added); ?></td>
            <td class="main" align="right" valign="top"><?php echo HTML::image(OSCOM::linkImage('Shop/' . $rInfo->products_image), $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"'); ?></td>
          </tr>
          <tr>
            <td class="main" colspan="2"><strong><?php echo OSCOM::getDef('text_info_review_status'); ?></strong> <?php echo HTML::radioField('reviews_status', '1', $in_status) . '&nbsp;' . OSCOM::getDef('text_review_published') . '&nbsp;' . HTML::radioField('reviews_status', '0', $out_status) . '&nbsp;' . OSCOM::getDef('text_review_not_published'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td class="main" valign="top"><strong><?php echo OSCOM::getDef('entry_review'); ?></strong><br /><br /><?php echo HTML::textareaField('reviews_text', '60', '15', $rInfo->reviews_text); ?></td>
          </tr>
          <tr>
            <td class="smallText" align="right"><?php echo OSCOM::getDef('entry_review_text'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td class="main"><strong><?php echo OSCOM::getDef('entry_rating'); ?></strong>&nbsp;<?php echo OSCOM::getDef('text_bad'); ?>&nbsp;<?php for ($i=1; $i<=5; $i++) echo HTML::radioField('reviews_rating', $i, $rInfo->reviews_rating == $i) . '&nbsp;'; echo OSCOM::getDef('text_good'); ?></td>
      </tr>
      <tr>
        <td align="right" class="smallText"><?php echo HTML::button(OSCOM::getDef('image_preview'), 'fa fa-file-o') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $_GET['rID'])); ?></td>
      </form></tr>
<?php
    } else {
      if (tep_not_null($_POST)) {
        $rInfo->reviews_rating = HTML::sanitize($_POST['reviews_rating']);
        $rInfo->reviews_text = HTML::sanitize($_POST['reviews_text']);
        $rInfo->reviews_status = HTML::sanitize($_POST['reviews_status']);
      }
?>
      <tr><?php if (tep_not_null($_POST)) { echo HTML::form('update', OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $_GET['rID'] . '&action=update')); } ?>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="main" valign="top"><strong><?php echo OSCOM::getDef('entry_product'); ?></strong> <?php echo $rInfo->products_name; ?><br /><strong><?php echo OSCOM::getDef('entry_from'); ?></strong> <?php echo $rInfo->customers_name; ?><br /><br /><strong><?php echo OSCOM::getDef('entry_date'); ?></strong> <?php echo DateTime::toShort($rInfo->date_added); ?></td>
            <td class="main" align="right" valign="top"><?php echo HTML::image(OSCOM::linkImage('Shop/' . $rInfo->products_image), $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"'); ?></td>
          </tr>
        </table>
      </tr>
      <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top" class="main"><strong><?php echo OSCOM::getDef('entry_review'); ?></strong><br /><br /><?php echo nl2br(HTML::output(tep_break_string($rInfo->reviews_text, 15))); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td class="main"><strong><?php echo OSCOM::getDef('entry_rating'); ?></strong>&nbsp;<?php echo HTML::image(OSCOM::linkImage('Shop/stars_' . $rInfo->reviews_rating . '.gif'), OSCOM::getDef('text_of_5_stars', ['reviews_rating' => $rInfo->reviews_rating])); ?>&nbsp;<small>[<?php echo OSCOM::getDef('text_of_5_stars', ['reviews_rating' => $rInfo->reviews_rating]); ?>]</small></td>
      </tr>
<?php
      if (tep_not_null($_POST)) {
        echo HTML::hiddenField('reviews_rating', $rInfo->reviews_rating);
        echo HTML::hiddenField('reviews_text', $rInfo->reviews_text);
        echo HTML::hiddenField('reviews_status', $rInfo->reviews_status);
?>
      <tr>
        <td align="right" class="smallText"><?php echo HTML::button(OSCOM::getDef('image_save'), 'fa fa-save') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id)); ?></td>
      </form></tr>
<?php
      } else {
        if (isset($_GET['origin'])) {
          $back_url = $_GET['origin'];
          $back_url_params = '';
        } else {
          $back_url = FILENAME_REVIEWS;
          $back_url_params = 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id;
        }
?>
      <tr>
        <td align="right" class="smallText"><?php echo HTML::button(OSCOM::getDef('image_back'), 'fa fa-chevron-left', OSCOM::link($back_url, $back_url_params)); ?></td>
      </tr>
<?php
      }
    }
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_products'); ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo OSCOM::getDef('table_heading_rating'); ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo OSCOM::getDef('table_heading_date_added'); ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo OSCOM::getDef('table_heading_status'); ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo OSCOM::getDef('table_heading_action'); ?>&nbsp;</td>
              </tr>
<?php
    $Qreviews = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS reviews_id, products_id, date_added, last_modified, reviews_rating, reviews_status from :table_reviews order by date_added desc limit :page_set_offset, :page_set_max_results');
    $Qreviews->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
    $Qreviews->execute();

    while ($Qreviews->fetch()) {
      if ((!isset($_GET['rID']) || (isset($_GET['rID']) && ((int)$_GET['rID'] === $Qreviews->valueInt('reviews_id')))) && !isset($rInfo)) {
        $Qextra = $OSCOM_Db->get([
          'reviews r',
          'reviews_description rd'
        ], [
          'r.reviews_read',
          'r.customers_name',
          'length(rd.reviews_text) as reviews_text_size'
        ], [
          'r.reviews_id' => [
            'val' => $Qreviews->valueInt('reviews_id'),
            'ref' => 'rd.reviews_id'
          ]
        ]);

        $Qproducts = $OSCOM_Db->get([
          'products p',
          'products_description pd'
        ], [
          'pd.products_name',
          'p.products_image',
        ], [
          'p.products_id' => [
            'val' => $Qreviews->valueInt('products_id'),
            'ref' => 'pd.products_id'
          ],
          'pd.language_id' => $OSCOM_Language->getId()
        ]);

        $Qaverage = $OSCOM_Db->get('reviews', [
          '(avg(reviews_rating) / 5 * 100) as average_rating'
        ], [
          'products_id' => $Qreviews->valueInt('products_id')
        ]);

        $rInfo_array = array_merge($Qreviews->toArray(), $Qextra->toArray(), $Qproducts->toArray(), $Qaverage->toArray());
        $rInfo = new objectInfo($rInfo_array);
      }

      if (isset($rInfo) && is_object($rInfo) && ($Qreviews->valueInt('reviews_id') === (int)$rInfo->reviews_id) ) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id . '&action=preview') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $Qreviews->valueInt('reviews_id')) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $Qreviews->valueInt('reviews_id') . '&action=preview') . '">' . HTML::image(OSCOM::linkImage('icons/preview.gif'), OSCOM::getDef('icon_preview')) . '</a>&nbsp;' . tep_get_products_name($Qreviews->valueInt('products_id')); ?></td>
                <td class="dataTableContent" align="right"><?php echo HTML::image(OSCOM::linkImage('Shop/stars_' . $Qreviews->valueInt('reviews_rating') . '.gif')); ?></td>
                <td class="dataTableContent" align="right"><?php echo DateTime::toShort($Qreviews->value('date_added')); ?></td>
                <td class="dataTableContent" align="center">
<?php
      if ($Qreviews->valueInt('reviews_status') === 1) {
        echo HTML::image(OSCOM::linkImage('icon_status_green.gif'), OSCOM::getDef('image_icon_status_green'), 10, 10) . '&nbsp;&nbsp;<a href="' . OSCOM::link(FILENAME_REVIEWS, 'action=setflag&flag=0&rID=' . $Qreviews->valueInt('reviews_id') . '&page=' . $_GET['page']) . '">' . HTML::image(OSCOM::linkImage('icon_status_red_light.gif'), OSCOM::getDef('image_icon_status_red_light'), 10, 10) . '</a>';
      } else {
        echo '<a href="' . OSCOM::link(FILENAME_REVIEWS, 'action=setflag&flag=1&rID=' . $Qreviews->valueInt('reviews_id') . '&page=' . $_GET['page']) . '">' . HTML::image(OSCOM::linkImage('icon_status_green_light.gif'), OSCOM::getDef('image_icon_status_green_light'), 10, 10) . '</a>&nbsp;&nbsp;' . HTML::image(OSCOM::linkImage('icon_status_red.gif'), OSCOM::getDef('image_icon_status_red'), 10, 10);
      }
?></td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($rInfo)) && ($Qreviews->valueInt('reviews_id') === (int)$rInfo->reviews_id) ) { echo HTML::image(OSCOM::linkImage('icon_arrow_right.gif')); } else { echo '<a href="' . OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $Qreviews->valueInt('reviews_id')) . '">' . HTML::image(OSCOM::linkImage('icon_info.gif'), OSCOM::getDef('image_icon_info')) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qreviews->getPageSetLabel(OSCOM::getDef('text_display_number_of_reviews')); ?></td>
                    <td class="smallText" align="right"><?php echo $Qreviews->getPageSetLinks(); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
    $heading = array();
    $contents = array();

    switch ($action) {
      case 'delete':
        $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_delete_review') . '</strong>');

        $contents = array('form' => HTML::form('reviews', OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id . '&action=deleteconfirm')));
        $contents[] = array('text' => OSCOM::getDef('text_info_delete_review_intro'));
        $contents[] = array('text' => '<br /><strong>' . $rInfo->products_name . '</strong>');
        $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id)));
        break;
      default:
      if (isset($rInfo) && is_object($rInfo)) {
        $heading[] = array('text' => '<strong>' . $rInfo->products_name . '</strong>');

        $contents[] = array('align' => 'center', 'text' => HTML::button(OSCOM::getDef('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id . '&action=edit')) . HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_REVIEWS, 'page=' . $_GET['page'] . '&rID=' . $rInfo->reviews_id . '&action=delete')));
        $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_date_added') . ' ' . DateTime::toShort($rInfo->date_added));
        if (tep_not_null($rInfo->last_modified)) $contents[] = array('text' => OSCOM::getDef('text_info_last_modified') . ' ' . DateTime::toShort($rInfo->last_modified));
        $contents[] = array('text' => '<br />' . tep_info_image($rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));
        $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_review_author') . ' ' . $rInfo->customers_name);
        $contents[] = array('text' => OSCOM::getDef('text_info_review_rating') . ' ' . HTML::image(OSCOM::linkImage('Shop/stars_' . $rInfo->reviews_rating . '.gif')));
        $contents[] = array('text' => OSCOM::getDef('text_info_review_read') . ' ' . $rInfo->reviews_read);
        $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_review_size') . ' ' . $rInfo->reviews_text_size . ' bytes');
        $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_products_average_rating') . ' ' . number_format($rInfo->average_rating, 2) . '%');
      }
        break;
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
<?php
  }
?>
    </table>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
