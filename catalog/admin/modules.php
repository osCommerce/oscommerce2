<?php
/*
  $Id: modules.php,v 1.47 2003/06/29 22:50:52 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $set = (isset($HTTP_GET_VARS['set']) ? $HTTP_GET_VARS['set'] : '');

  if (tep_not_null($set)) {
    switch ($set) {
      case 'shipping':
        $module_type = 'shipping';
        $module_directory = DIR_FS_CATALOG_MODULES . 'shipping/';
        $module_key = 'MODULE_SHIPPING_INSTALLED';
        define('HEADING_TITLE', HEADING_TITLE_MODULES_SHIPPING);
        break;
      case 'ordertotal':
        $module_type = 'order_total';
        $module_directory = DIR_FS_CATALOG_MODULES . 'order_total/';
        $module_key = 'MODULE_ORDER_TOTAL_INSTALLED';
        define('HEADING_TITLE', HEADING_TITLE_MODULES_ORDER_TOTAL);
        break;
      case 'payment':
      default:
        $module_type = 'payment';
        $module_directory = DIR_FS_CATALOG_MODULES . 'payment/';
        $module_key = 'MODULE_PAYMENT_INSTALLED';
        define('HEADING_TITLE', HEADING_TITLE_MODULES_PAYMENT);
        break;
    }
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'save':
        while (list($key, $value) = each($HTTP_POST_VARS['configuration'])) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . $value . "' where configuration_key = '" . $key . "'");
        }
        tep_redirect(tep_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $HTTP_GET_VARS['module']));
        break;
      case 'install':
      case 'remove':
        $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
        $class = basename($HTTP_GET_VARS['module']);
        if (file_exists($module_directory . $class . $file_extension)) {
          include($module_directory . $class . $file_extension);
          $module = new $class;
          if ($action == 'install') {
            $module->install();
          } elseif ($action == 'remove') {
            $module->remove();
          }
        }
        tep_redirect(tep_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class));
        break;
    }
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODULES; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_SORT_ORDER; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
  $directory_array = array();
  if ($dir = @dir($module_directory)) {
    while ($file = $dir->read()) {
      if (!is_dir($module_directory . $file)) {
        if (substr($file, strrpos($file, '.')) == $file_extension) {
          $directory_array[] = $file;
        }
      }
    }
    sort($directory_array);
    $dir->close();
  }

  $installed_modules = array();
  for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
    $file = $directory_array[$i];

    include(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/' . $module_type . '/' . $file);
    include($module_directory . $file);

    $class = substr($file, 0, strrpos($file, '.'));
    if (tep_class_exists($class)) {
      $module = new $class;
      if ($module->check() > 0) {
        if ($module->sort_order > 0) {
          $installed_modules[$module->sort_order] = $file;
        } else {
          $installed_modules[] = $file;
        }
      }

      if ((!isset($HTTP_GET_VARS['module']) || (isset($HTTP_GET_VARS['module']) && ($HTTP_GET_VARS['module'] == $class))) && !isset($mInfo)) {
        $module_info = array('code' => $module->code,
                             'title' => $module->title,
                             'description' => $module->description,
                             'status' => $module->check());

        $module_keys = $module->keys();

        $keys_extra = array();
        for ($j=0, $k=sizeof($module_keys); $j<$k; $j++) {
          $key_value_query = tep_db_query("select configuration_title, configuration_value, configuration_description, use_function, set_function from " . TABLE_CONFIGURATION . " where configuration_key = '" . $module_keys[$j] . "'");
          $key_value = tep_db_fetch_array($key_value_query);

          $keys_extra[$module_keys[$j]]['title'] = $key_value['configuration_title'];
          $keys_extra[$module_keys[$j]]['value'] = $key_value['configuration_value'];
          $keys_extra[$module_keys[$j]]['description'] = $key_value['configuration_description'];
          $keys_extra[$module_keys[$j]]['use_function'] = $key_value['use_function'];
          $keys_extra[$module_keys[$j]]['set_function'] = $key_value['set_function'];
        }

        $module_info['keys'] = $keys_extra;

        $mInfo = new objectInfo($module_info);
      }

      if (isset($mInfo) && is_object($mInfo) && ($class == $mInfo->code) ) {
        if ($module->check() > 0) {
          echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class . '&action=edit') . '\'">' . "\n";
        } else {
          echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
        }
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo $module->title; ?></td>
                <td class="dataTableContent" align="right"><?php if (is_numeric($module->sort_order)) echo $module->sort_order; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($mInfo) && is_object($mInfo) && ($class == $mInfo->code) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . tep_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
  }

  ksort($installed_modules);
  $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = '" . $module_key . "'");
  if (tep_db_num_rows($check_query)) {
    $check = tep_db_fetch_array($check_query);
    if ($check['configuration_value'] != implode(';', $installed_modules)) {
      tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . implode(';', $installed_modules) . "', last_modified = now() where configuration_key = '" . $module_key . "'");
    }
  } else {
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Installed Modules', '" . $module_key . "', '" . implode(';', $installed_modules) . "', 'This is automatically updated. No need to edit.', '6', '0', now())");
  }
?>
              <tr>
                <td colspan="3" class="smallText"><?php echo TEXT_MODULE_DIRECTORY . ' ' . $module_directory; ?></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'edit':
      $keys = '';
      reset($mInfo->keys);
      while (list($key, $value) = each($mInfo->keys)) {
        $keys .= '<b>' . $value['title'] . '</b><br>' . $value['description'] . '<br>';

        if ($value['set_function']) {
          eval('$keys .= ' . $value['set_function'] . "'" . $value['value'] . "', '" . $key . "');");
        } else {
          $keys .= tep_draw_input_field('configuration[' . $key . ']', $value['value']);
        }
        $keys .= '<br><br>';
      }
      $keys = substr($keys, 0, strrpos($keys, '<br><br>'));

      $heading[] = array('text' => '<b>' . $mInfo->title . '</b>');

      $contents = array('form' => tep_draw_form('modules', FILENAME_MODULES, 'set=' . $set . '&module=' . $HTTP_GET_VARS['module'] . '&action=save'));
      $contents[] = array('text' => $keys);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $HTTP_GET_VARS['module']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      $heading[] = array('text' => '<b>' . $mInfo->title . '</b>');

      if ($mInfo->status == '1') {
        $keys = '';
        reset($mInfo->keys);
        while (list(, $value) = each($mInfo->keys)) {
          $keys .= '<b>' . $value['title'] . '</b><br>';
          if ($value['use_function']) {
            $use_function = $value['use_function'];
            if (ereg('->', $use_function)) {
              $class_method = explode('->', $use_function);
              if (!is_object(${$class_method[0]})) {
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
          $keys .= '<br><br>';
        }
        $keys = substr($keys, 0, strrpos($keys, '<br><br>'));

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $mInfo->code . '&action=remove') . '">' . tep_image_button('button_module_remove.gif', IMAGE_MODULE_REMOVE) . '</a> <a href="' . tep_href_link(FILENAME_MODULES, 'set=' . $set . (isset($HTTP_GET_VARS['module']) ? '&module=' . $HTTP_GET_VARS['module'] : '') . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>');
        $contents[] = array('text' => '<br>' . $mInfo->description);
        $contents[] = array('text' => '<br>' . $keys);
      } else {
        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $mInfo->code . '&action=install') . '">' . tep_image_button('button_module_install.gif', IMAGE_MODULE_INSTALL) . '</a>');
        $contents[] = array('text' => '<br>' . $mInfo->description);
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
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
