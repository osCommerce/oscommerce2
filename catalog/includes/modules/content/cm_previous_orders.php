<?php
/*
  $Id$ cs_previous_orders
  Copyright (c) 2013 Club osC www.clubosc.com
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2010 osCommerce
  Released under the GNU General Public License
*/

  class cm_previous_orders {
    var $code = 'cm_previous_orders';
	var $group = 'checkout_success';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_previous_orders() {
      global $PHP_SELF, $oscTemplate;

      $this->title = MODULE_CHECKOUT_SUCCESS_PREVIOUS_ORDERS_TITLE . ' (' . $this->group . ')';
      $this->description = MODULE_CHECKOUT_SUCCESS_PREVIOUS_ORDERS_DESCRIPTION;

      if ( defined('MODULE_CHECKOUT_SUCCESS_PREVIOUS_ORDERS_STATUS') ) {
        $this->sort_order = MODULE_CHECKOUT_SUCCESS_PREVIOUS_ORDERS_SORT_ORDER;
        $this->enabled = (MODULE_CHECKOUT_SUCCESS_PREVIOUS_ORDERS_STATUS == 'True');
      }

      if ( !isset($oscTemplate) || ($PHP_SELF != FILENAME_CHECKOUT_SUCCESS) ) {
        $this->enabled = false;
      }
    }
    
     function execute() {
      global $customer_id, $languages_id, $oscTemplate;

      if (tep_session_is_registered('customer_id')) {
		// retreive the last x products purchased
        $orders_query = tep_db_query("select distinct op.products_id from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_PRODUCTS . " p where o.customers_id = '" . (int)$customer_id . "' and o.orders_id = op.orders_id and op.products_id = p.products_id and p.products_status = '1' group by products_id order by o.date_purchased desc limit " . MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX);
        if (tep_db_num_rows($orders_query)) {
          $product_ids = '';
          while ($orders = tep_db_fetch_array($orders_query)) {
            $product_ids .= (int)$orders['products_id'] . ',';
          }
          $product_ids = substr($product_ids, 0, -1);

          $customer_orders_string = '<table border="0" width="100%" cellspacing="0" cellpadding="0">';
          $products_query = tep_db_query("select products_id, products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id in (" . $product_ids . ") and language_id = '" . (int)$languages_id . "' order by products_name");
          while ($products = tep_db_fetch_array($products_query)) {
            $customer_orders_string .= '  <tr>' .
                                       '    <td><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products['products_id']) . '">' . $products['products_name'] . '</a></td>' .
                                       '    <td align="right">';
            if ($this->hasReviewed((int)$products['products_id']) > 0) {
              $customer_orders_string .= '&nbsp;';
            }
            else {
              $customer_orders_string .= tep_draw_button(WRITE_REVIEW, 'pencil', tep_href_link(FILENAME_PRODUCT_REVIEWS_WRITE, 'products_id=' . (int)$products['products_id'] ));
            }
            $customer_orders_string .= '    </td>';

            $customer_orders_string .= '  </tr>';
          }
          $customer_orders_string .= '</table>';

          $cs_data = '<div id="cs-prev-orders" class="ui-widget infoBoxContainer">' .
                     '  <div class="ui-widget-header infoBoxHeading">' . MODULE_BOXES_ORDER_HISTORY_BOX_TITLE . '</div>' .
                     '  <div class="ui-widget-content infoBoxContents">' . $customer_orders_string . '</div>' .
                     '</div>';

          $oscTemplate->addBlock($cs_data, $this->group);
        }
      }
    }


    function isEnabled() {
      return $this->enabled;
    }
    
    function hasReviewed($products_id) {
      global $customer_id;
      $orders_check_query = tep_db_query("select count(*) as total from " . TABLE_REVIEWS . " where customers_id = '" . (int)$customer_id . "' and products_id = " . $products_id);
      $orders_check = tep_db_fetch_array($orders_check_query);

      return $orders_check['total'];
    }

    function check() {
      return defined('MODULE_CHECKOUT_SUCCESS_PREVIOUS_ORDERS_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Previous Orders Module', 'MODULE_CHECKOUT_SUCCESS_PREVIOUS_ORDERS_STATUS', 'True', 'Do you want to show the Previous Orders Module on the Success Page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CHECKOUT_SUCCESS_PREVIOUS_ORDERS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CHECKOUT_SUCCESS_PREVIOUS_ORDERS_STATUS', 'MODULE_CHECKOUT_SUCCESS_PREVIOUS_ORDERS_SORT_ORDER');
    }
  }