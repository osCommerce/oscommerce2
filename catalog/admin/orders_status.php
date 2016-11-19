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

  if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    $_GET['page'] = 1;
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
      case 'save':
        if (isset($_GET['oID'])) $orders_status_id = HTML::sanitize($_GET['oID']);

        $languages = tep_get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $orders_status_name_array = $_POST['orders_status_name'];
          $language_id = $languages[$i]['id'];

          $sql_data_array = array('orders_status_name' => HTML::sanitize($orders_status_name_array[$language_id]),
                                  'public_flag' => ((isset($_POST['public_flag']) && ($_POST['public_flag'] == '1')) ? '1' : '0'),
                                  'downloads_flag' => ((isset($_POST['downloads_flag']) && ($_POST['downloads_flag'] == '1')) ? '1' : '0'));

          if ($action == 'insert') {
            if (empty($orders_status_id)) {
              $Qnext = $OSCOM_Db->get('orders_status', 'max(orders_status_id) as orders_status_id');
              $orders_status_id = $Qnext->valueInt('orders_status_id') + 1;
            }

            $insert_sql_data = array('orders_status_id' => $orders_status_id,
                                     'language_id' => $language_id);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            $OSCOM_Db->save('orders_status', $sql_data_array);
          } elseif ($action == 'save') {
            $OSCOM_Db->save('orders_status',
              $sql_data_array,
            [
              'orders_status_id' => (int)$orders_status_id,
              'language_id' => (int)$language_id
            ]);
          }
        }

        if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
          $OSCOM_Db->save('configuration', [
            'configuration_value' => $orders_status_id
          ], [
            'configuration_key' => 'DEFAULT_ORDERS_STATUS_ID'
          ]);
        }

        OSCOM::redirect(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $orders_status_id);
        break;
      case 'deleteconfirm':
        $oID = HTML::sanitize($_GET['oID']);

        $Qstatus = $OSCOM_Db->get('configuration', 'configuration_value', ['configuration_key' => 'DEFAULT_ORDERS_STATUS_ID']);

        if ($Qstatus->value('configuration_value') == $oID) {
          $OSCOM_Db->save('configuration', [
            'configuration_value' => ''
          ], [
            'configuration_key' => 'DEFAULT_ORDERS_STATUS_ID'
          ]);
        }

        $OSCOM_Db->delete('orders_status', ['orders_status_id' => $oID]);

        OSCOM::redirect(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page']);
        break;
      case 'delete':
        $oID = HTML::sanitize($_GET['oID']);

        $Qstatus = $OSCOM_Db->get('orders', 'orders_status', ['orders_status' => (int)$oID], null, 1);

        $remove_status = true;
        if ($oID == DEFAULT_ORDERS_STATUS_ID) {
          $remove_status = false;
          $OSCOM_MessageStack->add(OSCOM::getDef('error_remove_default_order_status'), 'error');
        } elseif ($Qstatus->fetch() !== false) {
          $remove_status = false;
          $OSCOM_MessageStack->add(OSCOM::getDef('error_status_used_in_orders'), 'error');
        } else {
          $Qhistory = $OSCOM_Db->get('orders_status_history', 'orders_status_id', ['orders_status_id' => (int)$oID], null, 1);
          if ($Qhistory->fetch() !== false) {
            $remove_status = false;
            $OSCOM_MessageStack->add(OSCOM::getDef('error_status_used_in_history'), 'error');
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
      <tr>
        <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo OSCOM::getDef('table_heading_orders_status'); ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo OSCOM::getDef('table_heading_public_status'); ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo OSCOM::getDef('table_heading_downloads_status'); ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo OSCOM::getDef('table_heading_action'); ?>&nbsp;</td>
              </tr>
<?php
  $Qstatus = $OSCOM_Db->prepare('select SQL_CALC_FOUND_ROWS * from :table_orders_status where language_id = :language_id order by orders_status_id limit :page_set_offset, :page_set_max_results');
  $Qstatus->bindInt(':language_id', $OSCOM_Language->getId());
  $Qstatus->setPageSet(MAX_DISPLAY_SEARCH_RESULTS);
  $Qstatus->execute();

  while ($Qstatus->fetch()) {
    if ((!isset($_GET['oID']) || (isset($_GET['oID']) && ((int)$_GET['oID'] === $Qstatus->valueInt('orders_status_id')))) && !isset($oInfo) && (substr($action, 0, 3) != 'new')) {
      $oInfo = new objectInfo($Qstatus->toArray());
    }

    if (isset($oInfo) && is_object($oInfo) && ($Qstatus->valueInt('orders_status_id') === (int)$oInfo->orders_status_id)) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . OSCOM::link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $Qstatus->valueInt('orders_status_id')) . '\'">' . "\n";
    }

    if ((int)DEFAULT_ORDERS_STATUS_ID == $Qstatus->valueInt('orders_status_id')) {
      echo '                <td class="dataTableContent"><strong>' . $Qstatus->value('orders_status_name') . ' (' . OSCOM::getDef('text_default') . ')</strong></td>' . "\n";
    } else {
      echo '                <td class="dataTableContent">' . $Qstatus->value('orders_status_name') . '</td>' . "\n";
    }
?>
                <td class="dataTableContent" align="center"><?php echo HTML::image(OSCOM::linkImage('icons/' . (($Qstatus->valueInt('public_flag') === 1) ? 'tick.gif' : 'cross.gif'))); ?></td>
                <td class="dataTableContent" align="center"><?php echo HTML::image(OSCOM::linkImage('icons/' . (($Qstatus->valueInt('downloads_flag') === 1) ? 'tick.gif' : 'cross.gif'))); ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($oInfo) && is_object($oInfo) && ($Qstatus->valueInt('orders_status_id') === (int)$oInfo->orders_status_id)) { echo HTML::image(OSCOM::linkImage('icon_arrow_right.gif'), ''); } else { echo '<a href="' . OSCOM::link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $Qstatus->valueInt('orders_status_id')) . '">' . HTML::image(OSCOM::linkImage('icon_info.gif'), OSCOM::getDef('image_icon_info')) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $Qstatus->getPageSetLabel(OSCOM::getDef('text_display_number_of_orders_status')); ?></td>
                    <td class="smallText" align="right"><?php echo $Qstatus->getPageSetLinks(); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td class="smallText" colspan="2" align="right"><?php echo HTML::button(OSCOM::getDef('image_insert'), 'fa fa-plus', OSCOM::link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&action=new')); ?></td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_new_orders_status') . '</strong>');

      $contents = array('form' => HTML::form('status', OSCOM::link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&action=insert')));
      $contents[] = array('text' => OSCOM::getDef('text_info_insert_intro'));

      $orders_status_inputs_string = '';
      $languages = tep_get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $orders_status_inputs_string .= '<br />' . $OSCOM_Language->getImage($languages[$i]['code']) . '&nbsp;' . HTML::inputField('orders_status_name[' . $languages[$i]['id'] . ']');
      }

      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_orders_status_name') . $orders_status_inputs_string);
      $contents[] = array('text' => '<br />' . HTML::checkboxField('public_flag', '1') . ' ' . OSCOM::getDef('text_set_public_status'));
      $contents[] = array('text' => HTML::checkboxField('downloads_flag', '1') . ' ' . OSCOM::getDef('text_set_downloads_status'));
      $contents[] = array('text' => '<br />' . HTML::checkboxField('default') . ' ' . OSCOM::getDef('text_set_default'));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_save'), 'fa fa-save') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'])));
      break;
    case 'edit':
      $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_edit_orders_status') . '</strong>');

      $contents = array('form' => HTML::form('status', OSCOM::link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id  . '&action=save')));
      $contents[] = array('text' => OSCOM::getDef('text_info_edit_intro'));

      $orders_status_inputs_string = '';
      $languages = tep_get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $orders_status_inputs_string .= '<br />' . $OSCOM_Language->getImage($languages[$i]['code']) . '&nbsp;' . HTML::inputField('orders_status_name[' . $languages[$i]['id'] . ']', tep_get_orders_status_name($oInfo->orders_status_id, $languages[$i]['id']));
      }

      $contents[] = array('text' => '<br />' . OSCOM::getDef('text_info_orders_status_name') . $orders_status_inputs_string);
      $contents[] = array('text' => '<br />' . HTML::checkboxField('public_flag', '1', $oInfo->public_flag) . ' ' . OSCOM::getDef('text_set_public_status'));
      $contents[] = array('text' => HTML::checkboxField('downloads_flag', '1', $oInfo->downloads_flag) . ' ' . OSCOM::getDef('text_set_downloads_status'));
      if (DEFAULT_ORDERS_STATUS_ID != $oInfo->orders_status_id) $contents[] = array('text' => '<br />' . HTML::checkboxField('default') . ' ' . OSCOM::getDef('text_set_default'));
      $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_save'), 'fa fa-save') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id)));
      break;
    case 'delete':
      $heading[] = array('text' => '<strong>' . OSCOM::getDef('text_info_heading_delete_orders_status') . '</strong>');

      $contents = array('form' => HTML::form('status', OSCOM::link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id  . '&action=deleteconfirm')));
      $contents[] = array('text' => OSCOM::getDef('text_info_delete_intro'));
      $contents[] = array('text' => '<br /><strong>' . $oInfo->orders_status_name . '</strong>');
      if ($remove_status) $contents[] = array('align' => 'center', 'text' => '<br />' . HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id)));
      break;
    default:
      if (isset($oInfo) && is_object($oInfo)) {
        $heading[] = array('text' => '<strong>' . $oInfo->orders_status_name . '</strong>');

        $contents[] = array('align' => 'center', 'text' => HTML::button(OSCOM::getDef('image_edit'), 'fa fa-edit', OSCOM::link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id . '&action=edit')) . HTML::button(OSCOM::getDef('image_delete'), 'fa fa-trash', OSCOM::link(FILENAME_ORDERS_STATUS, 'page=' . $_GET['page'] . '&oID=' . $oInfo->orders_status_id . '&action=delete')));

        $orders_status_inputs_string = '';
        $languages = tep_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $orders_status_inputs_string .= '<br />' . $OSCOM_Language->getImage($languages[$i]['code']) . '&nbsp;' . tep_get_orders_status_name($oInfo->orders_status_id, $languages[$i]['id']);
        }

        $contents[] = array('text' => $orders_status_inputs_string);
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
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
