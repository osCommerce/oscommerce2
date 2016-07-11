<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\Apps;
  use OSC\OM\HTML;
  use OSC\OM\Registry;

  require('includes/application_top.php');

  $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_CONTENT_INSTALLED' limit 1");
  if (tep_db_num_rows($check_query) < 1) {
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Installed Modules', 'MODULE_CONTENT_INSTALLED', '', 'This is automatically updated. No need to edit.', '6', '0', now())");
    define('MODULE_CONTENT_INSTALLED', '');
  }

  $modules_installed = (tep_not_null(MODULE_CONTENT_INSTALLED) ? explode(';', MODULE_CONTENT_INSTALLED) : array());
  $modules = array('installed' => array(), 'new' => array());

  $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));

  if ($maindir = @dir(DIR_FS_CATALOG_MODULES . 'content/')) {
    while ($group = $maindir->read()) {
      if ( ($group != '.') && ($group != '..') && is_dir(DIR_FS_CATALOG_MODULES . 'content/' . $group)) {
        if ($dir = @dir(DIR_FS_CATALOG_MODULES . 'content/' . $group)) {
          while ($file = $dir->read()) {
            if (!is_dir(DIR_FS_CATALOG_MODULES . 'content/' . $group . '/' . $file)) {
              if (substr($file, strrpos($file, '.')) == $file_extension) {
                $class = substr($file, 0, strrpos($file, '.'));

                if (!tep_class_exists($class)) {
                  if ( file_exists(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/content/' . $group . '/' . $file) ) {
                    include(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/content/' . $group . '/' . $file);
                  }

                  include(DIR_FS_CATALOG_MODULES . 'content/' . $group . '/' . $file);
                }

                if (tep_class_exists($class)) {
                  $module = new $class();

                  if (in_array($group . '/' . $class, $modules_installed)) {
                    $modules['installed'][] = array('code' => $class,
                                                    'title' => $module->title,
                                                    'group' => $group,
                                                    'sort_order' => (int)$module->sort_order);
                  } else {
                    $modules['new'][] = array('code' => $class,
                                              'title' => $module->title,
                                              'group' => $group);
                  }
                }
              }
            }
          }

          $dir->close();
        }
      }
    }

    $maindir->close();

    foreach (Apps::getModules('Content') as $k => $class) {
      $module = new $class();

      if (in_array($k, $modules_installed)) {
        $modules['installed'][] = array('code' => $k,
                                        'title' => $module->title,
                                        'group' => $module->group,
                                        'sort_order' => (int)$module->sort_order);
      } else {
        $modules['new'][] = array('code' => $k,
                                  'title' => $module->title,
                                  'group' => $module->group);
      }
    }

    function _sortContentModulesInstalled($a, $b) {
      return strnatcmp($a['group'] . '-' . (int)$a['sort_order'] . '-' . $a['title'], $b['group'] . '-' . (int)$b['sort_order'] . '-' . $b['title']);
    }

    function _sortContentModuleFiles($a, $b) {
      return strnatcmp($a['group'] . '-' . $a['title'], $b['group'] . '-' . $b['title']);
    }

    usort($modules['installed'], '_sortContentModulesInstalled');
    usort($modules['new'], '_sortContentModuleFiles');
  }

// Update sort order in MODULE_CONTENT_INSTALLED
  $_installed = array();

  foreach ( $modules['installed'] as $m ) {
    if (strpos($m['code'], '\\') !== false) {
      $_installed[] = $m['code'];
    } else {
      $_installed[] = $m['group'] . '/' . $m['code'];
    }
  }

  if ( implode(';', $_installed) != MODULE_CONTENT_INSTALLED ) {
    Registry::get('Db')->save('configuration', ['configuration_value' => implode(';', $_installed), 'last_modified' => 'now()'], ['configuration_key' => 'MODULE_CONTENT_INSTALLED']);
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'save':
        foreach ( $modules['installed'] as $m ) {
          if ( $m['code'] == $_GET['module'] ) {
            foreach ($_POST['configuration'] as $key => $value) {
              $key = tep_db_prepare_input($key);
              $value = tep_db_prepare_input($value);

              tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($value) . "' where configuration_key = '" . tep_db_input($key) . "'");
            }

            break;
          }
        }

        tep_redirect(tep_href_link('modules_content.php', 'module=' . $_GET['module']));

        break;

      case 'install':
        $class = $code = $_GET['module'];

        foreach ( $modules['new'] as $m ) {
          if ( $m['code'] == $code ) {
            if (strpos($code, '\\') !== false) {
              $class = Apps::getModuleClass($code, 'Content');
            }

            $module = new $class();

            $module->install();

            $modules_installed[] = $m['group'] . '/' . $m['code'];

            Registry::get('Db')->save('configuration', ['configuration_value' => implode(';', $modules_installed), 'last_modified' => 'now()'], ['configuration_key' => 'MODULE_CONTENT_INSTALLED']);

            tep_redirect(tep_href_link('modules_content.php', 'module=' . $code . '&action=edit'));
          }
        }

        tep_redirect(tep_href_link('modules_content.php', 'action=list_new&module=' . $code));

        break;

      case 'remove':
        $class = $code = $_GET['module'];

        foreach ( $modules['installed'] as $m ) {
          if ( $m['code'] == $code ) {
            if (strpos($code, '\\') !== false) {
              $class = Apps::getModuleClass($code, 'Content');

              $installed_code = $m['code'];
            } else {
              $installed_code = $m['group'] . '/' . $m['code'];
            }

            $module = new $class();

            $module->remove();

            $modules_installed = explode(';', MODULE_CONTENT_INSTALLED);

            if (in_array($installed_code, $modules_installed)) {
              unset($modules_installed[array_search($installed_code, $modules_installed)]);
            }

            Registry::get('Db')->save('configuration', ['configuration_value' => implode(';', $modules_installed), 'last_modified' => 'now()'], ['configuration_key' => 'MODULE_CONTENT_INSTALLED']);

            tep_redirect(tep_href_link('modules_content.php'));
          }
        }

        tep_redirect(tep_href_link('modules_content.php', 'module=' . $code));

        break;
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
<?php
  if ($action == 'list_new') {
    echo '            <td class="smallText" align="right">' . HTML::button(IMAGE_BACK, 'fa fa-chevron-left', tep_href_link('modules_content.php')) . '</td>';
  } else {
    echo '            <td class="smallText" align="right">' . HTML::button(IMAGE_MODULE_INSTALL . ' (' . count($modules['new']) . ')', 'fa fa-plus', tep_href_link('modules_content.php', 'action=list_new')) . '</td>';
  }
?>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top">
<?php
  if ( $action == 'list_new' ) {
?>
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODULES; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_GROUP; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    foreach ( $modules['new'] as $m ) {
      if (strpos($m['code'], '\\') !== false) {
        $class = Apps::getModuleClass($m['code'], 'Content');

        $module = new $class();
        $module->code = $m['code'];
      } else {
        $module = new $m['code']();
      }

      if ((!isset($_GET['module']) || (isset($_GET['module']) && ($_GET['module'] == $module->code))) && !isset($mInfo)) {
        $module_info = array('code' => $module->code,
                             'title' => $module->title,
                             'description' => $module->description,
                             'signature' => (isset($module->signature) ? $module->signature : null),
                             'api_version' => (isset($module->api_version) ? $module->api_version : null));

        $mInfo = new \ArrayObject($module_info, \ArrayObject::ARRAY_AS_PROPS);
      }

      if (isset($mInfo) && is_object($mInfo) && ($module->code == $mInfo->code) ) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('modules_content.php', 'action=list_new&module=' . addslashes($module->code)) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo $module->title; ?></td>
                <td class="dataTableContent"><?php echo $module->group; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($mInfo) && is_object($mInfo) && ($module->code == $mInfo->code) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link('modules_content.php', 'action=list_new&module=' . $module->code) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
            </table>
<?php
  } else {
?>
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODULES; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_GROUP; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SORT_ORDER; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    foreach ( $modules['installed'] as $m ) {
      if (strpos($m['code'], '\\') !== false) {
        $class = Apps::getModuleClass($m['code'], 'Content');

        $module = new $class();
        $module->code = $m['code'];
      } else {
        $module = new $m['code']();
      }

      if ((!isset($_GET['module']) || (isset($_GET['module']) && ($_GET['module'] == $module->code))) && !isset($mInfo)) {
        $module_info = array('code' => $module->code,
                             'title' => $module->title,
                             'description' => $module->description,
                             'signature' => (isset($module->signature) ? $module->signature : null),
                             'api_version' => (isset($module->api_version) ? $module->api_version : null),
                             'sort_order' => (int)$module->sort_order,
                             'keys' => array());

        foreach ($module->keys() as $key) {
          $key = tep_db_prepare_input($key);

          $key_value_query = tep_db_query("select configuration_title, configuration_value, configuration_description, use_function, set_function from " . TABLE_CONFIGURATION . " where configuration_key = '" . tep_db_input($key) . "'");
          $key_value = tep_db_fetch_array($key_value_query);

          $module_info['keys'][$key] = array('title' => $key_value['configuration_title'],
                                             'value' => $key_value['configuration_value'],
                                             'description' => $key_value['configuration_description'],
                                             'use_function' => $key_value['use_function'],
                                             'set_function' => $key_value['set_function']);
        }

        $mInfo = new \ArrayObject($module_info, \ArrayObject::ARRAY_AS_PROPS);
      }

      if (isset($mInfo) && is_object($mInfo) && ($module->code == $mInfo->code) ) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('modules_content.php', 'module=' . addslashes($module->code)) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo $module->title; ?></td>
                <td class="dataTableContent"><?php echo $module->group; ?></td>
                <td class="dataTableContent"><?php echo $module->sort_order; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($mInfo) && is_object($mInfo) && ($module->code == $mInfo->code) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link('modules_content.php', 'module=' . $module->code) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
            </table>
<?php
  }
?>
            <p class="smallText"><?php echo TEXT_MODULE_DIRECTORY . ' ' . DIR_FS_CATALOG_MODULES . 'content/'; ?></p>
            </td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'edit':
      $keys = '';

      foreach ($mInfo->keys as $key => $value) {
        $keys .= '<strong>' . $value['title'] . '</strong><br />' . $value['description'] . '<br />';

        if ($value['set_function']) {
          eval('$keys .= ' . $value['set_function'] . "'" . $value['value'] . "', '" . $key . "');");
        } else {
          $keys .= HTML::inputField('configuration[' . $key . ']', $value['value']);
        }

        $keys .= '<br /><br />';
      }

      $keys = substr($keys, 0, strrpos($keys, '<br /><br />'));

      $heading[] = array('text' => '<strong>' . $mInfo->title . '</strong>');

      $contents = array('form' => HTML::form('modules', tep_href_link('modules_content.php', 'module=' . $mInfo->code . '&action=save')));
      $contents[] = array('text' => $keys);
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_SAVE, 'fa fa-save', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', tep_href_link('modules_content.php', 'module=' . $mInfo->code)));

      break;

    default:
      if ( isset($mInfo) ) {
        $heading[] = array('text' => '<strong>' . $mInfo->title . '</strong>');

        if ($action == 'list_new') {
          $contents[] = array('align' => 'center', 'text' => HTML::button(IMAGE_MODULE_INSTALL, 'fa fa-plus', tep_href_link('modules_content.php', 'module=' . $mInfo->code . '&action=install')));

          if (isset($mInfo->signature) && (list($scode, $smodule, $sversion, $soscversion) = explode('|', $mInfo->signature))) {
            $contents[] = array('text' => '<br />' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '&nbsp;<strong>' . TEXT_INFO_VERSION . '</strong> ' . $sversion . ' (<a href="http://sig.oscommerce.com/' . $mInfo->signature . '" target="_blank">' . TEXT_INFO_ONLINE_STATUS . '</a>)');
          }

          if (isset($mInfo->api_version)) {
            $contents[] = array('text' => tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '&nbsp;<strong>' . TEXT_INFO_API_VERSION . '</strong> ' . $mInfo->api_version);
          }

          $contents[] = array('text' => '<br />' . $mInfo->description);
        } else {
          $keys = '';

          foreach ($mInfo->keys as $value) {
            $keys .= '<strong>' . $value['title'] . '</strong><br />';

            if ($value['use_function']) {
              $use_function = $value['use_function'];

              if (preg_match('/->/', $use_function)) {
                $class_method = explode('->', $use_function);

                if (!isset(${$class_method[0]}) || !is_object(${$class_method[0]})) {
                  include(DIR_WS_CLASSES . $class_method[0] . '.php');
                  ${$class_method[0]} = new $class_method[0]();
                }

                $keys .= tep_call_function($class_method[1], $value['value'], ${$class_method[0]});
              } else {
                $keys .= tep_call_function($use_function, $value['value']);
              }
            } else {
              $keys .= $value['value'];
            }

            $keys .= '<br /><br />';
          }

          $keys = substr($keys, 0, strrpos($keys, '<br /><br />'));

          $contents[] = array('align' => 'center', 'text' => HTML::button(IMAGE_EDIT, 'fa fa-edit', tep_href_link('modules_content.php', 'module=' . $mInfo->code . '&action=edit')) . HTML::button(IMAGE_MODULE_REMOVE, 'fa fa-minus', tep_href_link('modules_content.php', 'module=' . $mInfo->code . '&action=remove')));

          if (isset($mInfo->signature) && (list($scode, $smodule, $sversion, $soscversion) = explode('|', $mInfo->signature))) {
            $contents[] = array('text' => '<br />' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '&nbsp;<strong>' . TEXT_INFO_VERSION . '</strong> ' . $sversion . ' (<a href="http://sig.oscommerce.com/' . $mInfo->signature . '" target="_blank">' . TEXT_INFO_ONLINE_STATUS . '</a>)');
          }

          if (isset($mInfo->api_version)) {
            $contents[] = array('text' => tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '&nbsp;<strong>' . TEXT_INFO_API_VERSION . '</strong> ' . $mInfo->api_version);
          }

          $contents[] = array('text' => '<br />' . $mInfo->description);
          $contents[] = array('text' => '<br />' . $keys);
        }
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
