<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
  $directory_array = array();
  if ($dir = @dir(DIR_FS_CATALOG_MODULES . 'action_recorder/')) {
    while ($file = $dir->read()) {
      if (!is_dir(DIR_FS_CATALOG_MODULES . 'action_recorder/' . $file)) {
        if (substr($file, strrpos($file, '.')) == $file_extension) {
          $directory_array[] = $file;
        }
      }
    }
    sort($directory_array);
    $dir->close();
  }

  for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
    $file = $directory_array[$i];

    if (file_exists(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/action_recorder/' . $file)) {
      include(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/action_recorder/' . $file);
    }

    include(DIR_FS_CATALOG_MODULES . 'action_recorder/' . $file);

    $class = substr($file, 0, strrpos($file, '.'));
    if (tep_class_exists($class)) {
      $GLOBALS[$class] = new $class;
    }
  }

  $modules_array = array();
  $modules_list_array = array(array('id' => '', 'text' => TEXT_ALL_MODULES));

  $Qmodules = $OSCOM_Db->get('action_recorder', 'distinct module', null, 'module');

  while ($Qmodules->fetch()) {
    $modules_array[] = $Qmodules->value('module');

    $modules_list_array[] = [
      'id' => $Qmodules->value('module'),
      'text' => (is_object($GLOBALS[$Qmodules->value('module')]) ? $GLOBALS[$Qmodules->value('module')]->title : $Qmodules->value('module'))
    ];
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'expire':
        $expired_entries = 0;

        if (isset($_GET['module']) && in_array($_GET['module'], $modules_array)) {
          if (is_object($GLOBALS[$_GET['module']])) {
            $expired_entries += $GLOBALS[$_GET['module']]->expireEntries();
          } else {
            $expired_entries = $OSCOM_Db->delete('action_recorder', [
              'module' => $_GET['module']
            ]);
          }
        } else {
          foreach ($modules_array as $module) {
            if (is_object($GLOBALS[$module])) {
              $expired_entries += $GLOBALS[$module]->expireEntries();
            }
          }
        }

        $messageStack->add_session(sprintf(SUCCESS_EXPIRED_ENTRIES, $expired_entries), 'success');

        OSCOM::redirect(FILENAME_ACTION_RECORDER);

        break;
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2" height="40">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td align="right"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td class="smallText" align="right">
<?php
  echo HTML::form('search', OSCOM::link(FILENAME_ACTION_RECORDER), 'get', null, ['session_id' => true]);
  echo TEXT_FILTER_SEARCH . ' ' . HTML::inputField('search');
  echo HTML::hiddenField('module') . '</form>';
?>
                </td>
              </tr>
              <tr>
                <td class="smallText" align="right">
<?php
  echo HTML::form('filter', OSCOM::link(FILENAME_ACTION_RECORDER), 'get', null, ['session_id' => true]);
  echo HTML::selectField('module', $modules_list_array, null, 'onchange="this.form.submit();"');
  echo HTML::hiddenField('search') . '</form>';
?>
                </td>
              </tr>
            </table></td>
            <td class="smallText" align="right"><?php echo HTML::button(IMAGE_DELETE, 'fa fa-trash', OSCOM::link(FILENAME_ACTION_RECORDER, 'action=expire' . (isset($_GET['module']) && in_array($_GET['module'], $modules_array) ? '&module=' . $_GET['module'] : '')), 'primary'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" width="20">&nbsp;</td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODULE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMER; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_DATE_ADDED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $filter = array();

  if (isset($_GET['module']) && in_array($_GET['module'], $modules_array)) {
    $filter[] = 'module = :module';
  }

  if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filter[] = 'identifier like :identifier';
  }

  $sql_query = 'select SQL_CALC_FOUND_ROWS * from :table_action_recorder';

  if (!empty($filter)) {
    $sql_query .= ' where ' . implode(' and ', $filter);
  }

  $sql_query .= ' order by date_added desc limit :page_set_offset, :page_set_max_results';

  $Qactions = $OSCOM_Db->prepare($sql_query);

  if (!empty($filter)) {
    if (isset($_GET['module']) && in_array($_GET['module'], $modules_array)) {
      $Qactions->bindValue(':module', $_GET['module']);
    }

    if (isset($_GET['search']) && !empty($_GET['search'])) {
      $Qactions->bindValue(':identifier', '%' . $_GET['search'] . '%');
    }
  }

  $Qactions->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
  $Qactions->execute();

  while ($Qactions->fetch()) {
    $module = $Qactions->value('module');

    $module_title = $Qactions->value('module');
    if (is_object($GLOBALS[$module])) {
      $module_title = $GLOBALS[$module]->title;
    }

    if ((!isset($_GET['aID']) || (isset($_GET['aID']) && ((int)$_GET['aID'] === $Qactions->valueInt('id')))) && !isset($aInfo)) {
      $aInfo_array = array_merge($Qactions->toArray(), array('module_title' => $module_title));
      $aInfo = new objectInfo($aInfo_array);
    }

    if ( (isset($aInfo) && is_object($aInfo)) && ($Qactions->valueInt('id') === (int)$aInfo->id) ) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_ACTION_RECORDER, tep_get_all_get_params(array('aID')) . 'aID=' . $Qactions->valueInt('id')) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent" align="center"><?php echo HTML::image(DIR_WS_IMAGES . 'icons/' . (($Qactions->value('success') == '1') ? 'tick.gif' : 'cross.gif')); ?></td>
                <td class="dataTableContent"><?php echo $module_title; ?></td>
                <td class="dataTableContent"><?php echo $Qactions->valueProtected('user_name') . ' [' . $Qactions->valueInt('user_id') . ']'; ?></td>
                <td class="dataTableContent" align="right"><?php echo tep_datetime_short($Qactions->value('date_added')); ?></td>
                <td class="dataTableContent" align="right"><?php if ( (isset($aInfo) && is_object($aInfo)) && ($Qactions->valueInt('id') === (int)$aInfo->id) ) { echo HTML::image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . OSCOM::link(FILENAME_ACTION_RECORDER, tep_get_all_get_params(array('aID')) . 'aID=' . $Qactions->valueInt('id')) . '">' . HTML::image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qactions->getPageSetLabel(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></td>
                    <td class="smallText" align="right"><?php echo $Qactions->getPageSetLinks((isset($_GET['module']) && in_array($_GET['module'], $modules_array) && is_object($GLOBALS[$_GET['module']]) ? 'module=' . $_GET['module'] : null) . '&' . (isset($_GET['search']) && !empty($_GET['search']) ? 'search=' . $_GET['search'] : null)); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    default:
      if (isset($aInfo) && is_object($aInfo)) {
        $heading[] = array('text' => '<strong>' . $aInfo->module_title . '</strong>');

        $contents[] = array('text' => TEXT_INFO_IDENTIFIER . '<br /><br />' . (!empty($aInfo->identifier) ? '<a href="' . OSCOM::link(FILENAME_ACTION_RECORDER, 'search=' . $aInfo->identifier) . '"><u>' . HTML::outputProtected($aInfo->identifier) . '</u></a>': '(empty)'));
        $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . tep_datetime_short($aInfo->date_added));
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
