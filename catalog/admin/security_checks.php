<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  function tep_sort_secmodules($a, $b) {
    return strcasecmp($a['title'], $b['title']);
  }

  $types = array('info', 'warning', 'error');

  $modules = array();

  if ($secdir = @dir(OSCOM::getConfig('dir_root') . 'includes/modules/security_check/')) {
    while ($file = $secdir->read()) {
      if (!is_dir(OSCOM::getConfig('dir_root') . 'includes/modules/security_check/' . $file)) {
        if (substr($file, strrpos($file, '.')) == '.php') {
          $class = 'securityCheck_' . substr($file, 0, strrpos($file, '.'));

          include(OSCOM::getConfig('dir_root') . 'includes/modules/security_check/' . $file);
          $$class = new $class();

          $modules[] = array('title' => isset($$class->title) ? $$class->title : substr($file, 0, strrpos($file, '.')),
                             'class' => $class,
                             'code' => substr($file, 0, strrpos($file, '.')));
        }
      }
    }
    $secdir->close();
  }

  if ($extdir = @dir(OSCOM::getConfig('dir_root') . 'includes/modules/security_check/extended/')) {
    while ($file = $extdir->read()) {
      if (!is_dir(OSCOM::getConfig('dir_root') . 'includes/modules/security_check/extended/' . $file)) {
        if (substr($file, strrpos($file, '.')) == '.php') {
          $class = 'securityCheckExtended_' . substr($file, 0, strrpos($file, '.'));

          include(OSCOM::getConfig('dir_root') . 'includes/modules/security_check/extended/' . $file);
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

  require($oscTemplate->getFile('template_top.php'));
?>

<div style="float: right;"><?php echo HTML::button('Reload', 'fa fa-refresh', OSCOM::link('security_checks.php')); ?></div>

<h1 class="pageHeading"><?php echo OSCOM::getDef('heading_title'); ?></h1>

<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr class="dataTableHeadingRow">
    <td class="dataTableHeadingContent" width="20">&nbsp;</td>
    <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_title'); ?></td>
    <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_module'); ?></td>
    <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_info'); ?></td>
    <td class="dataTableHeadingContent" width="20" align="right">&nbsp;</td>
  </tr>

<?php
  foreach ($modules as $module) {
    $secCheck = $GLOBALS[$module['class']];

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
         '    <td class="dataTableContent" align="center" valign="top">' . HTML::image(OSCOM::linkImage('ms_' . $secCheck->type . '.png'), '', 16, 16) . '</td>' . "\n" .
         '    <td class="dataTableContent" valign="top" style="white-space: nowrap;">' . HTML::outputProtected($module['title']) . '</td>' . "\n" .
         '    <td class="dataTableContent" valign="top">' . HTML::outputProtected($module['code']) . '</td>' . "\n" .
         '    <td class="dataTableContent" valign="top">' . $output . '</td>' . "\n" .
         '    <td class="dataTableContent" align="center" valign="top">' . ((isset($secCheck->has_doc) && $secCheck->has_doc) ? '<a href="http://library.oscommerce.com/Wiki&oscom_2_3&security_checks&' . $module['code'] . '" target="_blank">' . HTML::image(OSCOM::linkImage('icons/preview.gif')) . '</a>' : '') . '</td>' . "\n" .
         '  </tr>' . "\n";
  }
?>

</table>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
