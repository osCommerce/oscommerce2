<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
        require('includes/functions/password_funcs.php');

        $username = HTML::sanitize($_POST['username']);
        $password = HTML::sanitize($_POST['password']);

        $Qcheck = $OSCOM_Db->get('administrators', 'id', ['user_name' => $username], null, 1);

        if (!$Qcheck->check()) {
          $OSCOM_Db->save('administrators', [
            'user_name' => $username,
            'user_password' => tep_encrypt_password($password)
          ]);
        } else {
          $OSCOM_MessageStack->add(ERROR_ADMINISTRATOR_EXISTS, 'error');
        }

        OSCOM::redirect(FILENAME_ADMINISTRATORS);
        break;
      case 'save':
        require('includes/functions/password_funcs.php');

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
            'user_password' => tep_encrypt_password($password)
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

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<div class="pull-right">
  <?= HTML::button(IMAGE_INSERT, 'fa fa-plus', OSCOM::link('administrators.php', 'action=new'), 'primary', null, 'btn-info'); ?>
</div>

<h2><i class="fa fa-users"></i> <a href="<?= OSCOM::link('administrators.php'); ?>"><?= HEADING_TITLE; ?></a></h2>

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
              $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
              $contents[] = array('text' => TEXT_INFO_USERNAME . '<br />' . HTML::inputField('username', $aInfo->user_name));
              $contents[] = array('text' => TEXT_INFO_NEW_PASSWORD . '<br />' . HTML::passwordField('password'));
              $contents[] = array('text' => HTML::button(IMAGE_SAVE, 'fa fa-save', null, 'primary', null, 'btn-success') . HTML::button(IMAGE_CANCEL, null, OSCOM::link(FILENAME_ADMINISTRATORS), null, null, 'btn-link'));
              break;

            case 'delete':
              $heading[] = array('text' => HTML::outputProtected($aInfo->user_name));

              $contents = array('form' => HTML::form('administrator', OSCOM::link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=deleteconfirm')));
              $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
              $contents[] = array('text' => '<strong>' . HTML::outputProtected($aInfo->user_name) . '</strong>');
              $contents[] = array('text' => HTML::button(IMAGE_DELETE, 'fa fa-trash', null, 'primary', null, 'btn-danger') . HTML::button(IMAGE_CANCEL, null, OSCOM::link(FILENAME_ADMINISTRATORS), null, null, 'btn-link'));
              break;
          }
        }
      }
    } else {
      $heading[] = array('text' => TEXT_INFO_HEADING_NEW_ADMINISTRATOR);

      $contents = array('form' => HTML::form('administrator', OSCOM::link(FILENAME_ADMINISTRATORS, 'action=insert'), 'post', 'autocomplete="off"'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => TEXT_INFO_USERNAME . '<br />' . HTML::inputField('username'));
      $contents[] = array('text' => TEXT_INFO_PASSWORD . '<br />' . HTML::passwordField('password'));
      $contents[] = array('text' => HTML::button(IMAGE_SAVE, 'fa fa-save', null, 'primary', null, 'btn-success') . HTML::button(IMAGE_CANCEL, null, OSCOM::link(FILENAME_ADMINISTRATORS), null, null, 'btn-link'));
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
      <th><?= TABLE_HEADING_ADMINISTRATORS; ?></th>
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
      <td class="action"><a href="<?= OSCOM::link('administrators.php', 'aID=' . $Qadmins->valueInt('id') . '&action=edit'); ?>"><i class="fa fa-pencil" title="<?= IMAGE_EDIT; ?>"></i></a><a href="<?= OSCOM::link('administrators.php', 'aID=' . $Qadmins->valueInt('id') . '&action=delete'); ?>"><i class="fa fa-trash" title="<?= IMAGE_DELETE; ?>"></i></a></td>
    </tr>

<?php
    }
?>

  </tbody>
</table>

<?php
  }

  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
