<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_PayPal {
    var $_version = '4.0';
    var $_api_version = '112';

    var $_map = array('EC' => array('code' => 'paypal_express',
                                    'minversion' => '4.0',
                                    'migrate_status' => 'MODULE_PAYMENT_PAYPAL_EXPRESS_STATUS'),
                      'DP' => array('code' => 'paypal_pro_dp',
                                    'minversion' => '4.0',
                                    'migrate_status' => 'MODULE_PAYMENT_PAYPAL_PRO_DP_STATUS'),
                      'HS' => array('code' => 'paypal_pro_hs',
                                    'minversion' => '4.0',
                                    'migrate_status' => 'MODULE_PAYMENT_PAYPAL_PRO_HS_STATUS'),
                      'PS' => array('code' => 'paypal_standard',
                                    'minversion' => '4.0',
                                    'migrate_status' => 'MODULE_PAYMENT_PAYPAL_STANDARD_STATUS'));

    function log($module, $action, $result, $request, $response, $server, $is_ipn = false) {
      global $customer_id;

      $filter = array('ACCT', 'CVV2', 'ISSUENUMBER');

      $request_string = '';

      if ( is_array($request) ) {
        foreach ( $request as $key => $value ) {
          if ( (strpos($key, '_nh-dns') !== false) || in_array($key, $filter) ) {
            $value = '**********';
          }

          $request_string .= $key . ': ' . $value . "\n";
        }
      } else {
        $request_string = $request;
      }

      $response_string = '';

      if ( is_array($response) ) {
        foreach ( $response as $key => $value ) {
          if ( (strpos($key, '_nh-dns') !== false) || in_array($key, $filter) ) {
            $value = '**********';
          }

          $response_string .= $key . ': ' . $value . "\n";
        }
      } else {
        $response_string = $response;
      }

      $data = array('customers_id' => tep_session_is_registered('customer_id') ? $customer_id : 0,
                    'module' => $module,
                    'action' => $action . (($is_ipn === true) ? ' [IPN]' : ''),
                    'result' => $result,
                    'server' => ($server == 'live') ? 1 : -1,
                    'request' => trim($request_string),
                    'response' => trim($response_string),
                    'ip_address' => sprintf('%u', ip2long(tep_get_ip_address())),
                    'date_added' => 'now()');

      tep_db_perform('oscom_app_paypal_log', $data);
    }

    function migrate() {
      $migrated = false;

      foreach ( $this->_map as $key => $value ) {
        if ( !defined('OSCOM_APP_PAYPAL_' . $key . '_STATUS') ) {
          $this->saveParameter('OSCOM_APP_PAYPAL_' . $key . '_STATUS', '');

          if ( defined($value['migrate_status']) && $this->canMigrate($key) ) {
            $class = 'OSCOM_PayPal_' . $key . '_Migrate';
            include(DIR_FS_CATALOG . 'includes/apps/paypal/modules/' . $key . '/Migrate.php');

            $migrate = new $class($this);

            if ( $migrated === false ) {
              $migrated = true;
            }
          }
        }
      }

      return $migrated;
    }

    function isInstalled($module) {
      if ( array_key_exists($module, $this->_map) ) {
        return defined('OSCOM_APP_PAYPAL_' . $module . '_STATUS') && tep_not_null(constant('OSCOM_APP_PAYPAL_' . $module . '_STATUS'));
      }

      return false;
    }

    function canMigrate($code) {
      if ( array_key_exists($code, $this->_map) && file_exists(DIR_FS_CATALOG . 'includes/modules/payment/' . $this->_map[$code]['code'] . '.php') ) {
        if ( !class_exists($this->_map[$code]['code']) ) {
          include(DIR_FS_CATALOG . 'includes/modules/payment/' . $this->_map[$code]['code'] . '.php');
        }

        $class = $this->_map[$code]['code'];
        $module = new $class();

        if ( isset($module->signature) ) {
          $sig = explode('|', $module->signature);

          if ( isset($sig[0]) && ($sig[0] == 'paypal') && isset($sig[1]) && ($sig[1] == $this->_map[$code]['code']) && isset($sig[2]) ) {
            return version_compare($sig[2], $this->_map[$code]['minversion']) >= 0;
          }
        }
      }

      return false;
    }

    function hasCredentials($module, $type = null) {
      $server = constant('OSCOM_APP_PAYPAL_' . $module . '_STATUS');

      if ( !in_array($server, array('1', '0')) ) {
        return false;
      }

      $server = ($server == '1') ? 'LIVE' : 'SANDBOX';

      if ( $type == 'email') {
        $creds = array('OSCOM_APP_PAYPAL_' . $server . '_SELLER_EMAIL');
      } elseif ( substr($type, 0, 7) == 'payflow' ) {
        $creds = array('OSCOM_APP_PAYPAL_PF_' . $server . '_' . strtoupper(substr($type, 8)));
      } else {
        $creds = array('OSCOM_APP_PAYPAL_' . $server . '_API_USERNAME',
                       'OSCOM_APP_PAYPAL_' . $server . '_API_PASSWORD',
                       'OSCOM_APP_PAYPAL_' . $server . '_API_SIGNATURE');
      }

      foreach ( $creds as $c ) {
        if ( !defined($c) || (strlen(trim(constant($c))) < 1) ) {
          return false;
        }
      }

      return true;
    }

    function getCredentials($module, $type) {
      if ( constant('OSCOM_APP_PAYPAL_' . $module . '_STATUS') == '1' ) {
        if ( $type == 'email') {
          return constant('OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL');
        } elseif ( $type == 'email_primary') {
          return constant('OSCOM_APP_PAYPAL_LIVE_SELLER_EMAIL_PRIMARY');
        } elseif ( substr($type, 0, 7) == 'payflow' ) {
          return constant('OSCOM_APP_PAYPAL_PF_LIVE_' . strtoupper(substr($type, 8)));
        } else {
          return constant('OSCOM_APP_PAYPAL_LIVE_API_' . strtoupper($type));
        }
      }

      if ( $type == 'email') {
        return constant('OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL');
      } elseif ( $type == 'email_primary') {
        return constant('OSCOM_APP_PAYPAL_SANDBOX_SELLER_EMAIL_PRIMARY');
      } elseif ( substr($type, 0, 7) == 'payflow' ) {
        return constant('OSCOM_APP_PAYPAL_PF_SANDBOX_' . strtoupper(substr($type, 8)));
      } else {
        return constant('OSCOM_APP_PAYPAL_SANDBOX_API_' . strtoupper($type));
      }
    }

    function getApiCredentials($server, $type) {
      if ( $server == 'live' ) {
        return constant('OSCOM_APP_PAYPAL_LIVE_API_' . strtoupper($type));
      }

      return constant('OSCOM_APP_PAYPAL_SANDBOX_API_' . strtoupper($type));
    }

    function getParameters($module) {
      if ( $module == 'G' ) {
        $result = array();

        if ( $dir = @dir(DIR_FS_CATALOG . 'includes/apps/paypal/cfg_params/') ) {
          $files = array();

          while ( $file = $dir->read() ) {
            if ( !is_dir(DIR_FS_CATALOG . 'includes/apps/paypal/cfg_params/' . $file) ) {
              if ( substr($file, strrpos($file, '.')) == '.php' ) {
                $files[] = $file;
              }
            }
          }

          natsort($files);

          foreach ( $files as $file ) {
            $result[] = 'OSCOM_APP_PAYPAL_' . strtoupper(substr($file, 0, strrpos($file, '.')));
          }
        }
      } else {
        $class = $this->_map[$module]['code'];

        if ( !class_exists($class) ) {
          include(DIR_FS_CATALOG . 'includes/modules/payment/' . $class . '.php');
        }

        $m = new $class();
        $result = $m->keys(true);
      }

      return $result;
    }

    function getInputParameters($module) {
      $result = array();

      if ( $module == 'G' ) {
        $cut = 'OSCOM_APP_PAYPAL_';
      } else {
        $cut = 'OSCOM_APP_PAYPAL_' . $module . '_';
      }

      $cut_length = strlen($cut);

      foreach ( $this->getParameters($module) as $key ) {
        $p = strtolower(substr($key, $cut_length));

        if ( $module == 'G' ) {
          $cfg_class = 'OSCOM_PayPal_Cfg_' . $p;

          if ( !class_exists($cfg_class) ) {
            include(DIR_FS_CATALOG . 'includes/apps/paypal/cfg_params/' . $p . '.php');
          }
        } else {
          $cfg_class = 'OSCOM_PayPal_' . $module . '_Cfg_' . $p;

          if ( !class_exists($cfg_class) ) {
            include(DIR_FS_CATALOG . 'includes/apps/paypal/modules/' . $module . '/cfg_params/' . $p . '.php');
          }
        }

        $cfg = new $cfg_class();

        if ( !defined($key) ) {
          $this->saveParameter($key, $cfg->default);
        }

        $result[] = $cfg->getSetField();
      }

      return $result;
    }

// APP calls require $server to be "live" or "sandbox"
    function getApiResult($module, $call, $extra_params = null, $server = null, $is_ipn = false) {
      if ( $module == 'APP' ) {
        $function = 'OSCOM_PayPal_Api_' . $call;

        if ( !function_exists($function) ) {
          include(DIR_FS_CATALOG . 'includes/apps/paypal/api/' . $call . '.php');
        }
      } else {
        if ( !isset($server) ) {
          $server = (constant('OSCOM_APP_PAYPAL_' . $module . '_STATUS') == '1') ? 'live' : 'sandbox';
        }

        $function = 'OSCOM_PayPal_' . $module . '_Api_' . $call;

        if ( !function_exists($function) ) {
          include(DIR_FS_CATALOG . 'includes/apps/paypal/modules/' . $module . '/api/' . $call . '.php');
        }
      }

      $result = $function($this, $server, $extra_params);

      $this->log($module, $call, ($result['success'] === true) ? 1 : -1, $result['req'], $result['res'], $server, $is_ipn);

      return $result['res'];
    }

    function makeApiCall($url, $parameters) {
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

      if ( isset($server['user']) && isset($server['pass']) ) {
        curl_setopt($curl, CURLOPT_USERPWD, $server['user'] . ':' . $server['pass']);
      }

      if ( OSCOM_APP_PAYPAL_VERIFY_SSL == '1' ) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        if ( (substr($server['host'], -10) == 'paypal.com') && file_exists(DIR_FS_CATALOG . 'ext/modules/payment/paypal/paypal.com.crt') ) {
          curl_setopt($curl, CURLOPT_CAINFO, DIR_FS_CATALOG . 'ext/modules/payment/paypal/paypal.com.crt');
        } elseif ( file_exists(DIR_FS_CATALOG . 'includes/cacert.pem') ) {
          curl_setopt($curl, CURLOPT_CAINFO, DIR_FS_CATALOG . 'includes/cacert.pem');
        }
      } else {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      }

      if ( tep_not_null(OSCOM_APP_PAYPAL_PROXY) ) {
        curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($curl, CURLOPT_PROXY, OSCOM_APP_PAYPAL_PROXY);
      }

      $result = curl_exec($curl);

      curl_close($curl);

      return $result;
    }

    function drawButton($title = null, $link = null, $type = null, $params = null, $force_css = false) {
      $colours = array('success' => '#1cb841',
                       'error' => '#ca3c3c',
                       'warning' => '#ebaa16',
                       'info' => '#42B8DD',
                       'primary' => '#0078E7');

      if ( !isset($type) || !in_array($type, array_keys($colours)) ) {
        $type = 'info';
      }

      $css = 'font-size:14px;color:#fff;padding:8px 16px;border:0;border-radius:4px;text-shadow:0 1px 1px rgba(0, 0, 0, 0.2);text-decoration:none;display:inline-block;cursor:pointer;white-space:nowrap;vertical-align:baseline;text-align:center;background-color:' . $colours[$type] . ';';

      $button = '';

      if ( isset($link) ) {
        $button .= '<a href="' . $link . '" class="pp-button';

        if ( isset($type) ) {
          $button .= ' pp-button-' . $type;
        }

        $button .= '"';

        if ( isset($params) ) {
          $button .= ' ' . $params;
        }

        if ( $force_css == true ) {
          $button .= ' style="' . $css . '"';
        }

        $button .= '>' . $title . '</a>';
      } else {
        $button .= '<button type="submit" class="pp-button';

        if ( isset($type) ) {
          $button .= ' pp-button-' . $type;
        }

        $button .= '"';

        if ( isset($params) ) {
          $button .= ' ' . $params;
        }

        if ( $force_css == true ) {
          $button .= ' style="' . $css . '"';
        }

        $button .= '>' . $title . '</button>';
      }

      return $button;
    }

    function createRandomValue($length, $type = 'mixed') {
      if ( ($type != 'mixed') && ($type != 'chars') && ($type != 'digits')) $type = 'mixed';

      $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $digits = '0123456789';

      $base = '';

      if ( ($type == 'mixed') || ($type == 'chars') ) {
        $base .= $chars;
      }

      if ( ($type == 'mixed') || ($type == 'digits') ) {
        $base .= $digits;
      }

      $value = '';

      if ( !class_exists('PasswordHash') && file_exists(DIR_FS_CATALOG . 'includes/classes/passwordhash.php') ) {
        include(DIR_FS_CATALOG . 'includes/classes/passwordhash.php');

        $hasher = new PasswordHash(10, true);

        do {
          $random = base64_encode($hasher->get_random_bytes($length));

          for ($i = 0, $n = strlen($random); $i < $n; $i++) {
            $char = substr($random, $i, 1);

            if ( strpos($base, $char) !== false ) {
              $value .= $char;
            }
          }
        } while ( strlen($value) < $length );

        if ( strlen($value) > $length ) {
          $value = substr($value, 0, $length);
        }

        return $value;
      }

// fallback for v2.3.1
      while ( strlen($value) < $length ) {
        if ($type == 'digits') {
          $char = tep_rand(0,9);
        } else {
          $char = chr(tep_rand(0,255));
        }

        if ( $type == 'mixed' ) {
          if (preg_match('/^[a-z0-9]$/i', $char)) $value .= $char;
        } elseif ($type == 'chars') {
          if (preg_match('/^[a-z]$/i', $char)) $value .= $char;
        } elseif ($type == 'digits') {
          if (preg_match('/^[0-9]$/i', $char)) $value .= $char;
        }
      }

      return $value;
    }

    function saveParameter($key, $value, $title = null, $description = null) {
      if ( !defined($key) ) {
        if ( !isset($title) ) {
          $title = 'PayPal App Parameter';
        }

        if ( !isset($description) ) {
          $description = 'A parameter for the PayPal Application.';
        }

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_db_input($title) . "', '" . tep_db_input($key) . "', '" . tep_db_input($value) . "', '" . tep_db_input($description) . "', '6', '0', now())");

        define($key, $value);
      } else {
        tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($value) . "' where configuration_key = '" . tep_db_input($key) . "'");
      }
    }

    function deleteParameter($key) {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = '" . tep_db_input($key) . "'");
    }

    function formatCurrencyRaw($total, $currency_code = null, $currency_value = null) {
      global $currencies, $currency;

      if ( !isset($currency_code) ) {
        $currency_code = tep_session_is_registered('currency') ? $currency : DEFAULT_CURRENCY;
      }

      if ( !isset($currency_value) || !is_numeric($currency_value) ) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(tep_round($total * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }

    function getVersion() {
      return $this->_version;
    }

    function getApiVersion() {
      return $this->_api_version;
    }

    function hasAlert() {
      return tep_session_is_registered('OSCOM_PayPal_Alerts');
    }

    function addAlert($message, $type) {
      global $OSCOM_PayPal_Alerts;

      if ( in_array($type, array('error', 'warning', 'success')) ) {
        if ( !tep_session_is_registered('OSCOM_PayPal_Alerts') ) {
          $OSCOM_PayPal_Alerts = array();
          tep_session_register('OSCOM_PayPal_Alerts');
        }

        $OSCOM_PayPal_Alerts[$type][] = $message;
      }
    }

    function getAlerts() {
      global $OSCOM_PayPal_Alerts;

      $output = '';

      if ( tep_session_is_registered('OSCOM_PayPal_Alerts') && !empty($OSCOM_PayPal_Alerts) ) {
        $result = array();

        foreach ( $OSCOM_PayPal_Alerts as $type => $messages ) {
          if ( in_array($type, array('error', 'warning', 'success')) ) {
            $m = '<ul class="pp-alerts-' . $type . '">';

            foreach ( $messages as $message ) {
              $m .= '<li>' . tep_output_string_protected($message) . '</li>';
            }

            $m .= '</ul>';

            $result[] = $m;
          }
        }

        if ( !empty($result) ) {
          $output .= '<div class="pp-alerts">' . implode("\n", $result) . '</div>';
        }
      }

      tep_session_unregister('OSCOM_PayPal_Alerts');

      return $output;
    }
  }
?>
