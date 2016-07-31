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

  if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'lock':
      case 'unlock':
        $newsletter_id = HTML::sanitize($_GET['nID']);
        $status = (($action == 'lock') ? '1' : '0');

        $OSCOM_Db->save('newsletters', ['locked' => $status], ['newsletters_id' => (int)$newsletter_id]);

        OSCOM::redirect(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']);
        break;
      case 'insert':
      case 'update':
        if (isset($_POST['newsletter_id'])) $newsletter_id = HTML::sanitize($_POST['newsletter_id']);
        $newsletter_module = HTML::sanitize($_POST['module']);
        $title = HTML::sanitize($_POST['title']);
        $content = HTML::sanitize($_POST['content']);

        $newsletter_error = false;
        if (empty($title)) {
          $OSCOM_MessageStack->add(ERROR_NEWSLETTER_TITLE, 'error');
          $newsletter_error = true;
        }

        if (empty($newsletter_module)) {
          $OSCOM_MessageStack->add(ERROR_NEWSLETTER_MODULE, 'error');
          $newsletter_error = true;
        }

        if ($newsletter_error == false) {
          $sql_data_array = array('title' => $title,
                                  'content' => $content,
                                  'module' => $newsletter_module);

          if ($action == 'insert') {
            $sql_data_array['date_added'] = 'now()';
            $sql_data_array['status'] = '0';
            $sql_data_array['locked'] = '0';

            $OSCOM_Db->save('newsletters', $sql_data_array);
            $newsletter_id = $OSCOM_Db->lastInsertId();
          } elseif ($action == 'update') {
            $OSCOM_Db->save('newsletters', $sql_data_array, ['newsletters_id' => (int)$newsletter_id]);
          }

          OSCOM::redirect(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $newsletter_id);
        } else {
          $action = 'new';
        }
        break;
      case 'deleteconfirm':
        $newsletter_id = HTML::sanitize($_GET['nID']);

        $OSCOM_Db->delete('newsletters', ['newsletters_id' => (int)$newsletter_id]);

        OSCOM::redirect(FILENAME_NEWSLETTERS, 'page=' . $_GET['page']);
        break;
      case 'delete':
      case 'new': if (!isset($_GET['nID'])) break;
      case 'send':
      case 'confirm_send':
        $newsletter_id = HTML::sanitize($_GET['nID']);

        $Qcheck = $OSCOM_Db->get('newsletters', 'locked', ['newsletters_id' => (int)$newsletter_id]);

        if ($Qcheck->fetch() !== false) {
          if ($Qcheck->valueInt('locked') < 1) {
            switch ($action) {
              case 'delete': $error = ERROR_REMOVE_UNLOCKED_NEWSLETTER; break;
              case 'new': $error = ERROR_EDIT_UNLOCKED_NEWSLETTER; break;
              case 'send': $error = ERROR_SEND_UNLOCKED_NEWSLETTER; break;
              case 'confirm_send': $error = ERROR_SEND_UNLOCKED_NEWSLETTER; break;
            }

            $OSCOM_MessageStack->add($error, 'error');

            OSCOM::redirect(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']);
          }
        }
        break;
    }
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
<?php
  if ($action == 'new') {
    $form_action = 'insert';

    $parameters = array('title' => '',
                        'content' => '',
                        'module' => '');

    $nInfo = new objectInfo($parameters);

    if (isset($_GET['nID'])) {
      $form_action = 'update';

      $nID = HTML::sanitize($_GET['nID']);

      $Qnewsletter = $OSCOM_Db->get('newsletters', [
        'title',
        'content',
        'module'
      ], [
        'newsletters_id' => (int)$nID
      ]);

      $nInfo->objectInfo($Qnewsletter->toArray());
    } elseif ($_POST) {
      $nInfo->objectInfo($_POST);
    }

    $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
    $directory_array = array();
    if ($dir = dir(DIR_WS_MODULES . 'newsletters/')) {
      while ($file = $dir->read()) {
        if (!is_dir(DIR_WS_MODULES . 'newsletters/' . $file)) {
          if (substr($file, strrpos($file, '.')) == $file_extension) {
            $directory_array[] = $file;
          }
        }
      }
      sort($directory_array);
      $dir->close();
    }

    for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
      $modules_array[] = array('id' => substr($directory_array[$i], 0, strrpos($directory_array[$i], '.')), 'text' => substr($directory_array[$i], 0, strrpos($directory_array[$i], '.')));
    }
?>
      <tr><?php echo HTML::form('newsletter', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&action=' . $form_action)); if ($form_action == 'update') echo HTML::hiddenField('newsletter_id', $nID); ?>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_NEWSLETTER_MODULE; ?></td>
            <td class="main"><?php echo HTML::selectField('module', $modules_array, $nInfo->module); ?></td>
          </tr>
          <tr>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_NEWSLETTER_TITLE; ?></td>
            <td class="main"><?php echo HTML::inputField('title', $nInfo->title) . TEXT_FIELD_REQUIRED; ?></td>
          </tr>
          <tr>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php echo TEXT_NEWSLETTER_CONTENT; ?></td>
            <td class="main"><?php echo HTML::textareaField('content', '100%', '20', $nInfo->content); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="smallText" align="right"><?php echo HTML::button(IMAGE_SAVE, 'fa fa-save', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&' . (isset($_GET['nID']) ? 'nID=' . $_GET['nID'] : ''))); ?></td>
          </tr>
        </table></td>
      </form></tr>
<?php
  } elseif ($action == 'preview') {
    $nID = HTML::sanitize($_GET['nID']);

    $Qnewsletter = $OSCOM_Db->get('newsletters', [
      'title',
      'content',
      'module'
    ], [
      'newsletters_id' => (int)$nID
    ]);

    $nInfo = new objectInfo($Qnewsletter->toArray());
?>
      <tr>
        <td class="smallText" align="right"><?php echo HTML::button(IMAGE_BACK, 'fa fa-chevron-left', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'])); ?></td>
      </tr>
      <tr>
        <td><tt><?php echo nl2br($nInfo->content); ?></tt></td>
      </tr>
      <tr>
        <td class="smallText" align="right"><?php echo HTML::button(IMAGE_BACK, 'fa fa-chevron-left', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'])); ?></td>
      </tr>
<?php
  } elseif ($action == 'send') {
    $nID = HTML::sanitize($_GET['nID']);

    $Qnewsletter = $OSCOM_Db->get('newsletters', [
      'title',
      'content',
      'module'
    ], [
      'newsletters_id' => (int)$nID
    ]);

    $nInfo = new objectInfo($Qnewsletter->toArray());

    include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    include(DIR_WS_MODULES . 'newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    $module_name = $nInfo->module;
    $module = new $module_name($nInfo->title, $nInfo->content);
?>
      <tr>
        <td><?php if ($module->show_choose_audience) { echo $module->choose_audience(); } else { echo $module->confirm(); } ?></td>
      </tr>
<?php
  } elseif ($action == 'confirm') {
    $nID = HTML::sanitize($_GET['nID']);

    $Qnewsletter = $OSCOM_Db->get('newsletters', [
      'title',
      'content',
      'module'
    ], [
      'newsletters_id' => (int)$nID
    ]);

    $nInfo = new objectInfo($Qnewsletter->toArray());

    include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    include(DIR_WS_MODULES . 'newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    $module_name = $nInfo->module;
    $module = new $module_name($nInfo->title, $nInfo->content);
?>
      <tr>
        <td><?php echo $module->confirm(); ?></td>
      </tr>
<?php
  } elseif ($action == 'confirm_send') {
    $nID = HTML::sanitize($_GET['nID']);

    $Qnewsletter = $OSCOM_Db->get('newsletters', [
      'newsletters_id',
      'title',
      'content',
      'module'
    ], [
      'newsletters_id' => (int)$nID
    ]);

    $nInfo = new objectInfo($Qnewsletter->toArray());

    include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    include(DIR_WS_MODULES . 'newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    $module_name = $nInfo->module;
    $module = new $module_name($nInfo->title, $nInfo->content);
?>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" valign="middle"><?php echo HTML::image(DIR_WS_IMAGES . 'ani_send_email.gif', IMAGE_ANI_SEND_EMAIL); ?></td>
            <td class="main" valign="middle"><strong><?php echo TEXT_PLEASE_WAIT; ?></strong></td>
          </tr>
        </table></td>
      </tr>
<?php
  tep_set_time_limit(0);
  flush();
  $module->send($nInfo->newsletters_id);
?>
      <tr>
        <td class="main"><font color="#ff0000"><strong><?php echo TEXT_FINISHED_SENDING_EMAILS; ?></strong></font></td>
      </tr>
      <tr>
        <td class="smallText"><?php echo HTML::button(IMAGE_BACK, 'fa fa-chevron-left', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'])); ?></td>
      </tr>
<?php
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NEWSLETTERS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_SIZE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_MODULE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_SENT; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $Qnewsletters = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS newsletters_id, title, length(content) as content_length, module, date_added, date_sent, status, locked from :table_newsletters order by date_added desc limit :page_set_offset, :page_set_max_results');
    $Qnewsletters->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
    $Qnewsletters->execute();

    while ($Qnewsletters->fetch()) {
    if ((!isset($_GET['nID']) || (isset($_GET['nID']) && ((int)$_GET['nID'] === $Qnewsletters->valueInt('newsletters_id')))) && !isset($nInfo) && (substr($action, 0, 3) != 'new')) {
        $nInfo = new objectInfo($Qnewsletters->toArray());
      }

      if (isset($nInfo) && is_object($nInfo) && ($Qnewsletters->valueInt('newsletters_id') === (int)$nInfo->newsletters_id) ) {
        echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $Qnewsletters->valueInt('newsletters_id')) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $Qnewsletters->valueInt('newsletters_id') . '&action=preview') . '">' . HTML::image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;' . $Qnewsletters->value('title'); ?></td>
                <td class="dataTableContent" align="right"><?php echo number_format($Qnewsletters->valueInt('content_length')) . ' bytes'; ?></td>
                <td class="dataTableContent" align="right"><?php echo $Qnewsletters->value('module'); ?></td>
                <td class="dataTableContent" align="center"><?php if ($Qnewsletters->valueInt('status') === 1) { echo HTML::image(DIR_WS_ICONS . 'tick.gif', ICON_TICK); } else { echo HTML::image(DIR_WS_ICONS . 'cross.gif', ICON_CROSS); } ?></td>
                <td class="dataTableContent" align="center"><?php if ($Qnewsletters->valueInt('locked') > 0) { echo HTML::image(DIR_WS_ICONS . 'locked.gif', ICON_LOCKED); } else { echo HTML::image(DIR_WS_ICONS . 'unlocked.gif', ICON_UNLOCKED); } ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($nInfo) && is_object($nInfo) && ($Qnewsletters->valueInt('newsletters_id') === (int)$nInfo->newsletters_id) ) { echo HTML::image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $Qnewsletters->valueInt('newsletters_id')) . '">' . HTML::image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="6"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qnewsletters->getPageSetLabel(TEXT_DISPLAY_NUMBER_OF_NEWSLETTERS); ?></td>
                    <td class="smallText" align="right"><?php echo $Qnewsletters->getPageSetLinks(); ?></td>
                  </tr>
                  <tr>
                    <td class="smallText" align="right" colspan="2"><?php echo HTML::button(IMAGE_NEW_NEWSLETTER, 'fa fa-plus', OSCOM::link(FILENAME_NEWSLETTERS, 'action=new')); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<strong>' . $nInfo->title . '</strong>');

      $contents = array('form' => HTML::form('newsletters', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=deleteconfirm')));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><strong>' . $nInfo->title . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_DELETE, 'fa fa-trash', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'])));
      break;
    default:
      if (isset($nInfo) && is_object($nInfo)) {
        $heading[] = array('text' => '<strong>' . $nInfo->title . '</strong>');

        if ($nInfo->locked > 0) {
          $contents[] = array('align' => 'center', 'text' => HTML::button(IMAGE_EDIT, 'fa fa-edit', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=new')) . HTML::button(IMAGE_DELETE, 'fa fa-trash', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=delete')) . HTML::button(IMAGE_PREVIEW, 'fa fa-file-o', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview')) . HTML::button(IMAGE_SEND, 'fa fa-envelope', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=send')) . HTML::button(IMAGE_UNLOCK, 'fa fa-unlock', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=unlock')));
        } else {
          $contents[] = array('align' => 'center', 'text' => HTML::button(IMAGE_PREVIEW, 'fa fa-file-o', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview')) . HTML::button(IMAGE_LOCK, 'fa fa-lock', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=lock')));
        }
        $contents[] = array('text' => '<br />' . TEXT_NEWSLETTER_DATE_ADDED . ' ' . tep_date_short($nInfo->date_added));
        if ($nInfo->status == '1') $contents[] = array('text' => TEXT_NEWSLETTER_DATE_SENT . ' ' . tep_date_short($nInfo->date_sent));
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
<?php
  }
?>
    </table>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
