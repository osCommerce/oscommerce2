<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  include(DIR_WS_CLASSES . 'phplot.php');

  $stats = array();
  $Qstats = $OSCOM_Db->get('banners_history', [
    'dayofmonth(banners_history_date) as name',
    'banners_shown as value',
    'banners_clicked as dvalue'
  ], [
    'banners_id' => $banner_id,
    'to_days(now()) - to_days(banners_history_date)' => [
      'op' => '<',
      'val' => $days
    ]
  ], 'banners_history_date');

  while ($Qstats->fetch()) {
    $stats[] = [
      $Qstats->value('name'),
      $Qstats->valueInt('value'),
      $Qstats->valueInt('dvalue')
    ];
  }

  if (sizeof($stats) < 1) $stats = array(array(date('j'), 0, 0));

  $graph = new PHPlot(200, 220, 'images/graphs/banner_infobox-' . $banner_id . '.' . $banner_extension);

  $graph->SetFileFormat($banner_extension);
  $graph->SetIsInline(1);
  $graph->SetPrintImage(0);

  $graph->draw_vert_ticks = 0;
  $graph->SetSkipBottomTick(1);
  $graph->SetDrawXDataLabels(0);
  $graph->SetDrawYGrid(0);
  $graph->SetPlotType('bars');
  $graph->SetDrawDataLabels(1);
  $graph->SetLabelScalePosition(1);
  $graph->SetMarginsPixels(15,15,15,30);

  $graph->SetTitleFontSize('4');
  $graph->SetTitle(TEXT_BANNERS_LAST_3_DAYS);

  $graph->SetDataValues($stats);
  $graph->SetDataColors(array('blue','red'),array('blue', 'red'));

  $graph->DrawGraph();

  $graph->PrintImage();
?>
