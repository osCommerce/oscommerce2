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

  $banner_extension = tep_banner_image_extension();

  if (tep_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if ( ($_GET['flag'] == '0') || ($_GET['flag'] == '1') ) {
          tep_set_banner_status($_GET['bID'], $_GET['flag']);

          $messageStack->add_session(SUCCESS_BANNER_STATUS_UPDATED, 'success');
        } else {
          $messageStack->add_session(ERROR_UNKNOWN_STATUS_FLAG, 'error');
        }

        OSCOM::redirect(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $_GET['bID']);
        break;
      case 'insert':
      case 'update':
        if (isset($_POST['banners_id'])) $banners_id = HTML::sanitize($_POST['banners_id']);
        $banners_title = HTML::sanitize($_POST['banners_title']);
        $banners_url = HTML::sanitize($_POST['banners_url']);
        $new_banners_group = HTML::sanitize($_POST['new_banners_group']);
        $banners_group = (empty($new_banners_group)) ? HTML::sanitize($_POST['banners_group']) : $new_banners_group;
        $banners_html_text = HTML::sanitize($_POST['banners_html_text']);
        $banners_image_local = HTML::sanitize($_POST['banners_image_local']);
        $banners_image_target = HTML::sanitize($_POST['banners_image_target']);
        $db_image_location = '';
        $expires_date = HTML::sanitize($_POST['expires_date']);
        $expires_impressions = HTML::sanitize($_POST['expires_impressions']);
        $date_scheduled = HTML::sanitize($_POST['date_scheduled']);

        $banner_error = false;
        if (empty($banners_title)) {
          $messageStack->add(ERROR_BANNER_TITLE_REQUIRED, 'error');
          $banner_error = true;
        }

        if (empty($banners_group)) {
          $messageStack->add(ERROR_BANNER_GROUP_REQUIRED, 'error');
          $banner_error = true;
        }

        if (empty($banners_html_text)) {
          if (empty($banners_image_local)) {
            $banners_image = new upload('banners_image');
            $banners_image->set_destination(DIR_FS_CATALOG_IMAGES . $banners_image_target);
            if ( ($banners_image->parse() == false) || ($banners_image->save() == false) ) {
              $banner_error = true;
            }
          }
        }

        if ($banner_error == false) {
          $db_image_location = (tep_not_null($banners_image_local)) ? $banners_image_local : $banners_image_target . $banners_image->filename;
          $sql_data_array = array('banners_title' => $banners_title,
                                  'banners_url' => $banners_url,
                                  'banners_image' => $db_image_location,
                                  'banners_group' => $banners_group,
                                  'banners_html_text' => $banners_html_text,
                                  'expires_date' => 'null',
                                  'expires_impressions' => 0,
                                  'date_scheduled' => 'null');

          if ($action == 'insert') {
            $insert_sql_data = array('date_added' => 'now()',
                                     'status' => '1');

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            $OSCOM_Db->save('banners', $sql_data_array);

            $banners_id = $OSCOM_Db->lastInsertId();

            $messageStack->add_session(SUCCESS_BANNER_INSERTED, 'success');
          } elseif ($action == 'update') {
            $OSCOM_Db->save('banners', $sql_data_array, ['banners_id' => (int)$banners_id]);

            $messageStack->add_session(SUCCESS_BANNER_UPDATED, 'success');
          }

          if (tep_not_null($expires_date)) {
            $expires_date = substr($expires_date, 0, 4) . substr($expires_date, 5, 2) . substr($expires_date, 8, 2);

            $OSCOM_Db->save('banners', [
              'expires_date' => $expires_date,
              'expires_impressions' => 'null'
            ], [
              'banners_id' => (int)$banners_id
            ]);
          } elseif (tep_not_null($expires_impressions)) {
            $OSCOM_Db->save('banners', [
              'expires_impressions' => $expires_impressions,
              'expires_date' => 'null'
            ], [
              'banners_id' => (int)$banners_id
            ]);
          }

          if (tep_not_null($date_scheduled)) {
            $date_scheduled = substr($date_scheduled, 0, 4) . substr($date_scheduled, 5, 2) . substr($date_scheduled, 8, 2);

            $OSCOM_Db->save('banners', [
              'status' => '0',
              'date_scheduled' => $date_scheduled
            ],
            [
              'banners_id' => (int)$banners_id
            ]);
          }

          OSCOM::redirect(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $banners_id);
        } else {
          $action = 'new';
        }
        break;
      case 'deleteconfirm':
        $banners_id = HTML::sanitize($_GET['bID']);

        if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {
          $Qbanner = $OSCOM_Db->get('banners', 'banners_image', ['banners_id' => (int)$banners_id]);

          if (tep_not_null($Qbanner->value('banners_image')) && file_exists(DIR_FS_CATALOG_IMAGES . $Qbanner->value('banners_image')) && is_file(DIR_FS_CATALOG_IMAGES . $Qbanner->value('banners_image'))) {
            if (tep_is_writable(DIR_FS_CATALOG_IMAGES . $Qbanner->value('banners_image'))) {
              unlink(DIR_FS_CATALOG_IMAGES . $Qbanner->value('banners_image'));
            } else {
              $messageStack->add_session(ERROR_IMAGE_IS_NOT_WRITEABLE, 'error');
            }
          } else {
            $messageStack->add_session(ERROR_IMAGE_DOES_NOT_EXIST, 'error');
          }
        }

        $OSCOM_Db->delete('banners', ['banners_id' => (int)$banners_id]);
        $OSCOM_Db->delete('banners_history', ['banners_id' => (int)$banners_id]);

        if (function_exists('imagecreate') && tep_not_null($banner_extension)) {
          if (is_file(DIR_WS_IMAGES . 'graphs/banner_infobox-' . (int)$banners_id . '.' . $banner_extension)) {
            if (tep_is_writable(DIR_WS_IMAGES . 'graphs/banner_infobox-' . (int)$banners_id . '.' . $banner_extension)) {
              unlink(DIR_WS_IMAGES . 'graphs/banner_infobox-' . (int)$banners_id . '.' . $banner_extension);
            }
          }

          if (is_file(DIR_WS_IMAGES . 'graphs/banner_yearly-' . (int)$banners_id . '.' . $banner_extension)) {
            if (tep_is_writable(DIR_WS_IMAGES . 'graphs/banner_yearly-' . (int)$banners_id . '.' . $banner_extension)) {
              unlink(DIR_WS_IMAGES . 'graphs/banner_yearly-' . (int)$banners_id . '.' . $banner_extension);
            }
          }

          if (is_file(DIR_WS_IMAGES . 'graphs/banner_monthly-' . (int)$banners_id . '.' . $banner_extension)) {
            if (tep_is_writable(DIR_WS_IMAGES . 'graphs/banner_monthly-' . (int)$banners_id . '.' . $banner_extension)) {
              unlink(DIR_WS_IMAGES . 'graphs/banner_monthly-' . (int)$banners_id . '.' . $banner_extension);
            }
          }

          if (is_file(DIR_WS_IMAGES . 'graphs/banner_daily-' . (int)$banners_id . '.' . $banner_extension)) {
            if (tep_is_writable(DIR_WS_IMAGES . 'graphs/banner_daily-' . (int)$banners_id . '.' . $banner_extension)) {
              unlink(DIR_WS_IMAGES . 'graphs/banner_daily-' . (int)$banners_id . '.' . $banner_extension);
            }
          }
        }

        $messageStack->add_session(SUCCESS_BANNER_REMOVED, 'success');

        OSCOM::redirect(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page']);
        break;
      case 'preview':
        $banners_id = HTML::sanitize($_GET['banner']);

        $Qbanner = $OSCOM_Db->get('banners', [
          'banners_title',
          'banners_image',
          'banners_html_text'
        ], [
          'banners_id' => (int)$banners_id
        ]);

        if ($Qbanner->check()) {
          echo '<h1>' . $Qbanner->valueProtected('banners_title') . '</h1>';

          if (tep_not_null($Qbanner->value('banners_html_text'))) {
            echo $banner['banners_html_text'];
          } elseif (tep_not_null($Qbanner->value('banners_image'))) {
            echo HTML::image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $Qbanner->value('banners_image'), $Qbanner->value('banners_title'));
          }

          exit;
        }
        break;
    }
  }

