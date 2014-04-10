<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class bm_order_history {
    var $code = 'bm_order_history';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function bm_order_history() {
      $this->title = MODULE_BOXES_ORDER_HISTORY_TITLE;
      $this->description = MODULE_BOXES_ORDER_HISTORY_DESCRIPTION;

      if ( defined('MODULE_BOXES_ORDER_HISTORY_STATUS') ) {
        $this->sort_order = MODULE_BOXES_ORDER_HISTORY_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_ORDER_HISTORY_STATUS == 'True');

        $this->group = ((MODULE_BOXES_ORDER_HISTORY_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function execute() {
      global $customer_id, $languages_id, $PHP_SELF, $oscTemplate;

      if (tep_session_is_registered('customer_id')) {
// retreive the last x products purchased
        $orders_query = tep_db_query("select distinct op.products_id from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_PRODUCTS . " p where o.customers_id = '" . (int)$customer_id . "' and o.orders_id = op.orders_id and op.products_id = p.products_id and p.products_status = '1' group by products_id order by o.date_purchased desc limit " . MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX);
        if (tep_db_num_rows($orders_query)) {
          $product_ids = '';
          while ($orders = tep_db_fetch_array($orders_query)) {
            $product_ids .= (int)$orders['products_id'] . ',';
          }
          $product_ids = substr($product_ids, 0, -1);

          $customer_orders_string = '<table border="0" width="100%" cellspacing="0" cellpadding="1" class="ui-widget-content infoBoxContents">';
          $products_query = tep_db_query("select products_id, products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id in (" . $product_ids . ") and language_id = '" . (int)$languages_id . "' order by products_name");
          while ($products = tep_db_fetch_array($products_query)) {
            $customer_orders_string .= '  <tr>' .
                                       '    <td><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products['products_id']) . '">' . $products['products_name'] . '</a></td>' .
                                       '    <td align="right" valign="top"><a href="' . tep_href_link($PHP_SELF, tep_get_all_get_params(array('action')) . 'action=cust_order&pid=' . $products['products_id']) . '">' . tep_image(DIR_WS_ICONS . 'cart.gif', ICON_CART) . '</a></td>' .
                                       '  </tr>';
          }
          $customer_orders_string .= '</table>';

          $data = '<div class="ui-widget infoBoxContainer">' .
                  '  <div class="ui-widget-header infoBoxHeading">' . MODULE_BOXES_ORDER_HISTORY_BOX_TITLE . '</div>' .
                  '  ' . $customer_orders_string .
                  '</div>';

          $oscTemplate->addBlock($data, $this->group);
        }
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_ORDER_HISTORY_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Order History Module', 'MODULE_BOXES_ORDER_HISTORY_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Placement', 'MODULE_BOXES_ORDER_HISTORY_CONTENT_PLACEMENT', 'Right Column', 'Should the module be loaded in the left or right column?', '6', '1', 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_ORDER_HISTORY_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_BOXES_ORDER_HISTORY_STATUS', 'MODULE_BOXES_ORDER_HISTORY_CONTENT_PLACEMENT', 'MODULE_BOXES_ORDER_HISTORY_SORT_ORDER');
    }
  }
?>
