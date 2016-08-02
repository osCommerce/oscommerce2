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
  } else {
    $OSCOM_MessageStack->add('The "GD" extension must be enabled in your PHP configuration to generate images.', 'error');
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
                            'text' => STATISTICS_TYPE_MONTHLY));

  if (!empty($years_array)) {
    $type_array[] = array('id' => 'yearly',
                          'text' => STATISTICS_TYPE_YEARLY);
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

<div class="pull-right">
  <?= HTML::button(IMAGE_BACK, 'fa fa-caret-left', OSCOM::link('banner_manager.php', 'page=' . $_GET['page']), null, null, 'btn-info'); ?>
</div>

<h2><i class="fa fa-line-chart"></i> <a href="<?= OSCOM::link('banner_statistics.php', 'page=' . $_GET['page'] . '&bID=' . (int)$_GET['bID']); ?>"><?= HEADING_TITLE; ?></a></h2>

<?= HTML::form('year', OSCOM::link(FILENAME_BANNER_STATISTICS), 'get', 'class="form-inline"', ['session_id' => true]); ?>

<?= (isset($_GET['page']) ? HTML::hiddenField('page', $_GET['page']) : '') . HTML::hiddenField('bID', $_GET['bID']); ?>

<?= HTML::selectField('type', $type_array, (tep_not_null($type) ? $type : 'daily'), 'onchange="this.form.submit();"'); ?>

<?php
  switch ($type) {
    case 'yearly': break;
    case 'monthly':
      if (!empty($years_array)) {
        echo HTML::selectField('year', $years_array, (isset($_GET['year']) ? $_GET['year'] : date('Y')), 'onchange="this.form.submit();"');
      }
      break;
    case 'daily':
    default:
      echo HTML::selectField('month', $months_array, (isset($_GET['month']) ? $_GET['month'] : date('n')), 'onchange="this.form.submit();"');

      if (!empty($years_array)) {
        HTML::selectField('year', $years_array, (isset($_GET['year']) ? $_GET['year'] : date('Y')), 'onchange="this.form.submit();"');
      }
      break;
  }
?>

</form>

<?php
  if ($dir_ok === true) {
    $banner_id = (int)$_GET['bID'];
?>

<div class="row text-center">

<?php
    switch ($type) {
      case 'yearly':
        include(DIR_WS_INCLUDES . 'graphs/banner_yearly.php');
        echo HTML::image(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banner_id . '.' . $banner_extension);
        break;
      case 'monthly':
        include(DIR_WS_INCLUDES . 'graphs/banner_monthly.php');
        echo HTML::image(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banner_id . '.' . $banner_extension);
        break;
      case 'daily':
      default:
        include(DIR_WS_INCLUDES . 'graphs/banner_daily.php');
        echo HTML::image(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banner_id . '.' . $banner_extension);
        break;
    }
?>

</div>

<div class="row">
  <div class="col-md-6 col-md-offset-3">
    <table class="table">
      <thead>
        <tr class="warning">
          <th><?= TABLE_HEADING_SOURCE; ?></th>
          <th class="text-right"><?= TABLE_HEADING_VIEWS; ?></th>
          <th class="text-right"><?= TABLE_HEADING_CLICKS; ?></th>
        </tr>
      </thead>
      <tbody>

<?php
    for ($i=0, $n=sizeof($stats); $i<$n; $i++) {
      echo '<tr>
              <td>' . $stats[$i][0] . '</td>
              <td class="text-right">' . number_format($stats[$i][1]) . '</td>
              <td class="text-right">' . number_format($stats[$i][2]) . '</td>
            </tr>';
    }
?>

      </tbody>
    </table>
  </div>
</div>

<?php
  }

  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
