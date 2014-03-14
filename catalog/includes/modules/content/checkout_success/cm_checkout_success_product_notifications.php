<?php
/*
  $Id$ cs_product_notifications
  2013 G.L. Walker - http://wsfive.com
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2010 osCommerce
  Released under the GNU General Public License
*/

  class cm_checkout_success_product_notifications {
    var $code = 'cm_checkout_success_product_notifications';
    var $group = 'checkout_success';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_checkout_success_product_notifications() {
      $this->title = MODULE_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_TITLE . ' (' . $this->group . ')';
      $this->description = MODULE_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_DESCRIPTION;

	  if (defined('MODULE_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_STATUS')) {
        $this->sort_order = MODULE_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_SORT_ORDER;
        $this->enabled = (MODULE_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_STATUS == 'True');
      }
	}
	 
    function execute() {
      global $oscTemplate, $customer_id;
	  
      if (isset($_GET['action']) && ($_GET['action'] == 'update')) {
        $notify_string = '';

	  if (isset($_POST['notify']) && !empty($_POST['notify'])) {
        $notify = $_POST['notify'];

        if (!is_array($notify)) {
          $notify = array($notify);
		}

	    for ($i=0, $n=sizeof($notify); $i<$n; $i++) {
          if (is_numeric($notify[$i])) {
            $notify_string .= 'notify[]=' . $notify[$i] . '&';
        }
	  }

      if (!empty($notify_string)) {
        $notify_string = 'action=notify&' . substr($notify_string, 0, -1);
	  }
    }
	
    if (MODULE_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_LINK == 'index') {
      $actionLink = FILENAME_DEFAULT; 
	} else {
	  $actionLink = FILENAME_ACCOUNT; 	
	}
    tep_redirect(tep_href_link($actionLink, $notify_string));
  }
  
  if (tep_session_is_registered('customer_id')) {
    $global_query = tep_db_query("select global_product_notifications from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . (int)$customer_id . "'");
    $global = tep_db_fetch_array($global_query);

    if ($global['global_product_notifications'] != '1') {
      $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where customers_id = '" . (int)$customer_id . "' order by date_purchased desc limit 1");
      $orders = tep_db_fetch_array($orders_query);

      $products_array = array();
      $products_query = tep_db_query("select products_id, products_name from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$orders['orders_id'] . "' order by products_name");
	
      while ($products = tep_db_fetch_array($products_query)) {
        $products_array[] = array('id' => $products['products_id'],
                                'text' => $products['products_name']);
      }
    }
  
    $notification_data = '' . PHP_EOL;
  
    if ($global['global_product_notifications'] != '1') { 
	  $notification_data .= MODULE_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_TEXT_NOTIFY_PRODUCTS . '<br /><p class="productsNotifications">';
      $products_displayed = array();
      for ($i=0, $n=sizeof($products_array); $i<$n; $i++) {
	    if (!in_array($products_array[$i]['id'], $products_displayed)) {
		  $notification_data .= tep_draw_checkbox_field('notify[]', $products_array[$i]['id']) . ' ' . $products_array[$i]['text'] . '<br />'. PHP_EOL;
        }
      }
	
      $notification_data .= '</p>' . PHP_EOL;
    
      $cs_data = tep_draw_form('order', tep_href_link(FILENAME_CHECKOUT_SUCCESS, 'action=update', 'SSL')) .
                '  <div id="cs-prod-notice" class="ui-widget infoBoxContainer">' .
                '    <div class="ui-widget-header infoBoxHeading">' . MODULE_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_TITLE . '</div>' .
                '    <div class="ui-widget-content infoBoxContents">' . $notification_data . '<br>' .
				'      <div class="buttonSet">' . 
		        '        <span class="buttonAction">' . tep_draw_button(IMAGE_BUTTON_CONTINUE, 'triangle-1-e', null, 'primary') .'</span>' .
				'      </div>' .
				'      <div style="clear:both;"></div>' .
				'    </div>' .
				'  </div>' .
                '</form>' . PHP_EOL;

      $oscTemplate->addContent($cs_data, $this->group);
     }
    }
  }
    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Product Notification Module', 'MODULE_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
	  
	  tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Landing Page', 'MODULE_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_LINK', 'index', 'Page to redirect customer to upon submiting notifications', '6', '2', 'tep_cfg_select_option(array(\'index\', \'account\'), ', now())");

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '3', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_STATUS','MODULE_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_LINK', 'MODULE_CHECKOUT_SUCCESS_PRODUCT_NOTIFICATIONS_SORT_ORDER');
    }
  }