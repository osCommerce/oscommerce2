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

  $htaccess_array = null;
  $htpasswd_array = null;
  $is_iis = stripos($_SERVER['SERVER_SOFTWARE'], 'iis');

  $authuserfile_array = array('##### OSCOMMERCE ADMIN PROTECTION - BEGIN #####',
                              'AuthType Basic',
                              'AuthName "osCommerce Online Merchant Administration Tool"',
                              'AuthUserFile ' . DIR_FS_ADMIN . '.htpasswd_oscommerce',
                              'Require valid-user',
                              '##### OSCOMMERCE ADMIN PROTECTION - END #####');

  if (!$is_iis && file_exists(DIR_FS_ADMIN . '.htpasswd_oscommerce') && tep_is_writable(DIR_FS_ADMIN . '.htpasswd_oscommerce') && file_exists(DIR_FS_ADMIN . '.htaccess') && tep_is_writable(DIR_FS_ADMIN . '.htaccess')) {
    $htaccess_array = array();
    $htpasswd_array = array();

    if (filesize(DIR_FS_ADMIN . '.htaccess') > 0) {
      $fg = fopen(DIR_FS_ADMIN . '.htaccess', 'rb');
      $data = fread($fg, filesize(DIR_FS_ADMIN . '.htaccess'));
      fclose($fg);

      $htaccess_array = explode("\n", $data);
    }

    if (filesize(DIR_FS_ADMIN . '.htpasswd_oscommerce') > 0) {
      $fg = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'rb');
      $data = fread($fg, filesize(DIR_FS_ADMIN . '.htpasswd_oscommerce'));
      fclose($fg);

      $htpasswd_array = explode("\n", $data);
    }
  }

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

          if (is_array($htpasswd_array)) {
            for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
              list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

              if ($ht_username == $username) {
                unset($htpasswd_array[$i]);
              }
            }

            if (isset($_POST['htaccess']) && ($_POST['htaccess'] == 'true')) {
              $htpasswd_array[] = $username . ':' . tep_crypt_apr_md5($password);
            }

            $fp = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'w');
            fwrite($fp, implode("\n", $htpasswd_array));
            fclose($fp);

            if (!in_array('AuthUserFile ' . DIR_FS_ADMIN . '.htpasswd_oscommerce', $htaccess_array) && !empty($htpasswd_array)) {
              array_splice($htaccess_array, sizeof($htaccess_array), 0, $authuserfile_array);
            } elseif (empty($htpasswd_array)) {
              for ($i=0, $n=sizeof($htaccess_array); $i<$n; $i++) {
                if (in_array($htaccess_array[$i], $authuserfile_array)) {
                  unset($htaccess_array[$i]);
                }
              }
            }

            $fp = fopen(DIR_FS_ADMIN . '.htaccess', 'w');
            fwrite($fp, implode("\n", $htaccess_array));
            fclose($fp);
          }
        } else {
          $messageStack->add_session(ERROR_ADMINISTRATOR_EXISTS, 'error');
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

// update username in htpasswd if changed
        if (is_array($htpasswd_array)) {
          for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
            list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

            if ( ($check['user_name'] == $ht_username) && ($check['user_name'] != $username) ) {
              $htpasswd_array[$i] = $username . ':' . $ht_password;
            }
          }
        }

        $OSCOM_Db->save('administrators', [
          'user_name' => $username
        ], [
          'id' => (int)$_GET['aID']
        ]);

        if (tep_not_null($password)) {
// update password in htpasswd
          if (is_array($htpasswd_array)) {
            for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
              list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

              if ($ht_username == $username) {
                unset($htpasswd_array[$i]);
              }
            }

            if (isset($_POST['htaccess']) && ($_POST['htaccess'] == 'true')) {
              $htpasswd_array[] = $username . ':' . tep_crypt_apr_md5($password);
            }
          }

          $OSCOM_Db->save('administrators', [
            'user_password' => tep_encrypt_password($password)
          ], [
            'id' => (int)$_GET['aID']
          ]);
        } elseif (!isset($_POST['htaccess']) || ($_POST['htaccess'] != 'true')) {
          if (is_array($htpasswd_array)) {
            for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
              list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

              if ($ht_username == $username) {
                unset($htpasswd_array[$i]);
              }
            }
          }
        }

