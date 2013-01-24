<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/


  define('FILENAME_ERROR_LOG', 'error_log.php');

  require('includes/application_top.php');

  if (!defined('STORE_PHP_ERROR_LOG')) {
    tep_redirect(tep_href_link(FILENAME_DEFAULT));
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    if ($action == 'delete') {
      $error = unlink(STORE_PHP_ERROR_LOG);
      if (!$error) {
        $messageStack->add_session(sprintf(ERROR_FILE_NOT_DELETED, STORE_PHP_ERROR_LOG), 'error');
      } else {
        $fh = fopen(STORE_PHP_ERROR_LOG, 'a');
        fclose($fh);
      }
      tep_redirect(tep_href_link(FILENAME_ERROR_LOG));
    }
  }

// check if the error file exists
  if (is_file(STORE_PHP_ERROR_LOG)) {
    if (!tep_is_writable(dirname(STORE_PHP_ERROR_LOG))) {
      $messageStack->add(ERROR_DIRECTORY_NOT_WRITEABLE, 'error');
    }
  } else {
    $messageStack->add(ERROR_DIRECTORY_DOES_NOT_EXIST, 'error');
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ERROR_DATE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ERROR_TEXT; ?></td>
              </tr>
<?php
  if ( file_exists(STORE_PHP_ERROR_LOG) ) {
    $messages = array_reverse(file(STORE_PHP_ERROR_LOG));
  }

  $page = (isset($_GET['page']) ? $_GET['page'] : 1);

  $result = array('entries' => array(),
                  'total' => sizeof($messages)+1);

  if ( $page !== -1 ) {
   $messages = array_slice($messages, (MAX_DISPLAY_SEARCH_RESULTS * ($page - 1))*MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_LINES, MAX_DISPLAY_SEARCH_RESULTS*MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_LINES);
  }

  foreach ( $messages as $key => $message ) {
    $result['entries'][$key] = array( 'date' => substr($message, 1, 20),
                                      'message' => substr($message, 27));
?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
                <td class="dataTableContent"><?php echo $result['entries'][$key]['date'];
                ?></td>
                <td class="dataTableContent"><?php echo $result['entries'][$key]['message'];
                ?></td>
              </tr>
<?php
  }
?>
              <tr>
                <td class="smallText" colspan="3"><?php echo TEXT_ERROR_DIRECTORY . ' ' . STORE_PHP_ERROR_LOG; ?>
                </td>
              </tr>
                <td class="smallText" colspan="3" align="right"><?php echo 'Total: ' . ( ( $result['total'] > 1 ) ? (($result['total']-1)/MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_LINES) : INFO_NO_ERRORS_IN_FILE ) . '&nbsp;' . tep_draw_button(EMPTY_FILE, 'trash', tep_href_link(FILENAME_ERROR_LOG, 'action=delete')); ?></td>
          </tr>
              <tr>
                <td class="smallText" colspan="3">
<?php
                if ($page > 1) {
                  echo '<a href="' . tep_href_link(FILENAME_ERROR_LOG, tep_get_all_get_params(array('page', 'action')) . 'page=' . ($page-1)) . '">' . PREVNEXT_BUTTON_PREV . '</a>';
                } else {
                  echo '|>';
                }
                echo $page . '/' . ceil(($result['total']-1)/MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_LINES/MAX_DISPLAY_SEARCH_RESULTS);
                if ( ($page < (($result['total']-1)/(MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_LINES*MAX_DISPLAY_SEARCH_RESULTS))) && ((($result['total']-1)/(MODULE_ADMIN_DASHBOARD_LATEST_ERRORS_LINES*MAX_DISPLAY_SEARCH_RESULTS)) != 1) ) {
                  echo '<a href="' . tep_href_link(FILENAME_ERROR_LOG, tep_get_all_get_params(array('page', 'action')) . 'page=' . ($page+1)) . '">' . PREVNEXT_BUTTON_NEXT . '</a>';
                } else {
                  echo '<|';
                }
?>
                </td>
              </tr>
            </table></td>
        </table></td>
      </tr>
    </table>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
