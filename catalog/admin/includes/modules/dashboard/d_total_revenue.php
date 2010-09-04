<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  class d_total_revenue {
    var $code = 'd_total_revenue';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_total_revenue() {
      $this->title = MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_DESCRIPTION;

      if ( defined('MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_STATUS == 'True');
      }
    }

    function getOutput() {
      $days = array();
      for($i = 0; $i < 30; $i++) {
        $days[date('Y-m-d', strtotime('-'. $i .' days'))] = 0;
      }

      $orders_query = tep_db_query("select date_format(o.date_purchased, '%Y-%m-%d') as dateday, sum(ot.value) as total from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot where date_sub(curdate(), interval 30 day) <= o.date_purchased and o.orders_id = ot.orders_id and ot.class = 'ot_total' group by dateday");
      while ($orders = tep_db_fetch_array($orders_query)) {
        $days[$orders['dateday']] = $orders['total'];
      }

      $days = array_reverse($days, true);

      $js_array = '';
      foreach ($days as $date => $total) {
        $js_array .= '[' . (mktime(0, 0, 0, substr($date, 5, 2), substr($date, 8, 2), substr($date, 0, 4))*1000) . ', ' . $total . '],';
      }

      if (!empty($js_array)) {
        $js_array = substr($js_array, 0, -1);
      }

      $output = '<div id="d_total_revenue" style="width: 100%; height: 150px;"></div>' .
                '<script language="javascript" type="text/javascript">' .
                '$(function () {' .
                '  var plot30 = [' . $js_array . '];' .
                '  $.plot($("#d_total_revenue"), [ {' .
                '    label: "' . tep_output_string(MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_CHART_LINK) . '",' .
                '    data: plot30,' .
                '    lines: { show: true, fill: true },' .
                '    color: "#66CC33"' .
                '  }], {' .
                '    xaxis: {' .
                '      ticks: 4,' .
                '      mode: "time"' .
                '    },' .
                '    yaxis: {' .
                '      ticks: 3,' .
                '      min: 0' .
                '    },' .
                '    grid: {' .
                '      backgroundColor: { colors: ["#fff", "#eee"] }' .
                '    },' .
                '    legend: {' .
                '      labelFormatter: function(label, series) {' .
                '        return \'<a href="' . tep_href_link(FILENAME_ORDERS) . '">\' + label + \'</a>\';' .
                '      }' .
                '    }' .
                '  });' .
                '});' .
                '</script>';

      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Total Revenue Module', 'MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_STATUS', 'True', 'Do you want to show the total revenue chart on the dashboard?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_STATUS', 'MODULE_ADMIN_DASHBOARD_TOTAL_REVENUE_SORT_ORDER');
    }
  }
?>
