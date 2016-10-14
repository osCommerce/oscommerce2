<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\Cache;
  use OSC\OM\FileSystem;
  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'reset':
        Cache::clear($_GET['block']);
        break;

      case 'resetAll':
        Cache::clearAll();
        break;
    }

    OSCOM::redirect(FILENAME_CACHE);
  }

// check if the cache directory exists
  if (is_dir(OSCOM::BASE_DIR . 'Work/Cache')) {
    if (!FileSystem::isWritable(OSCOM::BASE_DIR . 'Work/Cache')) $OSCOM_MessageStack->add(ERROR_CACHE_DIRECTORY_NOT_WRITEABLE, 'error');
  } else {
    $OSCOM_MessageStack->add(ERROR_CACHE_DIRECTORY_DOES_NOT_EXIST, 'error');
  }

  $cache_files = [];

  foreach (glob(OSCOM::BASE_DIR . 'Work/Cache/*.cache') as $c) {
    $key = basename($c, '.cache');

    if (($pos = strpos($key, '-')) !== false) {
      $cache_files[substr($key, 0, $pos)][] = $key;
    } else {
      $cache_files[$key][] = $key;
    }
  }

  require($oscTemplate->getFile('template_top.php'));
?>

<div class="pull-right">
  <?= HTML::button(IMAGE_DELETE, 'fa fa-recycle', OSCOM::link('cache.php', 'action=resetAll'), 'primary', null, 'btn-danger'); ?>
</div>

<h2><i class="fa fa-database"></i> <a href="<?= OSCOM::link('cache.php'); ?>"><?= HEADING_TITLE; ?></a></h2>

<table class="oscom-table table table-hover">
  <thead>
    <tr class="info">
      <th><?= TABLE_HEADING_CACHE; ?></th>
      <th class="text-right"><?= TABLE_HEADING_CACHE_NUMBER_OF_FILES; ?></th>
      <th class="action"></th>
    </tr>
  </thead>
  <tbody>

<?php
  foreach (array_keys($cache_files) as $key) {
?>

    <tr>
      <td><?= $key; ?></td>
      <td class="text-right"><?= count($cache_files[$key]); ?></td>
      <td class="action"><a href="<?= OSCOM::link(FILENAME_CACHE, 'action=reset&block=' . $key); ?>"><i class="fa fa-recycle" title="<?= IMAGE_DELETE; ?>"></i></a></td>
    </tr>

<?php
  }
?>

  </tbody>
</table>

<p>
  <?= '<strong>' . TEXT_CACHE_DIRECTORY . '</strong> ' . FileSystem::displayPath(OSCOM::BASE_DIR . 'Work/Cache/'); ?>
</p>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
