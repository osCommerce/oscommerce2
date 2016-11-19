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

  $gID = (isset($_GET['gID'])) ? $_GET['gID'] : 1;

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'save':
        $cID = HTML::sanitize($_GET['cID']);

        if (isset($_POST['configuration_value'])) {
          $configuration_value = $_POST['configuration_value'];
        } else {
          $configuration_value = '';
        }

        $OSCOM_Db->save('configuration', [
          'configuration_value' => $configuration_value,
          'last_modified' => 'now()'
        ], [
          'configuration_id' => (int)$cID
        ]);

        OSCOM::redirect(FILENAME_CONFIGURATION, 'gID=' . $gID . '&cID=' . $cID);
        break;
    }
  }

  $Qgroup = $OSCOM_Db->get('configuration_group', 'configuration_group_title', ['configuration_group_id' => (int)$gID]);

  $show_listing = true;

  require($oscTemplate->getFile('template_top.php'));
?>

<h2><i class="fa fa-cog"></i> <a href="<?= OSCOM::link('configuration.php', 'gID=' . $gID); ?>"><?= $Qgroup->valueProtected('configuration_group_title'); ?></a></h2>

<?php
  if (!empty($action)) {
    $heading = $contents = [];

    if (isset($_GET['cID'])) {
      $Qcfg = $OSCOM_Db->get('configuration', [
        'configuration_id',
        'configuration_title',
        'configuration_key',
        'configuration_value',
        'configuration_description',
        'set_function'
      ], [
        'configuration_id' => (int)$_GET['cID']
      ]);

      if ($Qcfg->fetch() !== false) {
        $cInfo = new objectInfo($Qcfg->toArray());

        if ($action == 'edit') {
          $heading[] = array('text' => $cInfo->configuration_title);

          if (!empty($cInfo->set_function)) {
            eval('$value_field = ' . $cInfo->set_function . '"' . htmlspecialchars($cInfo->configuration_value) . '");');
          } else {
            $value_field = HTML::inputField('configuration_value', $cInfo->configuration_value);
          }

          $contents = array('form' => HTML::form('configuration', OSCOM::link(FILENAME_CONFIGURATION, 'gID=' . $gID . '&cID=' . $cInfo->configuration_id . '&action=save')));
          $contents[] = array('text' => OSCOM::getDef('text_info_edit_intro'));
          $contents[] = array('text' => $cInfo->configuration_description);
          $contents[] = array('text' => $value_field);
          $contents[] = array('text' => HTML::button(OSCOM::getDef('image_save'), 'fa fa-save', null, null, 'btn-success') . HTML::button(OSCOM::getDef('image_cancel'), null, OSCOM::link(FILENAME_CONFIGURATION, 'gID=' . $gID), null, 'link'));
        }
      }
    }

    if (tep_not_null($heading) && tep_not_null($contents)) {
      $show_listing = false;

      echo HTML::panel($heading, $contents, ['type' => 'info']);
    }
  }

  if ($show_listing === true) {
?>

<table class="oscom-table table table-hover">
  <thead>
    <tr class="info">
      <th><?= OSCOM::getDef('table_heading_configuration_title'); ?></th>
      <th><?= OSCOM::getDef('table_heading_configuration_value'); ?></th>
      <th class="action"></th>
    </tr>
  </thead>
  <tbody>

<?php
    $Qcfg = $OSCOM_Db->get('configuration', [
      'configuration_id',
      'configuration_title',
      'configuration_value',
      'use_function'
    ], [
      'configuration_group_id' => (int)$gID
    ], 'sort_order');

    while ($Qcfg->fetch()) {
      if ($Qcfg->hasValue('use_function') && tep_not_null($Qcfg->value('use_function'))) {
        $use_function = $Qcfg->value('use_function');
        if (preg_match('/->/', $use_function)) {
          $class_method = explode('->', $use_function);
          if (!is_object(${$class_method[0]})) {
            include('includes/classes/' . $class_method[0] . '.php');
            ${$class_method[0]} = new $class_method[0]();
          }
          $cfgValue = tep_call_function($class_method[1], $Qcfg->value('configuration_value'), ${$class_method[0]});
        } else {
          $cfgValue = tep_call_function($use_function, $Qcfg->value('configuration_value'));
        }
      } else {
        $cfgValue = $Qcfg->value('configuration_value');
      }
?>

    <tr>
      <td><?= $Qcfg->value('configuration_title'); ?></td>
      <td><?= htmlspecialchars($cfgValue); ?></td>
      <td class="action"><a href="<?= OSCOM::link('configuration.php', 'gID=' . $gID . '&cID=' . $Qcfg->valueInt('configuration_id') . '&action=edit'); ?>"><i class="fa fa-pencil" title="<?= OSCOM::getDef('image_edit'); ?>"></i></a></td>
    </tr>

<?php
    }
?>

  </tbody>
</table>

<?php
  }

  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
