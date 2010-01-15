<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
  $directory_array = array();
  if ($dir = @dir(DIR_FS_CATALOG_MODULES . 'action_recorder/')) {
    while ($file = $dir->read()) {
      if (!is_dir(DIR_FS_CATALOG_MODULES . 'action_recorder/' . $file)) {
        if (substr($file, strrpos($file, '.')) == $file_extension) {
          $directory_array[] = $file;
        }
      }
    }
    sort($directory_array);
    $dir->close();
  }

  $expired_entries = 0;

  for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
    $file = $directory_array[$i];

    if (file_exists(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/action_recorder/' . $file)) {
      include(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/action_recorder/' . $file);
    }

    include(DIR_FS_CATALOG_MODULES . 'action_recorder/' . $file);

    $class = substr($file, 0, strrpos($file, '.'));
    if (tep_class_exists($class)) {
      $$class = new $class;
      $expired_entries += $$class->expireEntries();
    }
  }

  if ($expired_entries > 0) {
    $messageStack->add(sprintf(SUCCESS_EXPIRED_ENTRIES, $expired_entries), 'success');
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus();">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
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
                <td class="dataTableHeadingContent" width="20">&nbsp;</td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODULE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMER; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_DATE_ADDED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $actions_query_raw = "select ar.id, ar.module, ar.customer_id, ar.success, ar.date_added, c.customers_firstname, c.customers_lastname from " . TABLE_ACTION_RECORDER . " ar left join " . TABLE_CUSTOMERS . " c on (ar.customer_id = c.customers_id) order by ar.date_added desc";
  $actions_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $actions_query_raw, $actions_query_numrows);
  $actions_query = tep_db_query($actions_query_raw);
  while ($actions = tep_db_fetch_array($actions_query)) {
    $module = $actions['module'];

    if (is_object($$module)) {
      $module = $$module->_title;
    }

    if ((!isset($HTTP_GET_VARS['aID']) || (isset($HTTP_GET_VARS['aID']) && ($HTTP_GET_VARS['aID'] == $actions['id']))) && !isset($aInfo) && (substr($action, 0, 3) != 'new')) {
      $actions_extra_query = tep_db_query("select identifier from " . TABLE_ACTION_RECORDER . " where id = '" . (int)$actions['id'] . "'");
      $actions_extra = tep_db_fetch_array($actions_extra_query);

      $aInfo_array = array_merge($actions, $actions_extra, array('module' => $module));
      $aInfo = new objectInfo($aInfo_array);
    }

    if ( (isset($aInfo) && is_object($aInfo)) && ($actions['id'] == $aInfo->id) ) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_ACTION_RECORDER, 'aID=' . $actions['id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent" align="center"><?php echo tep_image(DIR_WS_IMAGES . 'icons/' . (($actions['success'] == '1') ? 'tick.gif' : 'cross.gif')); ?></td>
                <td class="dataTableContent"><?php echo $module; ?></td>
                <td class="dataTableContent"><?php echo ($actions['customer_id'] > 0 ? tep_output_string_protected($actions['customers_firstname'] . ' ' . $actions['customers_lastname']) : '(' . TEXT_GUEST . ')'); ?></td>
                <td class="dataTableContent" align="right"><?php echo tep_datetime_short($actions['date_added']); ?></td>
                <td class="dataTableContent" align="right"><?php if ( (isset($aInfo) && is_object($aInfo)) && ($actions['id'] == $aInfo->id) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_ACTION_RECORDER, 'aID=' . $actions['id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $actions_split->display_count($actions_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_ENTRIES); ?></td>
                    <td class="smallText" align="right"><?php echo $actions_split->display_links($actions_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    default:
      if (isset($aInfo) && is_object($aInfo)) {
        $heading[] = array('text' => '<b>' . $aInfo->module . '</b>');

        $contents[] = array('text' => TEXT_INFO_IDENTIFIER . '<br><br>' . $aInfo->identifier);
        $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . tep_datetime_short($aInfo->date_added));
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
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
