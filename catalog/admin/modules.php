<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Apps;
  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  require('includes/application_top.php');

  $set = (isset($_GET['set']) ? $_GET['set'] : '');

  $modules = $cfgModules->getAll();

  if (empty($set) || !$cfgModules->exists($set)) {
    $set = $modules[0]['code'];
  }

  $module_type = $cfgModules->get($set, 'code');
  $module_directory = $cfgModules->get($set, 'directory');
  $module_language_directory = $cfgModules->get($set, 'language_directory');
  $module_site = $cfgModules->get($set, 'site');
  $module_key = $cfgModules->get($set, 'key');
  define('HEADING_TITLE', $cfgModules->get($set, 'title'));
  $template_integration = $cfgModules->get($set, 'template_integration');

  $appModuleType = null;

  switch ($module_type) {
    case 'dashboard':
      $appModuleType = 'AdminDashboard';
      break;

    case 'payment':
      $appModuleType = 'Payment';
      break;
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'save':
        foreach( $_POST['configuration'] as $key => $value ) {
          $key = HTML::sanitize($key);
          $value = HTML::sanitize($value);

          $OSCOM_Db->save('configuration', ['configuration_value' => $value], ['configuration_key' => $key]);
        }
        OSCOM::redirect(FILENAME_MODULES, 'set=' . $set . '&module=' . $_GET['module']);
        break;
      case 'install':
      case 'remove':
        if (strpos($_GET['module'], '\\') !== false) {
          $class = Apps::getModuleClass($_GET['module'], $appModuleType);

          if (class_exists($class)) {
            $file_extension = '';
            $module = new $class();
            $class = $_GET['module'];
          }
        } else {
          $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
          $class = basename($_GET['module']);
          if (is_file($module_directory . $class . $file_extension)) {
            include($module_directory . $class . $file_extension);
            $module = new $class;
          }
        }

        if (isset($module)) {
          if ($action == 'install') {
            if ($module->check() > 0) { // remove module if already installed
              $module->remove();
            }

            $module->install();

            $modules_installed = explode(';', constant($module_key));

            if (!in_array($class . $file_extension, $modules_installed)) {
              $modules_installed[] = $class . $file_extension;
            }

            Registry::get('Db')->save('configuration', ['configuration_value' => implode(';', $modules_installed)], ['configuration_key' => $module_key]);
            OSCOM::redirect(FILENAME_MODULES, 'set=' . $set . '&module=' . $class);
          } elseif ($action == 'remove') {
            $module->remove();

            $modules_installed = explode(';', constant($module_key));

            if (in_array($class . $file_extension, $modules_installed)) {
              unset($modules_installed[array_search($class . $file_extension, $modules_installed)]);
            }

            Registry::get('Db')->save('configuration', ['configuration_value' => implode(';', $modules_installed)], ['configuration_key' => $module_key]);
            OSCOM::redirect(FILENAME_MODULES, 'set=' . $set);
          }
        }
        OSCOM::redirect(FILENAME_MODULES, 'set=' . $set . '&module=' . $class);
        break;
    }
  }

  require($oscTemplate->getFile('template_top.php'));

  $modules_installed = (defined($module_key) ? explode(';', constant($module_key)) : array());
  $new_modules_counter = 0;

  $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
  $directory_array = array();
  if ($dir = @dir($module_directory)) {
    while ($file = $dir->read()) {
      if (!is_dir($module_directory . $file)) {
        if (substr($file, strrpos($file, '.')) == $file_extension) {
          if (isset($_GET['list']) && ($_GET['list'] == 'new')) {
            if (!in_array($file, $modules_installed)) {
              $directory_array[] = $file;
            }
          } else {
            if (in_array($file, $modules_installed)) {
              $directory_array[] = $file;
            } else {
              $new_modules_counter++;
            }
          }
        }
      }
    }
    $dir->close();
  }

  if (isset($appModuleType)) {
    foreach (Apps::getModules($appModuleType) as $k => $v) {
      if (isset($_GET['list']) && ($_GET['list'] == 'new')) {
        if (!in_array($k, $modules_installed)) {
          $directory_array[] = $k;
        }
      } else {
        if (in_array($k, $modules_installed)) {
          $directory_array[] = $k;
        } else {
          $new_modules_counter++;
        }
      }
    }
  }

  sort($directory_array);
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
<?php
  if (isset($_GET['list'])) {
    echo '            <td class="smallText" align="right">' . HTML::button(OSCOM::getDef('image_back'), 'fa fa-chevron-left', OSCOM::link(FILENAME_MODULES, 'set=' . $set)) . '</td>';
  } else {
    echo '            <td class="smallText" align="right">' . HTML::button(OSCOM::getDef('image_module_install') . ' (' . $new_modules_counter . ')', 'fa fa-plus', OSCOM::link(FILENAME_MODULES, 'set=' . $set . '&list=new')) . '</td>';
  }
