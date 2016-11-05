<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\DateTime;
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
          $OSCOM_MessageStack->add(OSCOM::getDef('error_newsletter_title'), 'error');
          $newsletter_error = true;
        }

        if (empty($newsletter_module)) {
          $OSCOM_MessageStack->add(OSCOM::getDef('error_newsletter_module'), 'error');
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
              case 'delete': $error = OSCOM::getDef('error_remove_unlocked_newsletter'); break;
              case 'new': $error = OSCOM::getDef('error_edit_unlocked_newsletter'); break;
              case 'send': $error = OSCOM::getDef('error_send_unlocked_newsletter'); break;
              case 'confirm_send': $error = OSCOM::getDef('error_send_unlocked_newsletter'); break;
            }

            $OSCOM_MessageStack->add($error, 'error');

            OSCOM::redirect(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID']);
          }
        }
        break;
    }
  }

  require($oscTemplate->getFile('template_top.php'));
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo OSCOM::getDef('heading_title'); ?></td>
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
    if ($dir = dir('includes/modules/newsletters/')) {
      while ($file = $dir->read()) {
        if (!is_dir('includes/modules/newsletters/' . $file)) {
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
            <td class="main"><?php echo OSCOM::getDef('text_newsletter_module'); ?></td>
            <td class="main"><?php echo HTML::selectField('module', $modules_array, $nInfo->module); ?></td>
          </tr>
          <tr>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td class="main"><?php echo OSCOM::getDef('text_newsletter_title'); ?></td>
            <td class="main"><?php echo HTML::inputField('title', $nInfo->title) . OSCOM::getDef('text_field_required'); ?></td>
          </tr>
          <tr>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php echo OSCOM::getDef('text_newsletter_content'); ?></td>
            <td class="main"><?php echo HTML::textareaField('content', '100%', '20', $nInfo->content); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="smallText" align="right"><?php echo HTML::button(OSCOM::getDef('image_save'), 'fa fa-save') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&' . (isset($_GET['nID']) ? 'nID=' . $_GET['nID'] : ''))); ?></td>
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
        <td class="smallText" align="right"><?php echo HTML::button(OSCOM::getDef('image_back'), 'fa fa-chevron-left', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'])); ?></td>
      </tr>
      <tr>
        <td><tt><?php echo nl2br($nInfo->content); ?></tt></td>
      </tr>
      <tr>
        <td class="smallText" align="right"><?php echo HTML::button(OSCOM::getDef('image_back'), 'fa fa-chevron-left', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'])); ?></td>
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

    $OSCOM_Language->loadDefinitions('modules/newsletters/' . $nInfo->module);
    include('includes/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
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

    $OSCOM_Language->loadDefinitions('modules/newsletters/' . $nInfo->module);
    include('includes/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
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

    $OSCOM_Language->loadDefinitions('modules/newsletters/' . $nInfo->module);
    include('includes/modules/newsletters/' . $nInfo->module . substr($PHP_SELF, strrpos($PHP_SELF, '.')));
    $module_name = $nInfo->module;
    $module = new $module_name($nInfo->title, $nInfo->content);
?>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" valign="middle"><?php echo HTML::image(OSCOM::linkImage('ani_send_email.gif'), OSCOM::getDef('image_ani_send_email')); ?></td>
            <td class="main" valign="middle"><strong><?php echo OSCOM::getDef('text_please_wait'); ?></strong></td>
          </tr>
        </table></td>
      </tr>
<?php
  tep_set_time_limit(0);
  flush();
  $module->send($nInfo->newsletters_id);
?>
      <tr>
        <td class="main"><font color="#ff0000"><strong><?php echo OSCOM::getDef('text_finished_sending_emails'); ?></strong></font></td>
      </tr>
      <tr>
        <td class="smallText"><?php echo HTML::button(OSCOM::getDef('image_back'), 'fa fa-chevron-left', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'])); ?></td>
      </tr>
<?php
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_newsletters'); ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo OSCOM::getDef('table_heading_size'); ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo OSCOM::getDef('table_heading_module'); ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo OSCOM::getDef('table_heading_sent'); ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo OSCOM::getDef('table_heading_status'); ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo OSCOM::getDef('table_heading_action'); ?>&nbsp;</td>
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
                <td class="dataTableContent"><?php echo '<a href="' . OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $Qnewsletters->valueInt('newsletters_id') . '&action=preview') . '">' . HTML::image(OSCOM::linkImage('icons/preview.gif'), OSCOM::getDef('icon_preview')) . '</a>&nbsp;' . $Qnewsletters->value('title'); ?></td>
                <td class="dataTableContent" align="right"><?php echo number_format($Qnewsletters->valueInt('content_length')) . ' bytes'; ?></td>
                <td class="dataTableContent" align="right"><?php echo $Qnewsletters->value('module'); ?></td>
                <td class="dataTableContent" align="center"><?php if ($Qnewsletters->valueInt('status') === 1) { echo HTML::image(OSCOM::linkImage('icons/tick.gif'), OSCOM::getDef('icon_tick')); } else { echo HTML::image(OSCOM::linkImage('icons/cross.gif'), OSCOM::getDef('icon_cross')); } ?></td>
                <td class="dataTableContent" align="center"><?php if ($Qnewsletters->valueInt('locked') > 0) { echo HTML::image(OSCOM::linkImage('icons/locked.gif'), OSCOM::getDef('icon_locked')); } else { echo HTML::image(OSCOM::linkImage('icons/unlocked.gif'), OSCOM::getDef('icon_unlocked')); } ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($nInfo) && is_object($nInfo) && ($Qnewsletters->valueInt('newsletters_id') === (int)$nInfo->newsletters_id) ) { echo HTML::image(OSCOM::linkImage('icon_arrow_right.gif'), ''); } else { echo '<a href="' . OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $Qnewsletters->valueInt('newsletters_id')) . '">' . HTML::image(OSCOM::linkImage('icon_info.gif'), OSCOM::getDef('image_icon_info')) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="6"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qnewsletters->getPageSetLabel(OSCOM::getDef('text_display_number_of_newsletters')); ?></td>
                    <td class="smallText" align="right"><?php echo $Qnewsletters->getPageSetLinks(); ?></td>
                  </tr>
                  <tr>
                    <td class="smallText" align="right" colspan="2"><?php echo HTML::button(OSCOM::getDef('image_new_newsletter'), 'fa fa-plus', OSCOM::link(FILENAME_NEWSLETTERS, 'action=new')); ?></td>
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
      $contents[] = array('text' => OSCOM::getDef('text_info_delete_intro'));
      $contents[] = array('text' => '<br /><strong>' . $nInfo->title . '</strong>');
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'])));
      break;
    default:
      if (isset($nInfo) && is_object($nInfo)) {
        $heading[] = array('text' => '<strong>' . $nInfo->title . '</strong>');

        if ($nInfo->locked > 0) {
          $contents[] = array('align' => 'center', 'text' => HTML::button(OSCOM::getDef('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=new')) . HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=delete')) . HTML::button(OSCOM::getDef('image_preview'), 'fa fa-file-o', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview')) . HTML::button(OSCOM::getDef('image_send'), 'fa fa-envelope', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=send')) . HTML::button(OSCOM::getDef('image_unlock'), 'fa fa-unlock', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=unlock')));
        } else {
          $contents[] = array('align' => 'center', 'text' => HTML::button(OSCOM::getDef('image_preview'), 'fa fa-file-o', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=preview')) . HTML::button(OSCOM::getDef('image_lock'), 'fa fa-lock', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $nInfo->newsletters_id . '&action=lock')));
        }
        $contents[] = array('text' => '<br />' . OSCOM::getDef('text_newsletter_date_added') . ' ' . DateTime::toShort($nInfo->date_added));
        if ($nInfo->status == '1') $contents[] = array('text' => OSCOM::getDef('text_newsletter_date_sent') . ' ' . DateTime::toShort($nInfo->date_sent));
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
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
