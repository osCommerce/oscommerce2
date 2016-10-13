<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_CLASSES; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
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
                <td class="dataTableContent" align="right"><?php if (isset($tcInfo) && is_object($tcInfo) && ($Qclasses->valueInt('tax_class_id') === (int)$tcInfo->tax_class_id)) { echo HTML::image(OSCOM::linkImage('icon_arrow_right.gif'), ''); } else { echo '<a href="' . OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $Qclasses->valueInt('tax_class_id')) . '">' . HTML::image(OSCOM::linkImage('icon_info.gif'), IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qclasses->getPageSetLabel(TEXT_DISPLAY_NUMBER_OF_TAX_CLASSES); ?></td>
                    <td class="smallText" align="right"><?php echo $Qclasses->getPageSetLinks(); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td class="smallText" colspan="2" align="right"><?php echo HTML::button(IMAGE_NEW_TAX_CLASS, 'fa fa-plus', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&action=new')); ?></td>
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
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_TAX_CLASS . '</strong>');

      $contents = array('form' => HTML::form('classes', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&action=insert')));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_CLASS_TITLE . '<br />' . HTML::inputField('tax_class_title'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_CLASS_DESCRIPTION . '<br />' . HTML::inputField('tax_class_description'));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_SAVE, 'fa fa-save', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'])));
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_EDIT_TAX_CLASS . '</strong>');

      $contents = array('form' => HTML::form('classes', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=save')));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_CLASS_TITLE . '<br />' . HTML::inputField('tax_class_title', $tcInfo->tax_class_title));
      $contents[] = array('text' => '<br />' . TEXT_INFO_CLASS_DESCRIPTION . '<br />' . HTML::inputField('tax_class_description', $tcInfo->tax_class_description));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_SAVE, 'fa fa-save', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id)));
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_DELETE_TAX_CLASS . '</strong>');

      $contents = array('form' => HTML::form('classes', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=deleteconfirm')));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><strong>' . $tcInfo->tax_class_title . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_DELETE, 'fa fa-trash', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id)));
      break;
    default:
      if (isset($tcInfo) && is_object($tcInfo)) {
        $heading[] = array('text' => '<strong>' . $tcInfo->tax_class_title . '</strong>');

        $contents[] = array('align' => 'center', 'text' => HTML::button(IMAGE_EDIT, 'fa fa-edit', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=edit')) . HTML::button(IMAGE_DELETE, 'fa fa-trash', OSCOM::link(FILENAME_TAX_CLASSES, 'page=' . $_GET['page'] . '&tID=' . $tcInfo->tax_class_id . '&action=delete')));
        $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($tcInfo->date_added));
        $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($tcInfo->last_modified));
        $contents[] = array('text' => '<br />' . TEXT_INFO_CLASS_DESCRIPTION . '<br />' . $tcInfo->tax_class_description);
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
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