?>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_modules'); ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo OSCOM::getDef('table_heading_sort_order'); ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo OSCOM::getDef('table_heading_action'); ?>&nbsp;</td>
              </tr>
<?php
  $installed_modules = array();
  for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
    $file = $directory_array[$i];

    if (strpos($file, '\\') !== false) {
      $file_extension = '';

      $class = Apps::getModuleClass($file, $appModuleType);

      $module = new $class();
      $module->code = $file;

      $class = $file;
    } else {
      $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));

      $OSCOM_Language->loadDefinitions($module_site . '/modules/' . $module_type . '/' . pathinfo($file, PATHINFO_FILENAME));

      include($module_directory . $file);

      $class = substr($file, 0, strrpos($file, '.'));

      if (class_exists($class)) {
        $module = new $class;
      }
    }

    if (isset($module)) {
      if ($module->check() > 0) {
        if (($module->sort_order > 0) && !isset($installed_modules[$module->sort_order])) {
          $installed_modules[$module->sort_order] = $file;
        } else {
          $installed_modules[] = $file;
        }
      }

      if ((!isset($_GET['module']) || (isset($_GET['module']) && ($_GET['module'] == $class))) && !isset($mInfo)) {
        $module_info = array('code' => $module->code,
                             'title' => $module->title,
                             'description' => $module->description,
                             'status' => $module->check(),
                             'signature' => (isset($module->signature) ? $module->signature : null),
                             'api_version' => (isset($module->api_version) ? $module->api_version : null));

        $module_keys = $module->keys();

        $keys_extra = array();
        for ($j=0, $k=sizeof($module_keys); $j<$k; $j++) {
          $Qkeys = $OSCOM_Db->get('configuration', [
            'configuration_title',
            'configuration_value',
            'configuration_description',
            'use_function',
            'set_function'
          ], [
            'configuration_key' => $module_keys[$j]
          ]);

          $keys_extra[$module_keys[$j]]['title'] = $Qkeys->value('configuration_title');
          $keys_extra[$module_keys[$j]]['value'] = $Qkeys->value('configuration_value');
          $keys_extra[$module_keys[$j]]['description'] = $Qkeys->value('configuration_description');
          $keys_extra[$module_keys[$j]]['use_function'] = $Qkeys->value('use_function');
          $keys_extra[$module_keys[$j]]['set_function'] = $Qkeys->value('set_function');
        }

        $module_info['keys'] = $keys_extra;

        $mInfo = new \ArrayObject($module_info, \ArrayObject::ARRAY_AS_PROPS);
      }

      if (isset($mInfo) && is_object($mInfo) && ($class == $mInfo->code) ) {
        if ($module->check() > 0) {
          echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_MODULES, 'set=' . $set . '&module=' . addslashes($class) . '&action=edit') . '\'">' . "\n";
        } else {
          echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
        }
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_MODULES, 'set=' . $set . (isset($_GET['list']) ? '&list=new' : '') . '&module=' . addslashes($class)) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo $module->title; ?></td>
                <td class="dataTableContent" align="right"><?php if (in_array($module->code . $file_extension, $modules_installed) && is_numeric($module->sort_order)) echo $module->sort_order; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($mInfo) && is_object($mInfo) && ($class == $mInfo->code) ) { echo HTML::image(OSCOM::linkImage('icon_arrow_right.gif')); } else { echo '<a href="' . OSCOM::link(FILENAME_MODULES, 'set=' . $set . (isset($_GET['list']) ? '&list=new' : '') . '&module=' . $class) . '">' . HTML::image(OSCOM::linkImage('icon_info.gif'), OSCOM::getDef('image_icon_info')) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
  }

  if (!isset($_GET['list'])) {
    ksort($installed_modules);
    $Qcheck = $OSCOM_Db->get('configuration', 'configuration_value', ['configuration_key' => $module_key]);
    if ($Qcheck->fetch() !== false) {
      if ($Qcheck->value('configuration_value') != implode(';', $installed_modules)) {
        $OSCOM_Db->save('configuration', [
          'configuration_value' => implode(';', $installed_modules),
          'last_modified' => 'now()'
        ], [
          'configuration_key' => $module_key
        ]);
      }
    } else {
      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Installed Modules',
        'configuration_key' => $module_key,
        'configuration_value' => implode(';', $installed_modules),
        'configuration_description' => 'This is automatically updated. No need to edit.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);
    }

    if ($template_integration == true) {
      $Qcheck = $OSCOM_Db->get('configuration', 'configuration_value', ['configuration_key' => 'TEMPLATE_BLOCK_GROUPS']);
      if ($Qcheck->fetch() !== false) {
        $tbgroups_array = explode(';', $Qcheck->value('configuration_value'));
        if (!in_array($module_type, $tbgroups_array)) {
          $tbgroups_array[] = $module_type;
          sort($tbgroups_array);
          $OSCOM_Db->save('configuration', [
            'configuration_value' => implode(';', $tbgroups_array),
            'last_modified' => 'now()'
          ], [
            'configuration_key' => 'TEMPLATE_BLOCK_GROUPS'
          ]);
        }
      } else {
        $OSCOM_Db->save('configuration', [
          'configuration_title' => 'Installed Template Block Groups',
          'configuration_key' => 'TEMPLATE_BLOCK_GROUPS',
          'configuration_value' => $module_type,
          'configuration_description' => 'This is automatically updated. No need to edit.',
          'configuration_group_id' => '6',
          'sort_order' => '0',
          'date_added' => 'now()'
        ]);
      }
    }
  }
