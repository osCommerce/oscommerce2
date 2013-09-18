<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  function tep_sort_secmodules($a, $b) {
    return strcasecmp($a['title'], $b['title']);
  }

  $types = array('info', 'warning', 'error');

  $modules = array();

  if ($secdir = @dir(DIR_FS_ADMIN . 'includes/modules/security_check/')) {
    while ($file = $secdir->read()) {
      if (!is_dir(DIR_FS_ADMIN . 'includes/modules/security_check/' . $file)) {
        if (substr($file, strrpos($file, '.')) == '.php') {
          $class = 'securityCheck_' . substr($file, 0, strrpos($file, '.'));

          include(DIR_FS_ADMIN . 'includes/modules/security_check/' . $file);
          $$class = new $class();

          $modules[] = array('title' => isset($$class->title) ? $$class->title : substr($file, 0, strrpos($file, '.')),
                             'class' => $class,
                             'code' => substr($file, 0, strrpos($file, '.')));
        }
      }
    }
    $secdir->close();
  }

  if ($extdir = @dir(DIR_FS_ADMIN . 'includes/modules/security_check/extended/')) {
    while ($file = $extdir->read()) {
      if (!is_dir(DIR_FS_ADMIN . 'includes/modules/security_check/extended/' . $file)) {
        if (substr($file, strrpos($file, '.')) == '.php') {
          $class = 'securityCheckExtended_' . substr($file, 0, strrpos($file, '.'));

          include(DIR_FS_ADMIN . 'includes/modules/security_check/extended/' . $file);
          $$class = new $class();

          $modules[] = array('title' => isset($$class->title) ? $$class->title : substr($file, 0, strrpos($file, '.')),
                             'class' => $class,
                             'code' => substr($file, 0, strrpos($file, '.')));
        }
      }
    }
    $extdir->close();
  }

  usort($modules, 'tep_sort_secmodules');

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<div style="float: right;"><?php echo tep_draw_button('Reload', 'arrowrefresh-1-e', tep_href_link('security_checks.php')); ?></div>

<h1 class="pageHeading"><?php echo HEADING_TITLE; ?></h1>

<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr class="dataTableHeadingRow">
    <td class="dataTableHeadingContent" width="20">&nbsp;</td>
    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TITLE; ?></td>
    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODULE; ?></td>
    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_INFO; ?></td>
    <td class="dataTableHeadingContent" width="20" align="right">&nbsp;</td>
  </tr>

<?php
  foreach ($modules as $module) {
    $secCheck = $$module['class'];

    if ( !in_array($secCheck->type, $types) ) {
      $secCheck->type = 'info';
    }

    $output = '';

    if ( $secCheck->pass() ) {
      $secCheck->type = 'success';
    } else {
      $output = $secCheck->getMessage();
    }

    echo '  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n" .
         '    <td class="dataTableContent" align="center" valign="top">' . tep_image(DIR_WS_IMAGES . 'ms_' . $secCheck->type . '.png', '', 16, 16) . '</td>' . "\n" .
         '    <td class="dataTableContent" valign="top" style="white-space: nowrap;">' . tep_output_string_protected($module['title']) . '</td>' . "\n" .
         '    <td class="dataTableContent" valign="top">' . tep_output_string_protected($module['code']) . '</td>' . "\n" .
         '    <td class="dataTableContent" valign="top">' . $output . '</td>' . "\n" .
         '    <td class="dataTableContent" align="center" valign="top">' . ((isset($secCheck->has_doc) && $secCheck->has_doc) ? '<a href="http://library.oscommerce.com/Wiki&oscom_2_3&security_checks&' . $module['code'] . '" target="_blank">' . tep_image(DIR_WS_IMAGES . 'icons/preview.gif') . '</a>' : '') . '</td>' . "\n" .
         '  </tr>' . "\n";
  }
?>

</table>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
