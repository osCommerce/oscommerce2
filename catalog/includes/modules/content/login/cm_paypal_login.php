<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class cm_paypal_login {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_paypal_login() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_PAYPAL_LOGIN_TITLE;
      $this->description = MODULE_CONTENT_PAYPAL_LOGIN_DESCRIPTION;

      if ( defined('MODULE_CONTENT_PAYPAL_LOGIN_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_PAYPAL_LOGIN_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_PAYPAL_LOGIN_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate;

      ob_start();
      include(DIR_WS_MODULES . 'content/' . $this->group . '/templates/paypal_login.php');
      $template = ob_get_clean();

      $oscTemplate->addContent($template, $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_PAYPAL_LOGIN_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable PayPal Login Module', 'MODULE_CONTENT_PAYPAL_LOGIN_STATUS', 'True', 'Do you want to enable the PayPal Login module?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Client ID', 'MODULE_CONTENT_PAYPAL_LOGIN_CLIENT_ID', '', 'Your PayPal Application Client ID.', '6', '1', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Secret', 'MODULE_CONTENT_PAYPAL_LOGIN_SECRET', '', 'Your PayPal Application Secret.', '6', '1', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Server Type', 'MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE', 'Sandbox', 'Which server should be used? Live for production or Sandbox for testing.', '6', '1', 'tep_cfg_select_option(array(\'Live\', \'Sandbox\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Verify SSL Certificate', 'MODULE_CONTENT_PAYPAL_LOGIN_VERIFY_SSL', 'True', 'Verify gateway server SSL certificate on connection?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Proxy Server', 'MODULE_CONTENT_PAYPAL_LOGIN_PROXY', '', 'Send API requests through this proxy server. (host:port, eg: 123.45.67.89:8080 or proxy.example.com:8080)', '6', '1', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Width', 'MODULE_CONTENT_PAYPAL_LOGIN_CONTENT_WIDTH', 'Full', 'Should the content be shown in a full or half width container?', '6', '1', 'tep_cfg_select_option(array(\'Full\', \'Half\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_PAYPAL_LOGIN_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CONTENT_PAYPAL_LOGIN_STATUS', 'MODULE_CONTENT_PAYPAL_LOGIN_CLIENT_ID', 'MODULE_CONTENT_PAYPAL_LOGIN_SECRET', 'MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE', 'MODULE_CONTENT_PAYPAL_LOGIN_VERIFY_SSL', 'MODULE_CONTENT_PAYPAL_LOGIN_PROXY', 'MODULE_CONTENT_PAYPAL_LOGIN_CONTENT_WIDTH', 'MODULE_CONTENT_PAYPAL_LOGIN_SORT_ORDER');
    }

    function sendRequest($url, $parameters = null) {
      $server = parse_url($url);

      if ( !isset($server['port']) ) {
        $server['port'] = ($server['scheme'] == 'https') ? 443 : 80;
      }

      if ( !isset($server['path']) ) {
        $server['path'] = '/';
      }

      $curl = curl_init($server['scheme'] . '://' . $server['host'] . $server['path'] . (isset($server['query']) ? '?' . $server['query'] : ''));
      curl_setopt($curl, CURLOPT_PORT, $server['port']);
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
      curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
      curl_setopt($curl, CURLOPT_ENCODING, '');

      if ( MODULE_CONTENT_PAYPAL_LOGIN_VERIFY_SSL == 'True' ) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        if ( file_exists(DIR_FS_CATALOG . 'ext/modules/payment/paypal/paypal.com.crt') ) {
          curl_setopt($curl, CURLOPT_CAINFO, DIR_FS_CATALOG . 'ext/modules/payment/paypal/paypal.com.crt');
        } elseif ( file_exists(DIR_FS_CATALOG . 'includes/cacert.pem') ) {
          curl_setopt($curl, CURLOPT_CAINFO, DIR_FS_CATALOG . 'includes/cacert.pem');
        }
      } else {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      }

      if ( tep_not_null(MODULE_CONTENT_PAYPAL_LOGIN_PROXY) ) {
        curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($curl, CURLOPT_PROXY, MODULE_CONTENT_PAYPAL_LOGIN_PROXY);
      }

      $result = curl_exec($curl);

      curl_close($curl);

      return $result;
    }

    function getToken($params) {
      if ( MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE == 'Live' ) {
        $api_server = 'api.paypal.com';
      } else {
        $api_server = 'api.sandbox.paypal.com';
      }

      $parameters = array('client_id' => MODULE_CONTENT_PAYPAL_LOGIN_CLIENT_ID,
                          'client_secret' => MODULE_CONTENT_PAYPAL_LOGIN_SECRET,
                          'grant_type' => 'authorization_code',
                          'code' => $params['code']);

      $post_string = '';

      foreach ($parameters as $key => $value) {
        $post_string .= $key . '=' . urlencode(utf8_encode(trim($value))) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      $result = $this->sendRequest('https://' . $api_server . '/v1/identity/openidconnect/tokenservice', $post_string);

      $result_array = json_decode($result, true);

      return $result_array;
    }

    function getRefreshToken($params) {
      if ( MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE == 'Live' ) {
        $api_server = 'api.paypal.com';
      } else {
        $api_server = 'api.sandbox.paypal.com';
      }

      $parameters = array('client_id' => MODULE_CONTENT_PAYPAL_LOGIN_CLIENT_ID,
                          'client_secret' => MODULE_CONTENT_PAYPAL_LOGIN_SECRET,
                          'grant_type' => 'refresh_token',
                          'refresh_token' => $params['refresh_token']);

      $post_string = '';

      foreach ($parameters as $key => $value) {
        $post_string .= $key . '=' . urlencode(utf8_encode(trim($value))) . '&';
      }

      $post_string = substr($post_string, 0, -1);

      $result = $this->sendRequest('https://' . $api_server . '/v1/identity/openidconnect/tokenservice', $post_string);

      $result_array = json_decode($result, true);

      return $result_array;
    }

    function getUserInfo($params) {
      if ( MODULE_CONTENT_PAYPAL_LOGIN_SERVER_TYPE == 'Live' ) {
        $api_server = 'api.paypal.com';
      } else {
        $api_server = 'api.sandbox.paypal.com';
      }

      $result = $this->sendRequest('https://' . $api_server . '/v1/identity/openidconnect/userinfo/?schema=openid&access_token=' . $params['access_token']);

      $result_array = json_decode($result, true);

      return $result_array;
    }
  }
?>
