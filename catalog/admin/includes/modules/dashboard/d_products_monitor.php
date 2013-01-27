<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/


  class d_products_monitor {
    var $code = 'd_products_monitor';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function d_products_monitor() {
      $this->title = MODULE_ADMIN_DASHBOARD_PRODUCTS_MONITOR_TITLE;
      $this->description = MODULE_ADMIN_DASHBOARD_PRODUCTS_MONITOR_DESCRIPTION;

      if ( defined('MODULE_ADMIN_DASHBOARD_PRODUCTS_MONITOR_STATUS') ) {
        $this->sort_order = MODULE_ADMIN_DASHBOARD_PRODUCTS_MONITOR_SORT_ORDER;
        $this->enabled = (MODULE_ADMIN_DASHBOARD_PRODUCTS_MONITOR_STATUS == 'True');
      }
    }

    function getOutput() {
      global $languages_id;

      $products_query = tep_db_query("select pd.products_name, pd.products_id, p.products_last_modified, p.products_model, p.products_tax_class_id, p.products_image, p.products_price from " . TABLE_PRODUCTS_DESCRIPTION . " pd left join " . TABLE_PRODUCTS . " p on(pd.products_id = p.products_id) where (p.products_model= '' or p.products_model is null or p.products_image is null or p.products_image = '' or p.products_tax_class_id is null or p.products_tax_class_id = '0' or p.products_price is null) and pd.language_id = '" . (int)$languages_id . "' and p.products_status = '1' order by p.products_last_modified desc limit 6");

      if (!tep_db_num_rows($products_query)) {
        $output = '<div class="secSuccess">' .
                  '  <p class="smallText">' . MODULE_ADMIN_DASHBOARD_PERFECT_PRODUCTS . '</p>' .
                  '</div';

      } else {
        $output = '<table border="0" width="100%" cellspacing="0" cellpadding="4">' .
                  '  <tr class="dataTableHeadingRow">' .
                  '    <td class="dataTableHeadingContent">' . MODULE_ADMIN_DASHBOARD_MODEL_INFO_PRODUCTS_NAME . '</td>' .
                  '    <td class="dataTableHeadingContent">' . MODULE_ADMIN_DASHBOARD_PRODUCTS_MONITOR_PRODUCTS_ERRORS . '</td>' .
                  '    <td class="dataTableHeadingContent" align="right">' . MODULE_ADMIN_DASHBOARD_PRODUCTS_MONITOR_PRODUCTS_PRODUCTS_LAST_MODIFIED . '</td>' .
                  '  </tr>';
        while ($products = tep_db_fetch_array($products_query)) {
          $output .= '  <tr class="dataTableRow" onmouseover="rowOverEffect(this);" onmouseout="rowOutEffect(this);">' .
                     '    <td class="dataTableContent"><a style="color:red" href="' . tep_href_link(FILENAME_CATEGORIES, 'pID=' . (int)$products['products_id'] . '&action=new_product') . '">' . tep_output_string_protected($products['products_name']) . '</td>';

          $err_list = '';
          $list_no = false;
          if (!tep_not_null($products['products_model'])) {
                $err_list .= MODULE_ADMIN_DASHBOARD_PRODUCTS_MONITOR_NO_MODEL;
                $list_no = true;
          }
          if (!tep_not_null($products['products_image'])) {
                if ($list_no) {
                  $err_list .= ', ';
                }
                $err_list .= MODULE_ADMIN_DASHBOARD_PRODUCTS_MONITOR_NO_PICTURE;
                $list_no = true;
          }
          if ($products['products_tax_class_id'] == 0) {
                if ($list_no) {
                  $err_list .= ', ';
                }
                $err_list .= MODULE_ADMIN_DASHBOARD_PRODUCTS_MONITOR_NO_TAX;
                $list_no = true;
          }
          if ($products['products_price'] == 0) {
                if ($list_no) {
                  $err_list .= ', ';
                }
                $err_list .=  MODULE_ADMIN_DASHBOARD_PRODUCTS_MONITOR_NO_PRICE;
          }

          $output .= '    <td class="dataTableContent">' . $err_list . '</td>' .
                     '    <td class="dataTableContent" align="right">' . $products['products_last_modified'] . '</td>' .
                     '  </tr>';
        }
        $output .= '</table>';
      }
      return $output;
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_ADMIN_DASHBOARD_PRODUCTS_MONITOR_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Products Monitor Module', 'MODULE_ADMIN_DASHBOARD_PRODUCTS_MONITOR_STATUS', 'True', 'Do you want to show the products monitor on the dashboard?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_ADMIN_DASHBOARD_PRODUCTS_MONITOR_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '2', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_ADMIN_DASHBOARD_PRODUCTS_MONITOR_STATUS', 'MODULE_ADMIN_DASHBOARD_PRODUCTS_MONITOR_SORT_ORDER');
    }
  }
?>
