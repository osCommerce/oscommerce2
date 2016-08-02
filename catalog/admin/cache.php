<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    if ($action == 'reset') {
      tep_reset_cache_block($_GET['block']);
    }

    OSCOM::redirect(FILENAME_CACHE);
  }

// check if the cache directory exists
  if (is_dir(DIR_FS_CACHE)) {
    if (!tep_is_writable(DIR_FS_CACHE)) $OSCOM_MessageStack->add(ERROR_CACHE_DIRECTORY_NOT_WRITEABLE, 'error');
  } else {
    $OSCOM_MessageStack->add(ERROR_CACHE_DIRECTORY_DOES_NOT_EXIST, 'error');
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<h2><i class="fa fa-database"></i> <a href="<?= OSCOM::link('cache.php'); ?>"><?= HEADING_TITLE; ?></a></h2>

<table class="oscom-table table table-hover">
  <thead>
    <tr class="info">
      <th><?= TABLE_HEADING_CACHE; ?></th>
      <th class="text-right"><?= TABLE_HEADING_DATE_CREATED; ?></th>
      <th class="action"></th>
    </tr>
  </thead>
  <tbody>

<?php
  $languages = tep_get_languages();

  for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
    if ($languages[$i]['code'] == DEFAULT_LANGUAGE) {
      $language = $languages[$i]['directory'];
    }
  }

  for ($i=0, $n=sizeof($cache_blocks); $i<$n; $i++) {
    $cached_file = preg_replace('/-language/', '-' . $language, $cache_blocks[$i]['file']);

    if (file_exists(DIR_FS_CACHE . $cached_file)) {
      $cache_mtime = strftime(DATE_TIME_FORMAT, filemtime(DIR_FS_CACHE . $cached_file));
    } else {
      $cache_mtime = TEXT_FILE_DOES_NOT_EXIST;
      $dir = dir(DIR_FS_CACHE);

      while ($cache_file = $dir->read()) {
        $cached_file = preg_replace('/-language/', '-' . $language, $cache_blocks[$i]['file']);

        if (preg_match('/^' . $cached_file. '/', $cache_file)) {
          $cache_mtime = strftime(DATE_TIME_FORMAT, filemtime(DIR_FS_CACHE . $cache_file));
          break;
        }
      }

      $dir->close();
    }
?>
    <tr>
      <td><?= $cache_blocks[$i]['title']; ?></td>
      <td class="text-right"><?= $cache_mtime; ?></td>
      <td class="action"><?= '<a href="' . OSCOM::link(FILENAME_CACHE, 'action=reset&block=' . $cache_blocks[$i]['code']) . '">' . HTML::image(DIR_WS_IMAGES . 'icon_reset.gif', 'Reset', 13, 13) . '</a>'; ?></td>
    </tr>

<?php
  }
?>

  </tbody>
</table>

<p>
  <?= '<strong>' . TEXT_CACHE_DIRECTORY . '</strong> ' . DIR_FS_CACHE; ?>
</p>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
