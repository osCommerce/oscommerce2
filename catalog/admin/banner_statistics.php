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

  $type = (isset($_GET['type']) ? $_GET['type'] : '');

  $banner_extension = tep_banner_image_extension();

// check if the graphs directory exists
  $dir_ok = false;
  if (function_exists('imagecreate') && tep_not_null($banner_extension)) {
    if (is_dir(DIR_WS_IMAGES . 'graphs')) {
      if (tep_is_writable(DIR_WS_IMAGES . 'graphs')) {
        $dir_ok = true;
      } else {
        $OSCOM_MessageStack->add(ERROR_GRAPHS_DIRECTORY_NOT_WRITEABLE, 'error');
      }
    } else {
      $OSCOM_MessageStack->add(ERROR_GRAPHS_DIRECTORY_DOES_NOT_EXIST, 'error');
    }
  }

  $Qbanner = $OSCOM_Db->get('banners', 'banners_title', ['banners_id' => (int)$_GET['bID']]);

  $years_array = array();
  $Qyears = $OSCOM_Db->get('banners_history', 'distinct year(banners_history_date) as banner_year', ['banners_id' => (int)$_GET['bID']]);
  while ($Qyears->fetch()) {
    $years_array[] = [
      'id' => $Qyears->valueInt('banner_year'),
      'text' => $Qyears->valueInt('banner_year')
    ];
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

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr><?php echo HTML::form('year', OSCOM::link(FILENAME_BANNER_STATISTICS), 'get', null, ['session_id' => true]); ?>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="main" align="right"><?php echo TITLE_TYPE . ' ' . HTML::selectField('type', $type_array, (tep_not_null($type) ? $type : 'daily'), 'onchange="this.form.submit();"'); ?><noscript><input type="submit" value="GO"></noscript><br />
<?php
  switch ($type) {
    case 'yearly': break;
    case 'monthly':
      echo TITLE_YEAR . ' ' . HTML::selectField('year', $years_array, (isset($_GET['year']) ? $_GET['year'] : date('Y')), 'onchange="this.form.submit();"') . '<noscript><input type="submit" value="GO"></noscript>';
      break;
    default:
    case 'daily':
      echo TITLE_MONTH . ' ' . HTML::selectField('month', $months_array, (isset($_GET['month']) ? $_GET['month'] : date('n')), 'onchange="this.form.submit();"') . '<noscript><input type="submit" value="GO"></noscript><br />' . TITLE_YEAR . ' ' . HTML::selectField('year', $years_array, (isset($_GET['year']) ? $_GET['year'] : date('Y')), 'onchange="this.form.submit();"') . '<noscript><input type="submit" value="GO"></noscript>';
      break;
  }
?>
            </td>
          <?php echo (isset($_GET['page']) ? HTML::hiddenField('page', $_GET['page']) : '') . HTML::hiddenField('bID', $_GET['bID']); ?></form></tr>
        </table></td>
      </tr>
      <tr>
        <td align="center">
<?php
  if (function_exists('imagecreate') && ($dir_ok == true) && tep_not_null($banner_extension)) {
    $banner_id = (int)$_GET['bID'];

    switch ($type) {
      case 'yearly':
        include(DIR_WS_INCLUDES . 'graphs/banner_yearly.php');
        echo HTML::image(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banner_id . '.' . $banner_extension);
        break;
      case 'monthly':
        include(DIR_WS_INCLUDES . 'graphs/banner_monthly.php');
        echo HTML::image(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banner_id . '.' . $banner_extension);
        break;
      default:
      case 'daily':
        include(DIR_WS_INCLUDES . 'graphs/banner_daily.php');
        echo HTML::image(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banner_id . '.' . $banner_extension);
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
        echo tep_banner_graph_yearly($_GET['bID']);
        break;
      case 'monthly':
        echo tep_banner_graph_monthly($_GET['bID']);
        break;
      default:
      case 'daily':
        echo tep_banner_graph_daily($_GET['bID']);
        break;
    }
  }
?>
        </td>
      </tr>
      <tr>
        <td class="smallText" align="right"><?php echo HTML::button(IMAGE_BACK, 'fa fa-chevron-left', OSCOM::link(FILENAME_BANNER_MANAGER, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'bID=' . $_GET['bID'])); ?></td>
      </tr>
    </table>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
