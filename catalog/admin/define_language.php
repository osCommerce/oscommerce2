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
                        'writable' => FileSystem::isWritable($path . $filename),
                        'size' => filesize($path . $filename),
                        'last_modified' => strftime(DATE_TIME_FORMAT, filemtime($path . $filename)));

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

  if (!isset($_GET['lngdir'])) $_GET['lngdir'] = $OSCOM_Language->get('directory');

  $languages_array = array();
  $languages = tep_get_languages();
  $lng_exists = false;
  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
    if ($languages[$i]['directory'] == $_GET['lngdir']) $lng_exists = true;

    $languages_array[] = array('id' => $languages[$i]['directory'],
                               'text' => $languages[$i]['name']);
  }

  if (!$lng_exists) $_GET['lngdir'] = $OSCOM_Language->get('directory');

  if (isset($_GET['filename'])) {
    $file_edit = realpath(OSCOM::getConfig('dir_root', 'Shop') . 'includes/languages/' . $_GET['filename']);
    if (realpath(substr($file_edit, 0, strlen(OSCOM::getConfig('dir_root', 'Shop') . 'includes/languages/'))) != realpath(OSCOM::getConfig('dir_root', 'Shop') . 'includes/languages/')) {
      OSCOM::redirect(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $_GET['lngdir']);
    }
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'save':
        if (isset($_GET['lngdir']) && isset($_GET['filename'])) {
          $file = OSCOM::getConfig('dir_root', 'Shop') . 'includes/languages/' . $_GET['filename'];

          if (is_file($file) && FileSystem::isWritable($file)) {
            $new_file = fopen($file, 'w');
            $file_contents = stripslashes($_POST['file_contents']);
            fwrite($new_file, $file_contents, strlen($file_contents));
            fclose($new_file);
          }

          OSCOM::redirect(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $_GET['lngdir']);
        }
        break;
    }
  }

  require($oscTemplate->getFile('template_top.php'));
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr><?php echo HTML::form('lng', OSCOM::link(FILENAME_DEFINE_LANGUAGE), 'get', null, ['session_id' => true]); ?>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo HTML::selectField('lngdir', $languages_array, $_GET['lngdir'], 'onchange="this.form.submit();"'); ?></td>
          </form></tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  if (isset($_GET['lngdir']) && isset($_GET['filename'])) {
    $file = OSCOM::getConfig('dir_root', 'Shop') . 'includes/languages/' . $_GET['filename'];

    if (is_file($file)) {
      $file_array = file($file);
      $contents = implode('', $file_array);

      $file_writeable = true;
      if (!FileSystem::isWritable($file)) {
        $file_writeable = false;
        $OSCOM_MessageStack->add(sprintf(ERROR_FILE_NOT_WRITEABLE, $file), 'error', 'defineLanguage');
        echo $OSCOM_MessageStack->get('defineLanguage');
      }

?>
          <tr><?php echo HTML::form('language', OSCOM::link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $_GET['lngdir'] . '&filename=' . $_GET['filename'] . '&action=save')); ?>
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><strong><?php echo $_GET['filename']; ?></strong></td>
              </tr>
              <tr>
                <td class="main"><?php echo HTML::textareaField('file_contents', '80', '25', $contents, (($file_writeable) ? '' : 'readonly') . ' style="width: 100%;"'); ?></td>
              </tr>
              <tr>
                <td class="smallText" align="right"><?php if ($file_writeable == true) { echo HTML::button(IMAGE_SAVE, 'fa fa-save') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $_GET['lngdir'])); } else { echo HTML::button(IMAGE_BACK, 'fa fa-chevron-left', OSCOM::link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $_GET['lngdir'])); } ?></td>
              </tr>
            </table></td>
          </form></tr>
          <tr>
            <td class="main"><?php echo TEXT_EDIT_NOTE; ?></td>
          </tr>
<?php
    } else {
?>
          <tr>
            <td class="main"><strong><?php echo TEXT_FILE_DOES_NOT_EXIST; ?></strong></td>
          </tr>
          <tr>
            <td><?php echo HTML::button(IMAGE_BACK, 'fa fa-chevron-left', OSCOM::link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $_GET['lngdir'])); ?></td>
          </tr>
<?php
    }
  } else {
    $filename = $_GET['lngdir'] . '.php';
    $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
?>
          <tr>
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_FILES; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_WRITABLE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_LAST_MODIFIED; ?></td>
              </tr>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
                <td class="dataTableContent"><a href="<?php echo OSCOM::link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $_GET['lngdir'] . '&filename=' . $filename); ?>"><strong><?php echo $filename; ?></strong></a></td>
                <td class="dataTableContent" align="center"><?php echo HTML::image(OSCOM::linkImage('icons/' . (FileSystem::isWritable(OSCOM::getConfig('dir_root', 'Shop') . 'includes/languages/' . $filename) ? 'tick.gif' : 'cross.gif'))); ?></td>
                <td class="dataTableContent" align="right"><?php echo strftime(DATE_TIME_FORMAT, filemtime(OSCOM::getConfig('dir_root', 'Shop') . 'includes/languages/' . $filename)); ?></td>
              </tr>
<?php
    foreach (tep_opendir(OSCOM::getConfig('dir_root', 'Shop') . 'includes/languages/' . $_GET['lngdir']) as $file) {
      if (substr($file['name'], strrpos($file['name'], '.')) == $file_extension) {
        $filename = substr($file['name'], strlen(OSCOM::getConfig('dir_root', 'Shop') . 'includes/languages/'));

        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' .
             '                <td class="dataTableContent"><a href="' . OSCOM::link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $_GET['lngdir'] . '&filename=' . $filename) . '">' . substr($filename, strlen($_GET['lngdir'] . '/')) . '</a></td>' .
             '                <td class="dataTableContent" align="center">' . HTML::image(OSCOM::linkImage('icons/' . (($file['writable'] == true) ? 'tick.gif' : 'cross.gif'))) . '</td>' .
             '                <td class="dataTableContent" align="right">' . $file['last_modified'] . '</td>' .
             '              </tr>';
      }
    }
?>
              </tr>
            </table></td>
          </tr>
<?php
  }
?>
        </table></td>
      </tr>
    </table>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
