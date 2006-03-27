<?php
/*
  $Id: banner_statistics.php,v 1.5 2003/06/20 00:30:15 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $type = (isset($HTTP_GET_VARS['type']) ? $HTTP_GET_VARS['type'] : '');

  $banner_extension = tep_banner_image_extension();

// check if the graphs directory exists
  $dir_ok = false;
  if (function_exists('imagecreate') && tep_not_null($banner_extension)) {
    if (is_dir(DIR_WS_IMAGES . 'graphs')) {
      if (is_writeable(DIR_WS_IMAGES . 'graphs')) {
        $dir_ok = true;
      } else {
        $messageStack->add(ERROR_GRAPHS_DIRECTORY_NOT_WRITEABLE, 'error');
      }
    } else {
      $messageStack->add(ERROR_GRAPHS_DIRECTORY_DOES_NOT_EXIST, 'error');
    }
  }

  $banner_query = tep_db_query("select banners_title from " . TABLE_BANNERS . " where banners_id = '" . (int)$HTTP_GET_VARS['bID'] . "'");
  $banner = tep_db_fetch_array($banner_query);

  $years_array = array();
  $years_query = tep_db_query("select distinct year(banners_history_date) as banner_year from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . (int)$HTTP_GET_VARS['bID'] . "'");
  while ($years = tep_db_fetch_array($years_query)) {
    $years_array[] = array('id' => $years['banner_year'],
                           'text' => $years['banner_year']);
  }

  $months_array = array();
  for ($i=1; $i<13; $i++) {
    $months_array[] = array('id' => $i,
                            'text' => strftime('%B', mktime(0,0,0,$i)));
  }

  $type_array = array(array('id' => 'daily',
                            'text' => STATISTICS_TYPE_DAILY),
                      array('id' => 'monthly',
                            'text' => STATISTICS_TYPE_MONTHLY),
                      array('id' => 'yearly',
                            'text' => STATISTICS_TYPE_YEARLY));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
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
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr><?php echo tep_draw_form('year', FILENAME_BANNER_STATISTICS, '', 'get'); ?>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', '1', HEADING_IMAGE_HEIGHT); ?></td>
            <td class="main" align="right"><?php echo TITLE_TYPE . ' ' . tep_draw_pull_down_menu('type', $type_array, (tep_not_null($type) ? $type : 'daily'), 'onChange="this.form.submit();"'); ?><noscript><input type="submit" value="GO"></noscript><br>
<?php
  switch ($type) {
    case 'yearly': break;
    case 'monthly':
      echo TITLE_YEAR . ' ' . tep_draw_pull_down_menu('year', $years_array, (isset($HTTP_GET_VARS['year']) ? $HTTP_GET_VARS['year'] : date('Y')), 'onChange="this.form.submit();"') . '<noscript><input type="submit" value="GO"></noscript>';
      break;
    default:
    case 'daily':
      echo TITLE_MONTH . ' ' . tep_draw_pull_down_menu('month', $months_array, (isset($HTTP_GET_VARS['month']) ? $HTTP_GET_VARS['month'] : date('n')), 'onChange="this.form.submit();"') . '<noscript><input type="submit" value="GO"></noscript><br>' . TITLE_YEAR . ' ' . tep_draw_pull_down_menu('year', $years_array, (isset($HTTP_GET_VARS['year']) ? $HTTP_GET_VARS['year'] : date('Y')), 'onChange="this.form.submit();"') . '<noscript><input type="submit" value="GO"></noscript>';
      break;
  }
?>
            </td>
          <?php echo tep_draw_hidden_field('page', $HTTP_GET_VARS['page']) . tep_draw_hidden_field('bID', $HTTP_GET_VARS['bID']); ?></form></tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td align="center">
<?php
  if (function_exists('imagecreate') && ($dir_ok == true) && tep_not_null($banner_extension)) {
    $banner_id = (int)$HTTP_GET_VARS['bID'];

    switch ($type) {
      case 'yearly':
        include(DIR_WS_INCLUDES . 'graphs/banner_yearly.php');
        echo tep_image(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banner_id . '.' . $banner_extension);
        break;
      case 'monthly':
        include(DIR_WS_INCLUDES . 'graphs/banner_monthly.php');
        echo tep_image(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banner_id . '.' . $banner_extension);
        break;
      default:
      case 'daily':
        include(DIR_WS_INCLUDES . 'graphs/banner_daily.php');
        echo tep_image(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banner_id . '.' . $banner_extension);
        break;
    }
?>
          <table border="0" width="600" cellspacing="0" cellpadding="2">
            <tr class="dataTableHeadingRow">
             <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SOURCE; ?></td>
             <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_VIEWS; ?></td>
             <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_CLICKS; ?></td>
           </tr>
<?php
    for ($i=0, $n=sizeof($stats); $i<$n; $i++) {
      echo '            <tr class="dataTableRow">' . "\n" .
           '              <td class="dataTableContent">' . $stats[$i][0] . '</td>' . "\n" .
           '              <td class="dataTableContent" align="right">' . number_format($stats[$i][1]) . '</td>' . "\n" .
           '              <td class="dataTableContent" align="right">' . number_format($stats[$i][2]) . '</td>' . "\n" .
           '            </tr>' . "\n";
    }
?>
          </table>
<?php
  } else {
    include(DIR_WS_FUNCTIONS . 'html_graphs.php');

    switch ($type) {
      case 'yearly':
        echo tep_banner_graph_yearly($HTTP_GET_VARS['bID']);
        break;
      case 'monthly':
        echo tep_banner_graph_monthly($HTTP_GET_VARS['bID']);
        break;
      default:
      case 'daily':
        echo tep_banner_graph_daily($HTTP_GET_VARS['bID']);
        break;
    }
  }
?>
        </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $HTTP_GET_VARS['page'] . '&bID=' . $HTTP_GET_VARS['bID']) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
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
