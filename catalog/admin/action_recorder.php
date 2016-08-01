<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
  $directory_array = array();
  if ($dir = @dir(DIR_FS_CATALOG_MODULES . 'action_recorder/')) {
    while ($file = $dir->read()) {
      if (!is_dir(DIR_FS_CATALOG_MODULES . 'action_recorder/' . $file)) {
        if (substr($file, strrpos($file, '.')) == $file_extension) {
          $directory_array[] = $file;
        }
      }
    }
    sort($directory_array);
    $dir->close();
  }

  for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
    $file = $directory_array[$i];

    if (file_exists(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/action_recorder/' . $file)) {
      include(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/action_recorder/' . $file);
    }

    include(DIR_FS_CATALOG_MODULES . 'action_recorder/' . $file);

    $class = substr($file, 0, strrpos($file, '.'));
    if (tep_class_exists($class)) {
      $GLOBALS[$class] = new $class;
    }
  }

  $modules_array = array();
  $modules_list_array = array(array('id' => '', 'text' => TEXT_ALL_MODULES));

  $Qmodules = $OSCOM_Db->get('action_recorder', 'distinct module', null, 'module');

  while ($Qmodules->fetch()) {
    $modules_array[] = $Qmodules->value('module');

    $modules_list_array[] = [
      'id' => $Qmodules->value('module'),
      'text' => (is_object($GLOBALS[$Qmodules->value('module')]) ? $GLOBALS[$Qmodules->value('module')]->title : $Qmodules->value('module'))
    ];
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'expire':
        $expired_entries = 0;

        if (isset($_GET['module']) && in_array($_GET['module'], $modules_array)) {
          if (is_object($GLOBALS[$_GET['module']])) {
            $expired_entries += $GLOBALS[$_GET['module']]->expireEntries();
          } else {
            $expired_entries = $OSCOM_Db->delete('action_recorder', [
              'module' => $_GET['module']
            ]);
          }
        } else {
          foreach ($modules_array as $module) {
            if (is_object($GLOBALS[$module])) {
              $expired_entries += $GLOBALS[$module]->expireEntries();
            }
          }
        }

        $OSCOM_MessageStack->add(sprintf(SUCCESS_EXPIRED_ENTRIES, $expired_entries), 'success');

        OSCOM::redirect(FILENAME_ACTION_RECORDER);

        break;
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<div class="pull-right">
  <?= HTML::button(IMAGE_DELETE, 'fa fa-trash', OSCOM::link('action_recorder.php', 'action=expire' . (isset($_GET['module']) && in_array($_GET['module'], $modules_array) ? '&module=' . $_GET['module'] : '')), 'primary', null, 'btn-danger'); ?>
</div>

<h2><i class="fa fa-tasks"></i> <a href="<?= OSCOM::link('action_recorder.php'); ?>"><?= HEADING_TITLE; ?></a></h2>

<?php
  echo HTML::form('search', OSCOM::link('action_recorder.php'), 'get', 'class="form-inline"', ['session_id' => true]) .
       TEXT_FILTER_SEARCH . ' ' . HTML::inputField('search') .
       HTML::selectField('module', $modules_list_array, null, 'onchange="this.form.submit();"') .
       '</form>';
?>

<table class="oscom-table table table-hover">
  <thead>
    <tr class="info">
      <th><?= TABLE_HEADING_MODULE; ?></th>
      <th><?= TABLE_HEADING_CUSTOMER; ?></th>
      <th><?= TABLE_HEADING_IDENTIFIER; ?></th>
      <th class="text-right"><?= TABLE_HEADING_DATE_ADDED; ?></th>
    </tr>
  </thead>
  <tbody>

<?php
  $filter = array();

  if (isset($_GET['module']) && in_array($_GET['module'], $modules_array)) {
    $filter[] = 'module = :module';
  }

  if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filter[] = 'identifier like :identifier';
  }

  $sql_query = 'select SQL_CALC_FOUND_ROWS * from :table_action_recorder';

  if (!empty($filter)) {
    $sql_query .= ' where ' . implode(' and ', $filter);
  }

  $sql_query .= ' order by date_added desc limit :page_set_offset, :page_set_max_results';

  $Qactions = $OSCOM_Db->prepare($sql_query);

  if (!empty($filter)) {
    if (isset($_GET['module']) && in_array($_GET['module'], $modules_array)) {
      $Qactions->bindValue(':module', $_GET['module']);
    }

    if (isset($_GET['search']) && !empty($_GET['search'])) {
      $Qactions->bindValue(':identifier', '%' . $_GET['search'] . '%');
    }
  }

  $Qactions->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
  $Qactions->execute();

  while ($Qactions->fetch()) {
    $module = $Qactions->value('module');

    $module_title = $Qactions->value('module');
    if (is_object($GLOBALS[$module])) {
      $module_title = $GLOBALS[$module]->title;
    }
?>

    <tr>
      <td><i class="fa <?= (($Qactions->valueInt('success') === 1) ? 'fa-check text-success' : 'fa-times text-danger'); ?>"></i> <?= $module_title; ?></td>
      <td><?= $Qactions->valueProtected('user_name') . ' [' . $Qactions->valueInt('user_id') . ']'; ?></td>
      <td><?= (tep_not_null($Qactions->value('identifier')) ? '<a href="' . OSCOM::link('action_recorder.php', 'search=' . $Qactions->value('identifier')) . '"><u>' . $Qactions->valueProtected('identifier') . '</u></a>': '(empty)'); ?></td>
      <td class="text-right"><?= tep_datetime_short($Qactions->value('date_added')); ?></td>
    </tr>

<?php
  }
?>

  </tbody>
</table>

<div>
  <span class="pull-right"><?= $Qactions->getPageSetLinks((isset($_GET['module']) && in_array($_GET['module'], $modules_array) && is_object($GLOBALS[$_GET['module']]) ? 'module=' . $_GET['module'] : null) . '&' . (isset($_GET['search']) && !empty($_GET['search']) ? 'search=' . $_GET['search'] : null)); ?></span>
  <span><?= $Qactions->getPageSetLabel(TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></span>
</div>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
