<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\FileSystem;
  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  function tep_opendir($path) {
    $path = rtrim($path, '/') . '/';

    $exclude_array = array('.', '..', '.DS_Store', 'Thumbs.db');

    $result = array();

    if ($handle = opendir($path)) {
      while (false !== ($filename = readdir($handle))) {
        if (!in_array($filename, $exclude_array)) {
          $file = array('name' => $path . $filename,
                        'is_dir' => is_dir($path . $filename),
                        'writable' => FileSystem::isWritable($path . $filename));

          $result[] = $file;

          if ($file['is_dir'] == true) {
            $result = array_merge($result, tep_opendir($path . $filename));
          }
        }
      }

      closedir($handle);
    }

    return $result;
  }

  $whitelist_array = [];

  $Qwhitelist = $OSCOM_Db->get('sec_directory_whitelist', 'directory');

  while ($Qwhitelist->fetch()) {
    $whitelist_array[] = $Qwhitelist->value('directory');
  }

  $admin_dir = basename(OSCOM::getConfig('dir_root'));

  if ($admin_dir != 'admin') {
    for ($i=0, $n=sizeof($whitelist_array); $i<$n; $i++) {
      if (substr($whitelist_array[$i], 0, 6) == 'admin/') {
        $whitelist_array[$i] = $admin_dir . substr($whitelist_array[$i], 5);
      }
    }
  }

  require('includes/template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_DIRECTORIES; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_WRITABLE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_RECOMMENDED; ?></td>
              </tr>
<?php
  foreach (tep_opendir(OSCOM::getConfig('dir_root', 'Shop')) as $file) {
    if ($file['is_dir']) {
?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
                <td class="dataTableContent"><?php echo substr($file['name'], strlen(OSCOM::getConfig('dir_root', 'Shop'))); ?></td>
                <td class="dataTableContent" align="center"><?php echo HTML::image(OSCOM::linkImage('icons/' . (($file['writable'] == true) ? 'tick.gif' : 'cross.gif'))); ?></td>
                <td class="dataTableContent" align="center"><?php echo HTML::image(OSCOM::linkImage('icons/' . (in_array(substr($file['name'], strlen(OSCOM::getConfig('dir_root', 'Shop'))), $whitelist_array) ? 'tick.gif' : 'cross.gif'))); ?></td>
              </tr>
<?php
    }
  }
?>
              <tr>
                <td colspan="3" class="smallText"><?php echo TEXT_DIRECTORY . ' ' . OSCOM::getConfig('dir_root', 'Shop'); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table>

<?php
  require('includes/template_bottom.php');
  require('includes/application_bottom.php');
?>