// check if the graphs directory exists
  $dir_ok = false;
  if (function_exists('imagecreate') && tep_not_null($banner_extension)) {
    if (is_dir(DIR_WS_IMAGES . 'graphs')) {
      if (tep_is_writable(DIR_WS_IMAGES . 'graphs')) {
        $dir_ok = true;
      } else {
        $messageStack->add(ERROR_GRAPHS_DIRECTORY_NOT_WRITEABLE, 'error');
      }
    } else {
      $messageStack->add(ERROR_GRAPHS_DIRECTORY_DOES_NOT_EXIST, 'error');
    }
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<script type="text/javascript"><!--
function popupImageWindow(url) {
  window.open(url,'popupImageWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no');
}
//--></script>

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

    $parameters = array('expires_date' => '',
                        'date_scheduled' => '',
                        'banners_title' => '',
                        'banners_url' => '',
                        'banners_group' => '',
                        'banners_image' => '',
                        'banners_html_text' => '',
                        'expires_impressions' => '');

    $bInfo = new objectInfo($parameters);

    if (isset($_GET['bID'])) {
      $form_action = 'update';

      $bID = HTML::sanitize($_GET['bID']);

      $Qbanner = $OSCOM_Db->get('banners', [
        'banners_title',
        'banners_url',
        'banners_image',
        'banners_group',
        'banners_html_text',
        'status',
        'date_format(date_scheduled, "%Y/%m/%d") as date_scheduled',
        'date_format(expires_date, "%Y/%m/%d") as expires_date',
        'expires_impressions',
        'date_status_change'
      ], [
        'banners_id' => (int)$bID
      ]);

      $bInfo->objectInfo($Qbanner->toArray());
    } elseif (tep_not_null($_POST)) {
      $bInfo->objectInfo($_POST);
    }

    $groups_array = array();
    $Qgroups = $OSCOM_Db->get('banners', 'distinct banners_group', null, 'banners_group');
    while ($Qgroups->fetch()) {
      $groups_array[] = [
        'id' => $Qgroups->value('banners_group'),
        'text' => $Qgroups->value('banners_group')
      ];
    }
?>
      <tr><?php echo HTML::form('new_banner', OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&action=' . $form_action), 'post', 'enctype="multipart/form-data"'); if ($form_action == 'update') echo HTML::hiddenField('banners_id', $bID); ?>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_TITLE; ?></td>
            <td class="main"><?php echo HTML::inputField('banners_title', $bInfo->banners_title) . TEXT_FIELD_REQUIRED; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_URL; ?></td>
            <td class="main"><?php echo HTML::inputField('banners_url', $bInfo->banners_url); ?></td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php echo TEXT_BANNERS_GROUP; ?></td>
            <td class="main"><?php echo HTML::selectField('banners_group', $groups_array, $bInfo->banners_group) . TEXT_BANNERS_NEW_GROUP . '<br />' . HTML::inputField('new_banners_group') . ((sizeof($groups_array) > 0) ? '' : TEXT_FIELD_REQUIRED); ?></td>
          </tr>
          <tr>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php echo TEXT_BANNERS_IMAGE; ?></td>
            <td class="main"><?php echo HTML::fileField('banners_image') . ' ' . TEXT_BANNERS_IMAGE_LOCAL . '<br />' . DIR_FS_CATALOG_IMAGES . HTML::inputField('banners_image_local', (isset($bInfo->banners_image) ? $bInfo->banners_image : '')); ?></td>
          </tr>
          <tr>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_IMAGE_TARGET; ?></td>
            <td class="main"><?php echo DIR_FS_CATALOG_IMAGES . HTML::inputField('banners_image_target'); ?></td>
          </tr>
          <tr>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td valign="top" class="main"><?php echo TEXT_BANNERS_HTML_TEXT; ?></td>
            <td class="main"><?php echo HTML::textareaField('banners_html_text', '60', '5', $bInfo->banners_html_text); ?></td>
          </tr>
          <tr>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_SCHEDULED_AT; ?></td>
            <td class="main"><?php echo HTML::inputField('date_scheduled', $bInfo->date_scheduled, 'id="date_scheduled"') . ' <small>(YYYY-MM-DD)</small>'; ?></td>
          </tr>
          <tr>
            <td colspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td valign="top" class="main"><?php echo TEXT_BANNERS_EXPIRES_ON; ?></td>
            <td class="main"><?php echo HTML::inputField('expires_date', $bInfo->expires_date, 'id="expires_date"') . ' <small>(YYYY-MM-DD)</small>' . TEXT_BANNERS_OR_AT . '<br />' . HTML::inputField('expires_impressions', $bInfo->expires_impressions, 'maxlength="7" size="7"') . ' ' . TEXT_BANNERS_IMPRESSIONS; ?></td>
          </tr>
        </table>

<script type="text/javascript">
$('#date_scheduled').datepicker({
  dateFormat: 'yy-mm-dd'
});
$('#expires_date').datepicker({
  dateFormat: 'yy-mm-dd'
});
</script>

        </td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_BANNERS_BANNER_NOTE . '<br />' . TEXT_BANNERS_INSERT_NOTE . '<br />' . TEXT_BANNERS_EXPIRCY_NOTE . '<br />' . TEXT_BANNERS_SCHEDULE_NOTE; ?></td>
            <td class="smallText" align="right" valign="top" nowrap><?php echo HTML::button(IMAGE_SAVE, 'fa fa-save', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . (isset($_GET['bID']) ? '&bID=' . $_GET['bID'] : ''))); ?></td>
          </tr>
        </table></td>
      </form></tr>
<?php
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_BANNERS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_GROUPS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATISTICS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $Qbanners = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS banners_id, banners_title, banners_image, banners_group, status, expires_date, expires_impressions, date_status_change, date_scheduled, date_added from :table_banners order by banners_title, banners_group limit :page_set_offset, :page_set_max_results');
    $Qbanners->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
    $Qbanners->execute();

    while ($Qbanners->fetch()) {
      $Qinfo = $OSCOM_Db->get('banners_history', [
        'sum(banners_shown) as banners_shown',
        'sum(banners_clicked) as banners_clicked'
      ], [
        'banners_id' => $Qbanners->valueInt('banners_id')
      ]);

      if ((!isset($_GET['bID']) || (isset($_GET['bID']) && ((int)$_GET['bID'] === $Qbanners->valueInt('banners_id')))) && !isset($bInfo) && (substr($action, 0, 3) != 'new')) {
        $bInfo_array = array_merge($Qbanners->toArray(), $Qinfo->toArray());
        $bInfo = new objectInfo($bInfo_array);
      }

      if (isset($bInfo) && is_object($bInfo) && ($Qbanners->valueInt('banners_id') === (int)$bInfo->banners_id)) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_BANNER_STATISTICS, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id) . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $Qbanners->valueInt('banners_id')) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="javascript:popupImageWindow(\'' . OSCOM::link(FILENAME_BANNER_MANAGER, 'action=preview&banner=' . $Qbanners->valueInt('banners_id')) . '\');">' . HTML::image(DIR_WS_IMAGES . 'icon_popup.gif', 'View Banner') . '</a>&nbsp;' . $Qbanners->value('banners_title'); ?></td>
                <td class="dataTableContent" align="right"><?php echo $Qbanners->value('banners_group'); ?></td>
                <td class="dataTableContent" align="right"><?php echo $Qinfo->valueInt('banners_shown') . ' / ' . $Qinfo->valueInt('banners_clicked'); ?></td>
                <td class="dataTableContent" align="right">