// write new htpasswd file
        if (is_array($htpasswd_array)) {
          $fp = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'w');
          fwrite($fp, implode("\n", $htpasswd_array));
          fclose($fp);

          if (!in_array('AuthUserFile ' . DIR_FS_ADMIN . '.htpasswd_oscommerce', $htaccess_array) && !empty($htpasswd_array)) {
            array_splice($htaccess_array, sizeof($htaccess_array), 0, $authuserfile_array);
          } elseif (empty($htpasswd_array)) {
            for ($i=0, $n=sizeof($htaccess_array); $i<$n; $i++) {
              if (in_array($htaccess_array[$i], $authuserfile_array)) {
                unset($htaccess_array[$i]);
              }
            }
          }

          $fp = fopen(DIR_FS_ADMIN . '.htaccess', 'w');
          fwrite($fp, implode("\n", $htaccess_array));
          fclose($fp);
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

        if (is_array($htpasswd_array)) {
          for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
            list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

            if ($ht_username == $check['user_name']) {
              unset($htpasswd_array[$i]);
            }
          }

          $fp = fopen(DIR_FS_ADMIN . '.htpasswd_oscommerce', 'w');
          fwrite($fp, implode("\n", $htpasswd_array));
          fclose($fp);

          if (empty($htpasswd_array)) {
            for ($i=0, $n=sizeof($htaccess_array); $i<$n; $i++) {
              if (in_array($htaccess_array[$i], $authuserfile_array)) {
                unset($htaccess_array[$i]);
              }
            }

            $fp = fopen(DIR_FS_ADMIN . '.htaccess', 'w');
            fwrite($fp, implode("\n", $htaccess_array));
            fclose($fp);
          }
        }

        OSCOM::redirect(FILENAME_ADMINISTRATORS);
        break;
    }
  }

  $secMessageStack = new messageStack();

  if (is_array($htpasswd_array)) {
    if (empty($htpasswd_array)) {
      $secMessageStack->add(sprintf(HTPASSWD_INFO, implode('<br />', $authuserfile_array)), 'error');
    } else {
      $secMessageStack->add(HTPASSWD_SECURED, 'success');
    }
  } else if (!$is_iis) {
    $secMessageStack->add(HTPASSWD_PERMISSIONS, 'error');
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td>
<?php
  echo $secMessageStack->output();
?>
        </td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ADMINISTRATORS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_HTPASSWD; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $Qadmins = $OSCOM_Db->get('administrators', ['id', 'user_name'], null, 'user_name');
  while ($Qadmins->fetch()) {
    if ((!isset($_GET['aID']) || (isset($_GET['aID']) && ((int)$_GET['aID'] === $Qadmins->valueInt('id')))) && !isset($aInfo) && (substr($action, 0, 3) != 'new')) {
      $aInfo = new objectInfo($Qadmins->toArray());
    }

    $htpasswd_secured = HTML::image(DIR_WS_IMAGES . 'icon_status_red.gif', 'Not Secured', 10, 10);

    if ($is_iis) {
      $htpasswd_secured = 'N/A';
    }

    if (is_array($htpasswd_array)) {
      for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
        list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

        if ($ht_username == $Qadmins->value('user_name')) {
          $htpasswd_secured = HTML::image(DIR_WS_IMAGES . 'icon_status_green.gif', 'Secured', 10, 10);
          break;
        }
      }
    }

    if ( (isset($aInfo) && is_object($aInfo)) && ($Qadmins->valueInt('id') === (int)$aInfo->id) ) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_ADMINISTRATORS, 'aID=' . $Qadmins->valueInt('id')) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $Qadmins->valueProtected('user_name'); ?></td>
                <td class="dataTableContent" align="center"><?php echo $htpasswd_secured; ?></td>
                <td class="dataTableContent" align="right"><?php if ( (isset($aInfo) && is_object($aInfo)) && ($Qadmins->valueInt('id') === (int)$aInfo->id) ) { echo HTML::image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . OSCOM::link(FILENAME_ADMINISTRATORS, 'aID=' . $Qadmins->valueInt('id')) . '">' . HTML::image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td class="smallText" colspan="3" align="right"><?php echo HTML::button(IMAGE_INSERT, 'fa fa-plus', OSCOM::link(FILENAME_ADMINISTRATORS, 'action=new')); ?></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<strong>' . TEXT_INFO_HEADING_NEW_ADMINISTRATOR . '</strong>');

      $contents = array('form' => HTML::form('administrator', OSCOM::link(FILENAME_ADMINISTRATORS, 'action=insert'), 'post', 'autocomplete="off"'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_USERNAME . '<br />' . HTML::inputField('username'));
      $contents[] = array('text' => '<br />' . TEXT_INFO_PASSWORD . '<br />' . HTML::passwordField('password'));

      if (is_array($htpasswd_array)) {
        $contents[] = array('text' => '<br />' . HTML::checkboxField('htaccess', 'true') . ' ' . TEXT_INFO_PROTECT_WITH_HTPASSWD);
      }

      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_SAVE, 'fa fa-save', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_ADMINISTRATORS)));
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . $aInfo->user_name . '</strong>');

      $contents = array('form' => HTML::form('administrator', OSCOM::link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=save'), 'post', 'autocomplete="off"'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br />' . TEXT_INFO_USERNAME . '<br />' . HTML::inputField('username', $aInfo->user_name));
      $contents[] = array('text' => '<br />' . TEXT_INFO_NEW_PASSWORD . '<br />' . HTML::passwordField('password'));

      if (is_array($htpasswd_array)) {
        $default_flag = false;

        for ($i=0, $n=sizeof($htpasswd_array); $i<$n; $i++) {
          list($ht_username, $ht_password) = explode(':', $htpasswd_array[$i], 2);

          if ($ht_username == $aInfo->user_name) {
            $default_flag = true;
            break;
          }
        }

        $contents[] = array('text' => '<br />' . HTML::checkboxField('htaccess', 'true', $default_flag) . ' ' . TEXT_INFO_PROTECT_WITH_HTPASSWD);
      }

      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_SAVE, 'fa fa-save', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id)));
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . $aInfo->user_name . '</strong>');

      $contents = array('form' => HTML::form('administrator', OSCOM::link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=deleteconfirm')));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><strong>' . $aInfo->user_name . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_DELETE, 'fa fa-trash', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id)));
      break;
    default:
      if (isset($aInfo) && is_object($aInfo)) {
        $heading[] = array('text' => '<strong>' . $aInfo->user_name . '</strong>');

        $contents[] = array('align' => 'center', 'text' => HTML::button(IMAGE_EDIT, 'fa fa-edit', OSCOM::link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=edit')) . HTML::button(IMAGE_DELETE, 'fa fa-trash', OSCOM::link(FILENAME_ADMINISTRATORS, 'aID=' . $aInfo->id . '&action=delete')));
      }
      break;
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
