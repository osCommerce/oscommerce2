<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  class bm_product_notifications {
    var $code = 'bm_product_notifications';
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function bm_product_notifications() {
      $this->title = MODULE_BOXES_PRODUCT_NOTIFICATIONS_TITLE;
      $this->description = MODULE_BOXES_PRODUCT_NOTIFICATIONS_DESCRIPTION;

      if ( defined('MODULE_BOXES_PRODUCT_NOTIFICATIONS_STATUS') ) {
        $this->sort_order = MODULE_BOXES_PRODUCT_NOTIFICATIONS_SORT_ORDER;
        $this->enabled = (MODULE_BOXES_PRODUCT_NOTIFICATIONS_STATUS == 'True');

        $this->group = ((MODULE_BOXES_PRODUCT_NOTIFICATIONS_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
      }
    }

    function execute() {
      global $OSCOM_APP, $request_type, $oscTemplate;

      if ( ($OSCOM_APP->getCode() == 'products') && is_null($OSCOM_APP->getCurrentAction()) && isset($_GET['id']) && !empty($_GET['id']) ) {
        if (isset($_SESSION['customer_id'])) {
          $check_query = osc_db_query("select count(*) as count from " . TABLE_PRODUCTS_NOTIFICATIONS . " where products_id = '" . osc_get_prid($_GET['id']) . "' and customers_id = '" . (int)$_SESSION['customer_id'] . "'");
          $check = osc_db_fetch_array($check_query);

          $notification_exists = (($check['count'] > 0) ? true : false);
        } else {
          $notification_exists = false;
        }

        $notif_contents = '';

        if ($notification_exists == true) {
          $notif_contents = '<table border="0" cellspacing="0" cellpadding="2" class="ui-widget-content infoBoxContents"><tr><td><a href="' . osc_href_link(null, osc_get_all_get_params(array('action')) . 'action=notify_remove', $request_type) . '">' . osc_image(DIR_WS_IMAGES . 'box_products_notifications_remove.gif', IMAGE_BUTTON_REMOVE_NOTIFICATIONS) . '</a></td><td><a href="' . osc_href_link(null, osc_get_all_get_params(array('action')) . 'action=notify_remove', $request_type) . '">' . sprintf(MODULE_BOXES_PRODUCT_NOTIFICATIONS_BOX_NOTIFY_REMOVE, osc_get_products_name($_GET['id'])) .'</a></td></tr></table>';
        } else {
          $notif_contents = '<table border="0" cellspacing="0" cellpadding="2" class="ui-widget-content infoBoxContents"><tr><td><a href="' . osc_href_link(null, osc_get_all_get_params(array('action')) . 'action=notify', $request_type) . '">' . osc_image(DIR_WS_IMAGES . 'box_products_notifications.gif', IMAGE_BUTTON_NOTIFICATIONS) . '</a></td><td><a href="' . osc_href_link(null, osc_get_all_get_params(array('action')) . 'action=notify', $request_type) . '">' . sprintf(MODULE_BOXES_PRODUCT_NOTIFICATIONS_BOX_NOTIFY, osc_get_products_name($_GET['id'])) .'</a></td></tr></table>';
        }

        $data = '<li class="nav-header"><a href="' . osc_href_link('account', 'notifications', 'SSL') . '">' . MODULE_BOXES_PRODUCT_NOTIFICATIONS_BOX_TITLE . '</a></li>' .
                $notif_contents;

        $oscTemplate->addBlock($data, $this->group);
      }
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_BOXES_PRODUCT_NOTIFICATIONS_STATUS');
    }

    function install() {
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Product Notifications Module', 'MODULE_BOXES_PRODUCT_NOTIFICATIONS_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'osc_cfg_select_option(array(\'True\', \'False\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Placement', 'MODULE_BOXES_PRODUCT_NOTIFICATIONS_CONTENT_PLACEMENT', 'Right Column', 'Should the module be loaded in the left or right column?', '6', '1', 'osc_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', now())");
      osc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_PRODUCT_NOTIFICATIONS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      osc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_BOXES_PRODUCT_NOTIFICATIONS_STATUS', 'MODULE_BOXES_PRODUCT_NOTIFICATIONS_CONTENT_PLACEMENT', 'MODULE_BOXES_PRODUCT_NOTIFICATIONS_SORT_ORDER');
    }
  }
?>
