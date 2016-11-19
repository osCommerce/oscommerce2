<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
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
  if (is_dir(Cache::getPath())) {
    if (!FileSystem::isWritable(Cache::getPath())) $OSCOM_MessageStack->add(OSCOM::getDef('error_cache_directory_not_writeable'), 'error');
  } else {
    $OSCOM_MessageStack->add(OSCOM::getDef('error_cache_directory_does_not_exist'), 'error');
  }

  $cache_files = [];

  foreach (glob(Cache::getPath() . '*.cache') as $c) {
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
  <?= HTML::button(OSCOM::getDef('image_delete'), 'fa fa-recycle', OSCOM::link('cache.php', 'action=resetAll'), null, 'btn-danger'); ?>
</div>

<h2><i class="fa fa-database"></i> <a href="<?= OSCOM::link('cache.php'); ?>"><?= OSCOM::getDef('heading_title'); ?></a></h2>

<table class="oscom-table table table-hover">
  <thead>
    <tr class="info">
      <th><?= OSCOM::getDef('table_heading_cache'); ?></th>
      <th class="text-right"><?= OSCOM::getDef('table_heading_cache_number_of_files'); ?></th>
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
      <td class="action"><a href="<?= OSCOM::link(FILENAME_CACHE, 'action=reset&block=' . $key); ?>"><i class="fa fa-recycle" title="<?= OSCOM::getDef('image_delete'); ?>"></i></a></td>
    </tr>

<?php
  }
?>

  </tbody>
</table>

<p>
  <?= '<strong>' . OSCOM::getDef('text_cache_directory') . '</strong> ' . FileSystem::displayPath(Cache::getPath()); ?>
</p>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
