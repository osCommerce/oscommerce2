<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

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
                        'writable' => tep_is_writable($path . $filename),
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

  if (!isset($_GET['lngdir'])) $_GET['lngdir'] = $language;

  $languages_array = array();
  $languages = tep_get_languages();
  $lng_exists = false;
  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
    if ($languages[$i]['directory'] == $_GET['lngdir']) $lng_exists = true;

    $languages_array[] = array('id' => $languages[$i]['directory'],
                               'text' => $languages[$i]['name']);
  }

  if (!$lng_exists) $_GET['lngdir'] = $language;

  if (isset($_GET['filename'])) {
    $file_edit = realpath(DIR_FS_CATALOG_LANGUAGES . $_GET['filename']);

    if (substr($file_edit, 0, strlen(DIR_FS_CATALOG_LANGUAGES)) != DIR_FS_CATALOG_LANGUAGES) {
      tep_redirect(tep_href_link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $_GET['lngdir']));
    }
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'save':
        if (isset($_GET['lngdir']) && isset($_GET['filename'])) {
          $file = DIR_FS_CATALOG_LANGUAGES . $_GET['filename'];

          if (file_exists($file) && tep_is_writable($file)) {
            $new_file = fopen($file, 'w');
            $file_contents = stripslashes($_POST['file_contents']);
            fwrite($new_file, $file_contents, strlen($file_contents));
            fclose($new_file);
          }

          tep_redirect(tep_href_link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $_GET['lngdir']));
        }
        break;
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr><?php echo tep_draw_form('lng', FILENAME_DEFINE_LANGUAGE, '', 'get'); ?>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', '1', HEADING_IMAGE_HEIGHT); ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_pull_down_menu('lngdir', $languages_array, $_GET['lngdir'], 'onchange="this.form.submit();"'); ?></td>
          <?php echo tep_hide_session_id(); ?></form></tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  if (isset($_GET['lngdir']) && isset($_GET['filename'])) {
    $file = DIR_FS_CATALOG_LANGUAGES . $_GET['filename'];

    if (file_exists($file)) {
      $file_array = file($file);
      $contents = implode('', $file_array);

      $file_writeable = true;
      if (!tep_is_writable($file)) {
        $file_writeable = false;
        $messageStack->reset();
        $messageStack->add(sprintf(ERROR_FILE_NOT_WRITEABLE, $file), 'error');
        echo $messageStack->output();
      }

?>
          <tr><?php echo tep_draw_form('language', FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $_GET['lngdir'] . '&filename=' . $_GET['filename'] . '&action=save'); ?>
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><strong><?php echo $_GET['filename']; ?></strong></td>
              </tr>
              <tr>
                <td class="main"><?php echo tep_draw_textarea_field('file_contents', 'soft', '80', '25', $contents, (($file_writeable) ? '' : 'readonly') . ' style="width: 100%;"'); ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText" align="right"><?php if ($file_writeable == true) { echo tep_draw_button(IMAGE_SAVE, 'disk', null, 'primary') . tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $_GET['lngdir'])); } else { echo tep_draw_button(IMAGE_BACK, 'arrow-1-w', tep_href_link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $_GET['lngdir'])); } ?></td>
              </tr>
            </table></td>
          </form></tr>
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
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
            <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td><?php echo tep_draw_button(IMAGE_BACK, 'arrow-1-w', tep_href_link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $_GET['lngdir'])); ?></td>
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
                <td class="dataTableContent"><a href="<?php echo tep_href_link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $_GET['lngdir'] . '&filename=' . $filename); ?>"><strong><?php echo $filename; ?></strong></a></td>
                <td class="dataTableContent" align="center"><?php echo tep_image(DIR_WS_IMAGES . 'icons/' . ((tep_is_writable(DIR_FS_CATALOG_LANGUAGES . $filename) == true) ? 'tick.gif' : 'cross.gif')); ?></td>
                <td class="dataTableContent" align="right"><?php echo strftime(DATE_TIME_FORMAT, filemtime(DIR_FS_CATALOG_LANGUAGES . $filename)); ?></td>
              </tr>
<?php
    foreach (tep_opendir(DIR_FS_CATALOG_LANGUAGES . $_GET['lngdir']) as $file) {
      if (substr($file['name'], strrpos($file['name'], '.')) == $file_extension) {
        $filename = substr($file['name'], strlen(DIR_FS_CATALOG_LANGUAGES));

        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' .
             '                <td class="dataTableContent"><a href="' . tep_href_link(FILENAME_DEFINE_LANGUAGE, 'lngdir=' . $_GET['lngdir'] . '&filename=' . $filename) . '">' . substr($filename, strlen($_GET['lngdir'] . '/')) . '</a></td>' .
             '                <td class="dataTableContent" align="center">' . tep_image(DIR_WS_IMAGES . 'icons/' . (($file['writable'] == true) ? 'tick.gif' : 'cross.gif')) . '</td>' .
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
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