<?php
      if ($Qbanners->value('status') == '1') {
        echo HTML::image(DIR_WS_IMAGES . 'icon_status_green.gif', 'Active', 10, 10) . '&nbsp;&nbsp;<a href="' . OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $Qbanners->valueInt('banners_id') . '&action=setflag&flag=0') . '">' . HTML::image(DIR_WS_IMAGES . 'icon_status_red_light.gif', 'Set Inactive', 10, 10) . '</a>';
      } else {
        echo '<a href="' . OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $Qbanners->valueInt('banners_id') . '&action=setflag&flag=1') . '">' . HTML::image(DIR_WS_IMAGES . 'icon_status_green_light.gif', 'Set Active', 10, 10) . '</a>&nbsp;&nbsp;' . HTML::image(DIR_WS_IMAGES . 'icon_status_red.gif', 'Inactive', 10, 10);
      }
?></td>
                <td class="dataTableContent" align="right"><?php echo '<a href="' . OSCOM::link(FILENAME_BANNER_STATISTICS, 'page=' . $_GET['page'] . '&bID=' . $Qbanners->valueInt('banners_id')) . '">' . HTML::image(DIR_WS_ICONS . 'statistics.gif', ICON_STATISTICS) . '</a>&nbsp;'; if (isset($bInfo) && is_object($bInfo) && ($Qbanners->valueInt('banners_id') === (int)$bInfo->banners_id)) { echo HTML::image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $Qbanners->valueInt('banners_id')) . '">' . HTML::image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qbanners->getPageSetLabel(TEXT_DISPLAY_NUMBER_OF_BANNERS); ?></td>
                    <td class="smallText" align="right"><?php echo $Qbanners->getPageSetLinks(); ?></td>
                  </tr>
                  <tr>
                    <td class="smallText" align="right" colspan="2"><?php echo HTML::button(IMAGE_NEW_BANNER, 'fa fa-plus', OSCOM::link(FILENAME_BANNER_MANAGER, 'action=new')); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();
  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<strong>' . $bInfo->banners_title . '</strong>');

      $contents = array('form' => HTML::form('banners', OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=deleteconfirm')));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><strong>' . $bInfo->banners_title . '</strong>');
      if ($bInfo->banners_image) $contents[] = array('text' => '<br />' . HTML::checkboxField('delete_image', 'on', true) . ' ' . TEXT_INFO_DELETE_IMAGE);
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(IMAGE_DELETE, 'fa fa-trash', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $_GET['bID'])));
      break;
    default:
      if (is_object($bInfo)) {
        $heading[] = array('text' => '<strong>' . $bInfo->banners_title . '</strong>');

        $contents[] = array('align' => 'center', 'text' => HTML::button(IMAGE_EDIT, 'fa fa-edit', OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=new')) . HTML::button(IMAGE_DELETE, 'fa fa-trash', OSCOM::link(FILENAME_BANNER_MANAGER, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id . '&action=delete')) . HTML::button(IMAGE_DETAILS, 'fa fa-info-circle', OSCOM::link(FILENAME_BANNER_STATISTICS, 'page=' . $_GET['page'] . '&bID=' . $bInfo->banners_id)));
        $contents[] = array('text' => '<br />' . TEXT_BANNERS_DATE_ADDED . ' ' . tep_date_short($bInfo->date_added));

        if ( (function_exists('imagecreate')) && ($dir_ok) && ($banner_extension) ) {
          $banner_id = $bInfo->banners_id;
          $days = '3';
          include(DIR_WS_INCLUDES . 'graphs/banner_infobox.php');
          $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::image(DIR_WS_IMAGES . 'graphs/banner_infobox-' . $banner_id . '.' . $banner_extension));
        } else {
          include(DIR_WS_FUNCTIONS . 'html_graphs.php');
          $contents[] = array('align' => 'center', 'text' => '<br />' . tep_banner_graph_infoBox($bInfo->banners_id, '3'));
        }

        $contents[] = array('text' => HTML::image(DIR_WS_IMAGES . 'graph_hbar_blue.gif', 'Blue', '5', '5') . ' ' . TEXT_BANNERS_BANNER_VIEWS . '<br />' . HTML::image(DIR_WS_IMAGES . 'graph_hbar_red.gif', 'Red', '5', '5') . ' ' . TEXT_BANNERS_BANNER_CLICKS);

        if ($bInfo->date_scheduled) $contents[] = array('text' => '<br />' . sprintf(TEXT_BANNERS_SCHEDULED_AT_DATE, tep_date_short($bInfo->date_scheduled)));

        if ($bInfo->expires_date) {
          $contents[] = array('text' => '<br />' . sprintf(TEXT_BANNERS_EXPIRES_AT_DATE, tep_date_short($bInfo->expires_date)));
        } elseif ($bInfo->expires_impressions) {
          $contents[] = array('text' => '<br />' . sprintf(TEXT_BANNERS_EXPIRES_AT_IMPRESSIONS, $bInfo->expires_impressions));
        }

        if ($bInfo->date_status_change) $contents[] = array('text' => '<br />' . sprintf(TEXT_BANNERS_STATUS_CHANGE, tep_date_short($bInfo->date_status_change)));
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
