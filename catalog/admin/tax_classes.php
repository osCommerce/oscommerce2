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
      case 'insert':
        $tax_class_title = HTML::sanitize($_POST['tax_class_title']);
        $tax_class_description = HTML::sanitize($_POST['tax_class_description']);

        $OSCOM_Db->save('tax_class', [
          'tax_class_title' => $tax_class_title,
          'tax_class_description' => $tax_class_description,
          'date_added' => 'now()'
        ]);

        OSCOM::redirect(FILENAME_TAX_CLASSES);
        break;
      case 'save':
        $tax_class_id = HTML::sanitize($_GET['tID']);
        $tax_class_title = HTML::sanitize($_POST['tax_class_title']);
        $tax_class_description = HTML::sanitize($_POST['tax_class_description']);

        $OSCOM_Db->save('tax_class', [
          'tax_class_title' => $tax_class_title,
          'tax_class_description' => $tax_class_description,
          'last_modified' => 'now()'
        ], [
          'tax_class_id' => (int)$tax_class_id
        ]);

        OSCOM::redirect(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tax_class_id);
        break;
      case 'deleteconfirm':
        $tax_class_id = HTML::sanitize($_GET['tID']);

        $OSCOM_Db->delete('tax_class', ['tax_class_id' => (int)$tax_class_id]);

        OSCOM::redirect(FILENAME_TAX_CLASSES, 'page=' . $_GET['page']);
        break;
    }
  }

  require($oscTemplate->getFile('template_top.php'));
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo OSCOM::getDef('heading_title'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_tax_classes'); ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo OSCOM::getDef('table_heading_action'); ?>&nbsp;</td>
              </tr>
<?php
  $Qclasses = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS tax_class_id, tax_class_title, tax_class_description, last_modified, date_added from :table_tax_class order by tax_class_title limit :page_set_offset, :page_set_max_results');
  $Qclasses->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
  $Qclasses->execute();

  while ($Qclasses->fetch()) {
    if ((!isset($_GET['tID']) || (isset($_GET['tID']) && ((int)$_GET['tID'] === $Qclasses->valueInt('tax_class_id')))) && !isset($tcInfo) && (substr($action, 0, 3) != 'new')) {
      $tcInfo = new objectInfo($Qclasses->toArray());
    }

    if (isset($tcInfo) && is_object($tcInfo) && ($Qclasses->valueInt('tax_class_id') === (int)$tcInfo->tax_class_id)) {
      echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo'              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $Qclasses->valueInt('tax_class_id')) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $Qclasses->value('tax_class_title'); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($tcInfo) && is_object($tcInfo) && ($Qclasses->valueInt('tax_class_id') === (int)$tcInfo->tax_class_id)) { echo HTML::image(OSCOM::linkImage('icon_arrow_right.gif'), ''); } else { echo '<a href="' . OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $Qclasses->valueInt('tax_class_id')) . '">' . HTML::image(OSCOM::linkImage('icon_info.gif'), OSCOM::getDef('image_icon_info')) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qclasses->getPageSetLabel(OSCOM::getDef('text_display_number_of_tax_classes')); ?></td>
                    <td class="smallText" align="right"><?php echo $Qclasses->getPageSetLinks(); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td class="smallText" colspan="2" align="right"><?php echo HTML::button(OSCOM::getDef('image_new_tax_class'), 'fa fa-plus', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&action=new')); ?></td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_new_tax_class') . '</strong>');

      $contents = array('form' => HTML::form('classes', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&action=insert')));
      $contents[] = array('text' => OSCOM::getDef('text_info_insert_intro'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_class_title') . '<br />' . HTML::inputField('tax_class_title'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_class_description') . '<br />' . HTML::inputField('tax_class_description'));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_save'), 'fa fa-save') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'])));
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_edit_tax_class') . '</strong>');

      $contents = array('form' => HTML::form('classes', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=save')));
      $contents[] = array('text' => OSCOM::getDef('text_info_edit_intro'));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_class_title') . '<br />' . HTML::inputField('tax_class_title', $tcInfo->tax_class_title));
      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_class_description') . '<br />' . HTML::inputField('tax_class_description', $tcInfo->tax_class_description));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_save'), 'fa fa-save') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id)));
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_delete_tax_class') . '</strong>');

      $contents = array('form' => HTML::form('classes', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=deleteconfirm')));
      $contents[] = array('text' => OSCOM::getDef('text_info_delete_intro'));
      $contents[] = array('text' => '<br /><strong>' . $tcInfo->tax_class_title . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id)));
      break;
    default:
      if (isset($tcInfo) && is_object($tcInfo)) {
        $heading[] = array('text' => '<strong>' . $tcInfo->tax_class_title . '</strong>');

        $contents[] = array('align' => 'center', 'text' => HTML::button(OSCOM::getDef('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=edit')) . HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=delete')));
        $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_date_added') . ' ' . DateTime::toShort($tcInfo->date_added));
        $contents[] = array('text' => '' . OSCOM::getDef('text_info_last_modified') . ' ' . DateTime::toShort($tcInfo->last_modified));
        $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_class_description') . '<br />' . $tcInfo->tax_class_description);
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
    </table>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
