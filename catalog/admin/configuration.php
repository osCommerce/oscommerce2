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

  $gID = (isset($_GET['gID'])) ? $_GET['gID'] : 1;

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'save':
        $configuration_value = HTML::sanitize($_POST['configuration_value']);
        $cID = HTML::sanitize($_GET['cID']);

        $OSCOM_Db->save('configuration', [
          'configuration_value' => $configuration_value,
          'last_modified' => 'now()'
        ], [
          'configuration_id' => (int)$cID
        ]);

        OSCOM::redirect(FILENAME_CONFIGURATION, 'gID=' . $gID . '&cID=' . $cID);
        break;
    }
  }

  $Qgroup = $OSCOM_Db->get('configuration_group', 'configuration_group_title', ['configuration_group_id' => (int)$gID]);

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo $Qgroup->value('configuration_group_title'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_TITLE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CONFIGURATION_VALUE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $Qcfg = $OSCOM_Db->get('configuration', [
    'configuration_id',
    'configuration_title',
    'configuration_value',
    'use_function'
  ], [
    'configuration_group_id' => (int)$gID
  ], 'sort_order');

  while ($Qcfg->fetch()) {
    if ($Qcfg->hasValue('use_function') && tep_not_null($Qcfg->value('use_function'))) {
      $use_function = $Qcfg->value('use_function');
      if (preg_match('/->/', $use_function)) {
        $class_method = explode('->', $use_function);
        if (!is_object(${$class_method[0]})) {
          include(DIR_WS_CLASSES . $class_method[0] . '.php');
          ${$class_method[0]} = new $class_method[0]();
        }
        $cfgValue = tep_call_function($class_method[1], $Qcfg->value('configuration_value'), ${$class_method[0]});
      } else {
        $cfgValue = tep_call_function($use_function, $Qcfg->value('configuration_value'));
      }
    } else {
      $cfgValue = $Qcfg->value('configuration_value');
    }

    if ((!isset($_GET['cID']) || (isset($_GET['cID']) && ((int)$_GET['cID'] === $Qcfg->valueInt('configuration_id')))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
      $Qextra = $OSCOM_Db->get('configuration', [
        'configuration_key',
        'configuration_description',
        'date_added',
        'last_modified',
        'use_function',
        'set_function'
      ], [
        'configuration_id' => $Qcfg->valueInt('configuration_id')
      ]);

      $cInfo_array = array_merge($Qcfg->toArray(), $Qextra->toArray());
      $cInfo = new objectInfo($cInfo_array);
    }

    if ( (isset($cInfo) && is_object($cInfo)) && ($Qcfg->valueInt('configuration_id') === (int)$cInfo->configuration_id) ) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_CONFIGURATION, 'gID=' . $gID . '&cID=' . $cInfo->configuration_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_CONFIGURATION, 'gID=' . $gID . '&cID=' . $Qcfg->valueInt('configuration_id')) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $Qcfg->value('configuration_title'); ?></td>
                <td class="dataTableContent"><?php echo htmlspecialchars($cfgValue); ?></td>
                <td class="dataTableContent" align="right"><?php if ( (isset($cInfo) && is_object($cInfo)) && ($Qcfg->valueInt('configuration_id') === (int)$cInfo->configuration_id) ) { echo HTML::image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . OSCOM::link(FILENAME_CONFIGURATION, 'gID=' . $gID . '&cID=' . $Qcfg->valueInt('configuration_id')) . '">' . HTML::image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'edit':
      $heading[] = array('text' => '<strong>' . $cInfo->configuration_title . '</strong>');

      if ($cInfo->set_function) {
        eval('$value_field = ' . $cInfo->set_function . '"' . htmlspecialchars($cInfo->configuration_value) . '");');
      } else {
        $value_field = HTML::inputField('configuration_value', $cInfo->configuration_value);
      }

      $contents = array('form' => HTML::form('configuration', OSCOM::link(FILENAME_CONFIGURATION, 'gID=' . $gID . '&cID=' . $cInfo->configuration_id . '&action=save')));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br /><strong>' . $cInfo->configuration_title . '</strong><br />' . $cInfo->configuration_description . '<br />' . $value_field);
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_SAVE, 'fa fa-save', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_CONFIGURATION, 'gID=' . $gID . '&cID=' . $cInfo->configuration_id)));
      break;
    default:
      if (isset($cInfo) && is_object($cInfo)) {
        $heading[] = array('text' => '<strong>' . $cInfo->configuration_title . '</strong>');

        $contents[] = array('align' => 'center', 'text' => HTML::button(IMAGE_EDIT, 'fa fa-edit', OSCOM::link(FILENAME_CONFIGURATION, 'gID=' . $gID . '&cID=' . $cInfo->configuration_id . '&action=edit')));
        $contents[] = array('text' => '<br />' . $cInfo->configuration_description);
        $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($cInfo->date_added));
        if (tep_not_null($cInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($cInfo->last_modified));
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
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
