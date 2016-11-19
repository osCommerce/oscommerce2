<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class d_total_customers {
    var $code = 'd_total_customers';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_total_customers() {
      $this->title = OSCOM::getDef('module_admin_dashboard_total_customers_title');
      $this->description = OSCOM::getDef('module_admin_dashboard_total_customers_description');

      if ( defined('MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_STATUS == 'True');
      }
    }

    function getOutput() {
      $OSCOM_Db = Registry::get('Db');

      $days = array();
      for($i = 0; $i < 7; $i++) {
        $days[date('Y-m-d', strtotime('-'. $i .' days'))] = 0;
      }

      $Qorders = $OSCOM_Db->query('select date_format(customers_info_date_account_created, "%Y-%m-%d") as dateday, count(*) as total from :table_customers_info where date_sub(curdate(), interval 7 day) <= customers_info_date_account_created group by dateday');

      while ($Qorders->fetch()) {
        $days[$Qorders->value('dateday')] = $Qorders->value('total');
      }

      $days = array_reverse($days, true);

      $chart_label = HTML::output(OSCOM::getDef('module_admin_dashboard_total_customers_chart_link'));
      $chart_label_link = OSCOM::link(FILENAME_CUSTOMERS);

      $data_labels = json_encode(array_keys($days));
      $data = json_encode(array_values($days));

      $output = <<<EOD
<h5 class="text-center"><a href="$chart_label_link">$chart_label</a></h5>
<div id="d_total_customers"></div>
<script>
$(function() {
  var data = {
    labels: $data_labels,
    series: [ $data ]
  };

  var options = {
    fullWidth: true,
    height: '200px',
    showPoint: false,
    showArea: true,
    axisY: {
      labelInterpolationFnc: function skipLabels(value, index) {
        return index % 2  === 0 ? value : null;
      }
    }
  }

  var chart = new Chartist.Line('#d_total_customers', data, options);

  chart.on('draw', function(context) {
    if (context.type === 'line') {
      context.element.attr({
        style: 'stroke: green;'
      });
    } else if (context.type === 'area') {
      context.element.attr({
        style: 'fill: green;'
      });
    }
  });
});
</script>
EOD;

      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_STATUS');
    }

    function install() {
      $OSCOM_Db = Registry::get('Db');

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Enable Total Customers Module',
        'configuration_key' => 'MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_STATUS',
        'configuration_value' => 'True',
        'configuration_description' => 'Do you want to show the total customers chart on the dashboard?',
        'configuration_group_id' => '6',
        'sort_order' => '1',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
        'date_added' => 'now()'
      ]);

      $OSCOM_Db->save('configuration', [
        'configuration_title' => 'Sort Order',
        'configuration_key' => 'MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_SORT_ORDER',
        'configuration_value' => '0',
        'configuration_description' => 'Sort order of display. Lowest is displayed first.',
        'configuration_group_id' => '6',
        'sort_order' => '0',
        'date_added' => 'now()'
      ]);
    }

    function remove() {
      return Registry::get('Db')->exec('delete from :table_configuration where configuration_key in ("' . implode('", "', $this->keys()) . '")');
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_STATUS', 'MODULE_ADMIN_DASHBOARD_TOTAL_CUSTOMERS_SORT_ORDER');
    }
  }
?>
