<?php
/*
  $Id$ cs_downloads
  2013 G.L. Walker - http://wsfive.com
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2010 osCommerce
  Released under the GNU General Public License
*/

  class cm_cs_downloads {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_cs_downloads() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CHECKOUT_SUCCESS_DOWNLOADS_TITLE;
      $this->description = MODULE_CHECKOUT_SUCCESS_DOWNLOADS_DESCRIPTION;

	  if (defined('MODULE_CHECKOUT_SUCCESS_DOWNLOADS_STATUS')) {
        $this->sort_order = MODULE_CHECKOUT_SUCCESS_DOWNLOADS_SORT_ORDER;
        $this->enabled = (MODULE_CHECKOUT_SUCCESS_DOWNLOADS_STATUS == 'True');
      }
	}
	 
    function execute() {
      global $oscTemplate, $languages_id, $_GET, $customer_id;
	  
	  
  		if (tep_session_is_registered('customer_id')) {	
  
  	     $order_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where customers_id = '" . (int)$customer_id . "' order by date_purchased desc limit 1");

          if (tep_db_num_rows($order_query) == 1) {
            $order = tep_db_fetch_array($order_query); 
		  }
	  // Now get all downloadable products in that order
      $downloads_query = tep_db_query("select date_format(o.date_purchased, '%Y-%m-%d') as date_purchased_day, opd.download_maxdays, op.products_name, opd.orders_products_download_id, opd.orders_products_filename, opd.download_count, opd.download_maxdays from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd, " . TABLE_ORDERS_STATUS . " os where o.customers_id = '" . (int)$customer_id . "' and o.orders_id = '" . (int)$order['orders_id'] . "' and o.orders_id = op.orders_id and op.orders_products_id = opd.orders_products_id and opd.orders_products_filename != '' and o.orders_status = os.orders_status_id and os.downloads_flag = '1' and os.language_id = '" . (int)$languages_id . "'");
	 
	  if (tep_db_num_rows($downloads_query) > 0) {
	  $cs_data = '' . PHP_EOL;
	  $cs_data .= '<div id="cs-prod-downloads">' . PHP_EOL;
	  $cs_data .= '  <h2>' . HEADING_DOWNLOAD . '</h2>' . PHP_EOL;
	  $cs_data .= '  <table border="0" width="100%" cellspacing="1" cellpadding="2">' . PHP_EOL;
	  
	  while ($downloads = tep_db_fetch_array($downloads_query)) {
// MySQL 3.22 does not have INTERVAL
        list($dt_year, $dt_month, $dt_day) = explode('-', $downloads['date_purchased_day']);
        $download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + $downloads['download_maxdays'], $dt_year);
        $download_expiry = date('Y-m-d H:i:s', $download_timestamp);
		
		// The link will appear only if:
        // - Download remaining count is > 0, AND
        // - The file is present in the DOWNLOAD directory, AND EITHER
        // - No expiry date is enforced (maxdays == 0), OR
        // - The expiry date is not reached
        if ( ($downloads['download_count'] > 0) && (file_exists(DIR_FS_DOWNLOAD . $downloads['orders_products_filename'])) && ( ($downloads['download_maxdays'] == 0) || ($download_timestamp > time())) ) {
			
          $cs_data .= '        <td><a href="' . tep_href_link(FILENAME_DOWNLOAD, 'order=' . $last_order . '&id=' . $downloads['orders_products_download_id']) . '">' . $downloads['products_name'] . '</a></td>' . PHP_EOL;
        } else {
          $cs_data .= '        <td>' . $downloads['products_name'] . '</td>' . PHP_EOL;
        }

        $cs_data .= '        <td>' . TABLE_HEADING_DOWNLOAD_DATE . tep_date_long($download_expiry) . '</td>' . "\n" .
             '        <td align="right">' . $downloads['download_count'] . TABLE_HEADING_DOWNLOAD_COUNT . '</td>' . "\n" .
             '      </tr>' . PHP_EOL;
      }
      
	  $cs_data .= '  </table>' . PHP_EOL;
	  $cs_data .= '</div>' . PHP_EOL;
		
      $oscTemplate->addContent($cs_data, $this->group);
    }
	
  }
	 
  }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CHECKOUT_SUCCESS_DOWNLOADS_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Product Download Module', 'MODULE_CHECKOUT_SUCCESS_DOWNLOADS_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CHECKOUT_SUCCESS_DOWNLOADS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '2', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CHECKOUT_SUCCESS_DOWNLOADS_STATUS', 'MODULE_CHECKOUT_SUCCESS_DOWNLOADS_SORT_ORDER');
    }
  }