?>
              <tr>
                <td colspan="3" class="smallText"><?php echo OSCOM::getDef('text_module_directory') . ' ' . $module_directory; ?></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  if (isset($mInfo) && (strpos($mInfo->code, '\\') !== false)) {
    $file_extension = '';
  } else {
    $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
  }

  switch ($action) {
    case 'edit':
      $keys = '';
      foreach( $mInfo->keys as $key => $value ) {
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

      $contents = array('form' => HTML::form('modules', OSCOM::link(FILENAME_MODULES, 'set=' . $set . '&module=' . $_GET['module'] . '&action=save')));
      $contents[] = array('text' => $keys);
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_save'), 'fa fa-save') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_MODULES, 'set=' . $set . '&module=' . $_GET['module'])));
      break;
    default:
      $heading[] = array('text' => '<strong>' . $mInfo->title . '</strong>');

      if (in_array($mInfo->code . $file_extension, $modules_installed) && ($mInfo->status > 0)) {
        $keys = '';
        foreach( $mInfo->keys as $value ) {
          $keys .= '<strong>' . $value['title'] . '</strong><br />';
          if ($value['use_function']) {
            $use_function = $value['use_function'];
            if (preg_match('/->/', $use_function)) {
              $class_method = explode('->', $use_function);
              if (!isset(${$class_method[0]}) || !is_object(${$class_method[0]})) {
                include('includes/classes/' . $class_method[0] . '.php');
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

        $contents[] = array('align' => 'center', 'text' => HTML::button(OSCOM::getDef('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_MODULES, 'set=' . $set . '&module=' . $mInfo->code . '&action=edit')) . HTML::button(OSCOM::getDef('image_module_remove'), 'fa fa-minus', OSCOM::link(FILENAME_MODULES, 'set=' . $set . '&module=' . $mInfo->code . '&action=remove')));

        if (isset($mInfo->signature) && (list($scode, $smodule, $sversion, $soscversion) = explode('|', $mInfo->signature))) {
          $contents[] = array('text' => '<br />' . HTML::image(OSCOM::linkImage('icon_info.gif'), OSCOM::getDef('image_icon_info')) . '&nbsp;<strong>' . OSCOM::getDef('text_info_version') . '</strong> ' . $sversion . ' (<a href="http://sig.oscommerce.com/' . $mInfo->signature . '" target="_blank">' . OSCOM::getDef('text_info_online_status') . '</a>)');
        }

        if (isset($mInfo->api_version)) {
          $contents[] = array('text' => HTML::image(OSCOM::linkImage('icon_info.gif'), OSCOM::getDef('image_icon_info')) . '&nbsp;<strong>' . OSCOM::getDef('text_info_api_version') . '</strong> ' . $mInfo->api_version);
        }

        $contents[] = array('text' => '<br />' . $mInfo->description);
        $contents[] = array('text' => '<br />' . $keys);
      } elseif (isset($_GET['list']) && ($_GET['list'] == 'new')) {
        if (isset($mInfo)) {
          $contents[] = array('align' => 'center', 'text' => HTML::button(OSCOM::getDef('image_module_install'), 'fa fa-plus', OSCOM::link(FILENAME_MODULES, 'set=' . $set . '&module=' . $mInfo->code . '&action=install')));

          if (isset($mInfo->signature) && (list($scode, $smodule, $sversion, $soscversion) = explode('|', $mInfo->signature))) {
            $contents[] = array('text' => '<br />' . HTML::image(OSCOM::linkImage('icon_info.gif'), OSCOM::getDef('image_icon_info')) . '&nbsp;<strong>' . OSCOM::getDef('text_info_version') . '</strong> ' . $sversion . ' (<a href="http://sig.oscommerce.com/' . $mInfo->signature . '" target="_blank">' . OSCOM::getDef('text_info_online_status') . '</a>)');
          }

          if (isset($mInfo->api_version)) {
            $contents[] = array('text' => HTML::image(OSCOM::linkImage('icon_info.gif'), OSCOM::getDef('image_icon_info')) . '&nbsp;<strong>' . OSCOM::getDef('text_info_api_version') . '</strong> ' . $mInfo->api_version);
          }

          $contents[] = array('text' => '<br />' . $mInfo->description);
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
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
