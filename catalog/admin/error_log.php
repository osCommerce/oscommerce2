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

  if (!defined('STORE_ERROR_LOG_FILE')) {
    tep_redirect(tep_href_link(FILENAME_DEFAULT));
  }

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

  if (tep_not_null($action)) {
    if ($action == 'delete') {
      $error = unlink(STORE_ERROR_LOG_FILE);
      if (!$error) {
        $messageStack->add_session(sprintf(ERROR_FILE_NOT_DELETED, STORE_ERROR_LOG_FILE), 'error');
      } else {
        $fh = fopen(STORE_ERROR_LOG_FILE, 'a');
        fclose($fh);
      }
      tep_redirect(tep_href_link(FILENAME_ERROR_LOG));
    }
  }

// check if the error file exists
  if (is_file(STORE_ERROR_LOG_FILE)) {
    if (!tep_is_writable(dirname(STORE_ERROR_LOG_FILE))) {
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
<?php
    if (STORE_ERROR_LOG_CUT_DATE == 'True') {
?>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ERROR_DATE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ERROR_TEXT; ?></td>
<?php
    } else {
?>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ERROR_TEXT; ?></td>
<?php
    }
?>
              </tr>
<?php
  if ( file_exists(STORE_ERROR_LOG_FILE) ) {
    $messages = array_reverse(file(STORE_ERROR_LOG_FILE));
  }

  $page = (isset($_GET['page']) ? $_GET['page'] : 1);

  $result = array('entries' => array(),
                  'total' => sizeof($messages)+1);

  if ( $page !== -1 ) {
   $messages = array_slice($messages, (MAX_DISPLAY_SEARCH_RESULTS * ($page - 1)), MAX_DISPLAY_SEARCH_RESULTS);
  }

  foreach ( $messages as $key => $message ) {
?>
              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
<?php
    if (STORE_ERROR_LOG_CUT_DATE == 'True') {
?>
                <td class="dataTableContent"><?php echo substr($message, 1, 20); ?></td>
                <td class="dataTableContent"><?php echo substr($message, 27); ?></td>
<?php
    } else {
?>
                <td class="dataTableContent"><?php echo $message; ?></td>
<?php
    }
?>
              </tr>
<?php
  }
?>
              <tr>
                <td class="smallText" colspan="3"><?php echo TEXT_ERROR_DIRECTORY . ' ' . STORE_ERROR_LOG_FILE; ?>
                </td>
              </tr>
                <td class="smallText" colspan="3" align="right"><?php echo 'Total: ' . ( ( $result['total'] > 1 ) ? (($result['total']-1)) : INFO_NO_ERRORS_IN_FILE ) . '&nbsp;' . tep_draw_button(EMPTY_FILE, 'trash', tep_href_link(FILENAME_ERROR_LOG, 'action=delete')); ?></td>
          </tr>
              <tr>
                <td class="smallText" colspan="3">
<?php
                if ($page > 1) {
                  echo '<a href="' . tep_href_link(FILENAME_ERROR_LOG, tep_get_all_get_params(array('page', 'action')) . 'page=' . ($page-1)) . '">' . PREVNEXT_BUTTON_PREV . '</a>';
                } else {
                  echo '|>';
                }
                echo $page . '/' . ceil(($result['total']-1)/MAX_DISPLAY_SEARCH_RESULTS);
                if ( ($page < (($result['total']-1)/MAX_DISPLAY_SEARCH_RESULTS)) && ((($result['total']-1)/MAX_DISPLAY_SEARCH_RESULTS) != 1) ) {
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
