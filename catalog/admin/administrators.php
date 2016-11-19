<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\Hash;
  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
        $username = HTML::sanitize($_POST['username']);
        $password = HTML::sanitize($_POST['password']);

        $Qcheck = $OSCOM_Db->get('administrators', 'id', ['user_name' => $username], null, 1);

        if (!$Qcheck->check()) {
          $OSCOM_Db->save('administrators', [
            'user_name' => $username,
            'user_password' => Hash::encrypt($password)
          ]);
        } else {
          $OSCOM_MessageStack->add(OSCOM::getDef('error_administrator_exists'), 'error');
        }

        OSCOM::redirect(FILENAME_ADMINISTRATORS);
        break;
      case 'save':
        $username = HTML::sanitize($_POST['username']);
        $password = HTML::sanitize($_POST['password']);

        $Qcheck = $OSCOM_Db->get('administrators', [
          'id',
          'user_name'
        ], [
          'id' => (int)$_GET['aID']
        ]);

// update username in current session if changed
        if ( ($Qcheck->valueInt('id') === $_SESSION['admin']['id']) && ($username !== $_SESSION['admin']['username']) ) {
          $_SESSION['admin']['username'] = $username;
        }

        $OSCOM_Db->save('administrators', [
          'user_name' => $username
        ], [
          'id' => (int)$_GET['aID']
        ]);

        if (tep_not_null($password)) {
          $OSCOM_Db->save('administrators', [
            'user_password' => Hash::encrypt($password)
          ], [
            'id' => (int)$_GET['aID']
          ]);
        }

        OSCOM::redirect(FILENAME_ADMINISTRATORS, 'aID=' . (int)$_GET['aID']);
        break;
      case 'deleteconfirm':
        $id = (int)$_GET['aID'];

        $Qcheck = $OSCOM_Db->get('administrators', ['id', 'user_name'], ['id' => $id]);

        if ($_SESSION['admin']['id'] === $Qcheck->valueInt('id')) {
          unset($_SESSION['admin']);
        }

        $OSCOM_Db->delete('administrators', ['id' => $id]);

        OSCOM::redirect(FILENAME_ADMINISTRATORS);
        break;
    }
  }

  $show_listing = true;

  require($oscTemplate->getFile('template_top.php'));

  if (empty($action)) {
?>

<div class="pull-right">
  <?= HTML::button(OSCOM::getDef('image_insert'), 'fa fa-plus', OSCOM::link('administrators.php', 'action=new'), null, 'btn-info'); ?>
</div>

<?php
  }
?>

<h2><i class="fa fa-users"></i> <a href="<?= OSCOM::link('administrators.php'); ?>"><?= OSCOM::getDef('heading_title'); ?></a></h2>

<?php
  if (!empty($action)) {
    $heading = $contents = [];

    if ($action != 'new') {
      if (isset($_GET['aID'])) {
        $Qadmin = $OSCOM_Db->get('administrators', ['id', 'user_name'], ['id' => (int)$_GET['aID']]);

        if ($Qadmin->fetch() !== false) {
          $aInfo = new objectInfo($Qadmin->toArray());

          switch ($action) {
            case 'edit':
              $heading[] = array('text' => HTML::outputProtected($aInfo->user_name));

              $contents = array('form' => HTML::form('administrator', OSCOM::link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=save'), 'post', 'autocomplete="off"'));
              $contents[] = array('text' => OSCOM::getDef('text_info_edit_intro'));
              $contents[] = array('text' => OSCOM::getDef('text_info_username') . '<br />' . HTML::inputField('username', $aInfo->user_name));
              $contents[] = array('text' => OSCOM::getDef('text_info_new_password') . '<br />' . HTML::passwordField('password'));
              $contents[] = array('text' => HTML::button(OSCOM::getDef('image_save'), 'fa fa-save', null, null, 'btn-success') . HTML::button(OSCOM::getDef('image_cancel'), null, OSCOM::link(FILENAME_ADMINISTRATORS), null, 'btn-link'));
              break;

            case 'delete':
              $heading[] = array('text' => HTML::outputProtected($aInfo->user_name));

              $contents = array('form' => HTML::form('administrator', OSCOM::link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=deleteconfirm')));
              $contents[] = array('text' => OSCOM::getDef('text_info_delete_intro'));
              $contents[] = array('text' => '<strong>' . HTML::outputProtected($aInfo->user_name) . '</strong>');
              $contents[] = array('text' => HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash', null, null, 'btn-danger') . HTML::button(OSCOM::getDef('image_cancel'), null, OSCOM::link(FILENAME_ADMINISTRATORS), null, 'btn-link'));
              break;
          }
        }
      }
    } else {
      $heading[] = array('text' => OSCOM::getDef('text_info_heading_new_administrator'));

      $contents = array('form' => HTML::form('administrator', OSCOM::link(FILENAME_ADMINISTRATORS, 'action=insert'), 'post', 'autocomplete="off"'));
      $contents[] = array('text' => OSCOM::getDef('text_info_insert_intro'));
      $contents[] = array('text' => OSCOM::getDef('text_info_username') . '<br />' . HTML::inputField('username'));
      $contents[] = array('text' => OSCOM::getDef('text_info_password') . '<br />' . HTML::passwordField('password'));
      $contents[] = array('text' => HTML::button(OSCOM::getDef('image_save'), 'fa fa-save', null, null, 'btn-success') . HTML::button(OSCOM::getDef('image_cancel'), null, OSCOM::link(FILENAME_ADMINISTRATORS), null, 'btn-link'));
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
      <th><?= OSCOM::getDef('table_heading_administrators'); ?></th>
      <th class="action"></th>
    </tr>
  </thead>
  <tbody>

<?php
    $Qadmins = $OSCOM_Db->get('administrators', ['id', 'user_name'], null, 'user_name');

    while ($Qadmins->fetch()) {
?>

    <tr>
      <td><?= $Qadmins->valueProtected('user_name'); ?></td>
      <td class="action"><a href="<?= OSCOM::link('administrators.php', 'aID=' . $Qadmins->valueInt('id') . '&action=edit'); ?>"><i class="fa fa-pencil" title="<?= OSCOM::getDef('image_edit'); ?>"></i></a><a href="<?= OSCOM::link('administrators.php', 'aID=' . $Qadmins->valueInt('id') . '&action=delete'); ?>"><i class="fa fa-trash" title="<?= OSCOM::getDef('image_delete'); ?>"></i></a></td>
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
