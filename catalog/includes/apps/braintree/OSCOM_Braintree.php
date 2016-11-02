<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2016 osCommerce

  Released under the GNU General Public License
*/

  class OSCOM_Braintree {
    var $_code = 'braintree';
    var $_title = 'Braintree App';
    var $_version;
    var $_api_version;
    var $_identifier = 'osCommerce_BTapp_v1';
    var $_definitions = array();

    function OSCOM_Braintree() {
      if ( !class_exists('Braintree', false) ) {
        include(DIR_FS_CATALOG . 'includes/apps/braintree/lib/Braintree.php');
      }

      $this->_api_version = Braintree_Version::get();

      $this->installCheck();
    }

    function installCheck() {
      if (!defined('OSCOM_APP_PAYPAL_BRAINTREE_CC_SORT_ORDER')) {
        $installed = explode(';', MODULE_PAYMENT_INSTALLED);
        $installed_pos = array_search('braintree_cc.php', $installed);

        if ( $installed_pos === false ) {
          $installed[] = 'braintree_cc.php';

          $this->saveParameter('MODULE_PAYMENT_INSTALLED', implode(';', $installed));
        }

        $this->saveParameter('OSCOM_APP_PAYPAL_BRAINTREE_CC_SORT_ORDER', '0', 'Sort Order', 'Sort order of display. Lowest is displayed first.');

        $check_query = tep_db_query('show tables like "customers_braintree_tokens"');
        if (!tep_db_num_rows($check_query)) {
          $sql = <<<EOD
CREATE TABLE customers_braintree_tokens (
  id int NOT NULL auto_increment,
  customers_id int NOT NULL,
  braintree_token varchar(255) NOT NULL,
  card_type varchar(32) NOT NULL,
  number_filtered varchar(20) NOT NULL,
  expiry_date char(6) NOT NULL,
  date_added datetime NOT NULL,
  PRIMARY KEY (id),
  KEY idx_cbraintreet_customers_id (customers_id),
  KEY idx_cbraintreet_token (braintree_token)
) CHARACTER SET utf8 COLLATE utf8_unicode_ci;
EOD;

          tep_db_query($sql);
        }
      }

      if ( !defined('OSCOM_APP_PAYPAL_BRAINTREE_TRANSACTIONS_ORDER_STATUS_ID') ) {
        $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Braintree [Transactions]' limit 1");

        if (tep_db_num_rows($check_query) < 1) {
          $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
          $status = tep_db_fetch_array($status_query);

          $status_id = $status['status_id']+1;

          $languages = tep_get_languages();

          foreach ($languages as $lang) {
            tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $lang['id'] . "', 'Braintree [Transactions]')");
          }

          $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
          if (tep_db_num_rows($flags_query) == 1) {
            tep_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 0 and downloads_flag = 0 where orders_status_id = '" . $status_id . "'");
          }
        } else {
          $check = tep_db_fetch_array($check_query);

          $status_id = $check['orders_status_id'];
        }

        $this->saveParameter('OSCOM_APP_PAYPAL_BRAINTREE_TRANSACTIONS_ORDER_STATUS_ID', $status_id);
      }

      if (!defined('MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_SORT_ORDER')) {
        $cm = explode(';', MODULE_CONTENT_INSTALLED);
        $pos = array_search('account/cm_account_braintree_cards', $cm);

        if ($pos === false) {
          $cm[] = 'account/cm_account_braintree_cards';

          $this->saveParameter('MODULE_CONTENT_INSTALLED', implode(';', $cm));
        }

        $this->saveParameter('MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_SORT_ORDER', '0', 'Sort Order', 'Sort order of display. Lowest is displayed first.');
      }
    }

    function migrate() {
      $migrated = false;

      foreach ( $this->getModules() as $module ) {
        if ( !defined('OSCOM_APP_PAYPAL_BRAINTREE_' . $module . '_STATUS') ) {
          $this->saveParameter('OSCOM_APP_PAYPAL_BRAINTREE_' . $module . '_STATUS', '');

          $class = 'OSCOM_Braintree_' . $module;

          if ( !class_exists($class) ) {
            $this->loadLanguageFile('modules/' . $module . '/' . $module . '.php');

            include(DIR_FS_CATALOG . 'includes/apps/braintree/modules/' . $module . '/' . $module . '.php');
          }

          $m = new $class();

          if ( method_exists($m, 'canMigrate') && $m->canMigrate() ) {
            $m->migrate($this);

            if ( $migrated === false ) {
              $migrated = true;
            }
          }
        }
      }

      $this->deleteParameter('MODULE_CONTENT_ACCOUNT_BRAINTREE_CARDS_STATUS');

      return $migrated;
    }

    function getModules() {
      static $result;

      if ( !isset($result) ) {
        $result = array();

        if ( $dir = @dir(DIR_FS_CATALOG . 'includes/apps/braintree/modules/') ) {
          while ( $file = $dir->read() ) {
            if ( !in_array($file, array('.', '..')) && is_dir(DIR_FS_CATALOG . 'includes/apps/braintree/modules/' . $file) && file_exists(DIR_FS_CATALOG . 'includes/apps/braintree/modules/' . $file . '/' . $file . '.php') ) {
              $sort_order = $this->getModuleInfo($file, 'sort_order');

              if ( is_numeric($sort_order) ) {
                $counter = (int)$sort_order;
              } else {
                $counter = count($result);
              }

              while ( true ) {
                if ( isset($result[$counter]) ) {
                  $counter++;

                  continue;
                }

                $result[$counter] = $file;

                break;
              }
            }
          }

          ksort($result, SORT_NUMERIC);
        }
      }

      return $result;
    }

    function isInstalled($module) {
      if ( file_exists(DIR_FS_CATALOG . 'includes/apps/braintree/modules/' . basename($module) . '/' . basename($module) . '.php') ) {
        return defined('OSCOM_APP_PAYPAL_BRAINTREE_' . basename($module) . '_STATUS') && tep_not_null(constant('OSCOM_APP_PAYPAL_BRAINTREE_' . basename($module) . '_STATUS'));
      }

      return false;
    }

    function getModuleInfo($module, $info) {
      $class = 'OSCOM_Braintree_' . $module;

      if ( !class_exists($class) ) {
        $this->loadLanguageFile('modules/' . $module . '/' . $module . '.php');

        include(DIR_FS_CATALOG . 'includes/apps/braintree/modules/' . $module . '/' . $module . '.php');
      }

      $m = new $class();

      return $m->{'_' . $info};
    }

    function hasApiCredentials($server) {
      $server = ($server == 'live') ? '' : 'SANDBOX_';

      $creds = array('OSCOM_APP_PAYPAL_BRAINTREE_' . $server . 'MERCHANT_ID',
                     'OSCOM_APP_PAYPAL_BRAINTREE_' . $server . 'PUBLIC_KEY',
                     'OSCOM_APP_PAYPAL_BRAINTREE_' . $server . 'PRIVATE_KEY');

      foreach ( $creds as $c ) {
        if ( !defined($c) || (strlen(trim(constant($c))) < 1) ) {
          return false;
        }
      }

      return true;
    }

    function getApiCredentials($server, $type) {
      if ( ($server == 'live') && defined('OSCOM_APP_PAYPAL_BRAINTREE_LIVE_API_' . strtoupper($type)) ) {
        return constant('OSCOM_APP_PAYPAL_BRAINTREE_LIVE_API_' . strtoupper($type));
      } elseif ( defined('OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_API_' . strtoupper($type)) ) {
        return constant('OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_API_' . strtoupper($type));
      }
    }

    function getParameters($module) {
      $result = array();

      if ( $module == 'G' ) {
        if ( $dir = @dir(DIR_FS_CATALOG . 'includes/apps/braintree/cfg_params/') ) {
          while ( $file = $dir->read() ) {
            if ( !is_dir(DIR_FS_CATALOG . 'includes/apps/braintree/cfg_params/' . $file) && (substr($file, strrpos($file, '.')) == '.php') ) {
              $result[] = 'OSCOM_APP_PAYPAL_BRAINTREE_' . strtoupper(substr($file, 0, strrpos($file, '.')));
            }
          }
        }
      } else {
        if ( $dir = @dir(DIR_FS_CATALOG . 'includes/apps/braintree/modules/' . $module . '/cfg_params/') ) {
          while ( $file = $dir->read() ) {
            if ( !is_dir(DIR_FS_CATALOG . 'includes/apps/braintree/modules/' . $module . '/cfg_params/' . $file) && (substr($file, strrpos($file, '.')) == '.php') ) {
              $result[] = 'OSCOM_APP_PAYPAL_BRAINTREE_' . $module . '_' . strtoupper(substr($file, 0, strrpos($file, '.')));
            }
          }
        }
      }

      return $result;
    }

    function getInputParameters($module) {
      $result = array();

      if ( $module == 'G' ) {
        $cut = 'OSCOM_APP_PAYPAL_BRAINTREE_';
      } else {
        $cut = 'OSCOM_APP_PAYPAL_BRAINTREE_' . $module . '_';
      }

      $cut_length = strlen($cut);

      foreach ( $this->getParameters($module) as $key ) {
        $p = strtolower(substr($key, $cut_length));

        if ( $module == 'G' ) {
          $cfg_class = 'OSCOM_Braintree_Cfg_' . $p;

          if ( !class_exists($cfg_class) ) {
            $this->loadLanguageFile('cfg_params/' . $p . '.php');

            include(DIR_FS_CATALOG . 'includes/apps/braintree/cfg_params/' . $p . '.php');
          }
        } else {
          $cfg_class = 'OSCOM_Braintree_' . $module . '_Cfg_' . $p;

          if ( !class_exists($cfg_class) ) {
            $this->loadLanguageFile('modules/' . $module . '/cfg_params/' . $p . '.php');

            include(DIR_FS_CATALOG . 'includes/apps/braintree/modules/' . $module . '/cfg_params/' . $p . '.php');
          }
        }

        $cfg = new $cfg_class();

        if ( !defined($key) ) {
          $this->saveParameter($key, $cfg->default, isset($cfg->title) ? $cfg->title : null, isset($cfg->description) ? $cfg->description : null, isset($cfg->set_func) ? $cfg->set_func : null);
        }

        if ( !isset($cfg->app_configured) || ($cfg->app_configured !== false) ) {
          if ( isset($cfg->sort_order) && is_numeric($cfg->sort_order) ) {
            $counter = (int)$cfg->sort_order;
          } else {
            $counter = count($result);
          }

          while ( true ) {
            if ( isset($result[$counter]) ) {
              $counter++;

              continue;
            }

            $set_field = $cfg->getSetField();

            if ( !empty($set_field) ) {
              $result[$counter] = $set_field;
            }

            break;
          }
        }
      }

      ksort($result, SORT_NUMERIC);

      return $result;
    }

    function makeApiCall($url, $parameters = null, $headers = null) {
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
      curl_setopt($curl, CURLOPT_ENCODING, ''); // disable gzip

      if ( isset($parameters) ) {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
      }

      if ( isset($headers) && is_array($headers) && !empty($headers) ) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
      }

      if ( isset($server['user']) && isset($server['pass']) ) {
        curl_setopt($curl, CURLOPT_USERPWD, $server['user'] . ':' . $server['pass']);
      }

      if ( defined('OSCOM_APP_PAYPAL_BRAINTREE_VERIFY_SSL') && (OSCOM_APP_PAYPAL_BRAINTREE_VERIFY_SSL == '1') ) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

        if ( file_exists(DIR_FS_CATALOG . 'includes/cacert.pem') ) {
          curl_setopt($curl, CURLOPT_CAINFO, DIR_FS_CATALOG . 'includes/cacert.pem');
        }
      } else {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      }

      if ( defined('OSCOM_APP_PAYPAL_BRAINTREE_PROXY') && tep_not_null(OSCOM_APP_PAYPAL_BRAINTREE_PROXY) ) {
        curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true);
        curl_setopt($curl, CURLOPT_PROXY, OSCOM_APP_PAYPAL_BRAINTREE_PROXY);
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
        $button .= '<a href="' . $link . '" class="bt-button';

        if ( isset($type) ) {
          $button .= ' bt-button-' . $type;
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
        $button .= '<button type="submit" class="bt-button';

        if ( isset($type) ) {
          $button .= ' bt-button-' . $type;
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

    function saveParameter($key, $value, $title = null, $description = null, $set_func = null) {
      if ( !defined($key) ) {
        if ( !isset($title) ) {
          $title = 'Braintree App Parameter';
        }

        if ( !isset($description) ) {
          $description = 'A parameter for the Braintree Application.';
        }

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('" . tep_db_input($title) . "', '" . tep_db_input($key) . "', '" . tep_db_input($value) . "', '" . tep_db_input($description) . "', '6', '0', now())");

        if ( isset($set_func) ) {
          tep_db_query("update " . TABLE_CONFIGURATION . " set set_function = '" . tep_db_input($set_func) . "' where configuration_key = '" . tep_db_input($key) . "'");
        }

        define($key, $value);
      } else {
        tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($value) . "' where configuration_key = '" . tep_db_input($key) . "'");
      }
    }

    function deleteParameter($key) {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = '" . tep_db_input($key) . "'");
    }

    function setupCredentials($server = null) {
      $status = ((isset($server) && ($server === 'live')) || (!isset($server) && (OSCOM_APP_PAYPAL_BRAINTREE_CC_STATUS === '1'))) ? '1' : '0';

      Braintree_Configuration::environment($status === '1' ? 'production' : 'sandbox');
      Braintree_Configuration::merchantId($status === '1' ? OSCOM_APP_PAYPAL_BRAINTREE_MERCHANT_ID : OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_MERCHANT_ID);
      Braintree_Configuration::publicKey($status === '1' ? OSCOM_APP_PAYPAL_BRAINTREE_PUBLIC_KEY : OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_PUBLIC_KEY);
      Braintree_Configuration::privateKey($status === '1' ? OSCOM_APP_PAYPAL_BRAINTREE_PRIVATE_KEY : OSCOM_APP_PAYPAL_BRAINTREE_SANDBOX_PRIVATE_KEY);

      if (defined('OSCOM_APP_PAYPAL_BRAINTREE_PROXY') && tep_not_null(OSCOM_APP_PAYPAL_BRAINTREE_PROXY)) {
        $url = parse_url(OSCOM_APP_PAYPAL_BRAINTREE_PROXY);

        Braintree_Configuration::proxyHost($url['host']);

        if (isset($url['port'])) {
          Braintree_Configuration::proxyPort($url['port']);
        }
      }
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

    function getCode() {
      return $this->_code;
    }

    function getTitle() {
      return $this->_title;
    }

    function getVersion() {
      if ( !isset($this->_version) ) {
        $version = trim(file_get_contents(DIR_FS_CATALOG . 'includes/apps/braintree/version.txt'));

        if ( is_numeric($version) ) {
          $this->_version = $version;
        } else {
          trigger_error('OSCOM APP [BRAINTREE]: Could not read App version number.');
        }
      }

      return $this->_version;
    }

    function getApiVersion() {
      return $this->_api_version;
    }

    function getIdentifier() {
      return $this->_identifier;
    }

    function hasAlert() {
      return tep_session_is_registered('OSCOM_Braintree_Alerts');
    }

    function addAlert($message, $type) {
      global $OSCOM_Braintree_Alerts;

      if ( in_array($type, array('error', 'warning', 'success')) ) {
        if ( !tep_session_is_registered('OSCOM_Braintree_Alerts') ) {
          $OSCOM_Braintree_Alerts = array();
          tep_session_register('OSCOM_Braintree_Alerts');
        }

        $OSCOM_Braintree_Alerts[$type][] = $message;
      }
    }

    function getAlerts() {
      global $OSCOM_Braintree_Alerts;

      $output = '';

      if ( tep_session_is_registered('OSCOM_Braintree_Alerts') && !empty($OSCOM_Braintree_Alerts) ) {
        $result = array();

        foreach ( $OSCOM_Braintree_Alerts as $type => $messages ) {
          if ( in_array($type, array('error', 'warning', 'success')) ) {
            $m = '<ul class="bt-alerts-' . $type . '">';

            foreach ( $messages as $message ) {
              $m .= '<li>' . tep_output_string_protected($message) . '</li>';
            }

            $m .= '</ul>';

            $result[] = $m;
          }
        }

        if ( !empty($result) ) {
          $output .= '<div class="bt-alerts">' . implode("\n", $result) . '</div>';
        }
      }

      tep_session_unregister('OSCOM_Braintree_Alerts');

      return $output;
    }

    function install($module) {
      $cut_length = strlen('OSCOM_APP_PAYPAL_BRAINTREE_' . $module . '_');

      foreach ( $this->getParameters($module) as $key ) {
        $p = strtolower(substr($key, $cut_length));

        $cfg_class = 'OSCOM_Braintree_' . $module . '_Cfg_' . $p;

        if ( !class_exists($cfg_class) ) {
          $this->loadLanguageFile('modules/' . $module . '/cfg_params/' . $p . '.php');

          include(DIR_FS_CATALOG . 'includes/apps/braintree/modules/' . $module . '/cfg_params/' . $p . '.php');
        }

        $cfg = new $cfg_class();

        $this->saveParameter($key, $cfg->default, isset($cfg->title) ? $cfg->title : null, isset($cfg->description) ? $cfg->description : null, isset($cfg->set_func) ? $cfg->set_func : null);
      }

      $m_class = 'OSCOM_Braintree_' . $module;

      if ( !class_exists($m_class) ) {
        $this->loadLanguageFile('modules/' . $module . '/' . $module . '.php');

        include(DIR_FS_CATALOG . 'includes/apps/braintree/modules/' . $module . '/' . $module . '.php');
      }

      $m = new $m_class();

      if ( method_exists($m, 'install') ) {
        $m->install($this);
      }
    }

    function uninstall($module) {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key like 'OSCOM_APP_PAYPAL_BRAINTREE_" . tep_db_input($module) . "_%'");

      $m_class = 'OSCOM_Braintree_' . $module;

      if ( !class_exists($m_class) ) {
        $this->loadLanguageFile('modules/' . $module . '/' . $module . '.php');

        include(DIR_FS_CATALOG . 'includes/apps/braintree/modules/' . $module . '/' . $module . '.php');
      }

      $m = new $m_class();

      if ( method_exists($m, 'uninstall') ) {
        $m->uninstall($this);
      }
    }

    function logUpdate($message, $version) {
      if ( is_writable(DIR_FS_CATALOG . 'includes/apps/braintree/work') ) {
        file_put_contents(DIR_FS_CATALOG . 'includes/apps/braintree/work/update_log-' . $version . '.php', '[' . date('d-M-Y H:i:s') . '] ' . $message . "\n", FILE_APPEND);
      }
    }

    public function loadLanguageFile($filename, $lang = null) {
      global $language;

      $lang = isset($lang) ? basename($lang) : basename($language);

      if ( $lang != 'english' ) {
        $this->loadLanguageFile($filename, 'english');
      }

      $pathname = DIR_FS_CATALOG . 'includes/apps/braintree/languages/' . $lang . '/' . $filename;

      if ( file_exists($pathname) ) {
        $contents = file($pathname);

        $ini_array = array();

        foreach ( $contents as $line ) {
          $line = trim($line);

          if ( !empty($line) && (substr($line, 0, 1) != '#') ) {
            $delimiter = strpos($line, '=');

            if ( ($delimiter !== false) && (preg_match('/^[A-Za-z0-9_-]/', substr($line, 0, $delimiter)) === 1) && (substr_count(substr($line, 0, $delimiter), ' ') == 1) ) {
              $key = trim(substr($line, 0, $delimiter));
              $value = trim(substr($line, $delimiter + 1));

              $ini_array[$key] = $value;
            } elseif ( isset($key) ) {
              $ini_array[$key] .= "\n" . $line;
            }
          }
        }

        unset($contents);

        $this->_definitions = array_merge($this->_definitions, $ini_array);

        unset($ini_array);
      }
    }

    function getDef($key, $values = null) {
      $def = isset($this->_definitions[$key]) ? $this->_definitions[$key] : $key;

      if ( is_array($values) ) {
        $keys = array_keys($values);

        foreach ( $keys as &$k ) {
          $k = ':' . $k;
        }

        $def = str_replace($keys, array_values($values), $def);
      }

      return $def;
    }

    function getDirectoryContents($base, &$result = array()) {
      foreach ( scandir($base) as $file ) {
        if ( ($file == '.') || ($file == '..') ) {
          continue;
        }

        $pathname = $base . '/' . $file;

        if ( is_dir($pathname) ) {
          $this->getDirectoryContents($pathname, $result);
        } else {
          $result[] = str_replace('\\', '/', $pathname); // Unix style directory separator "/"
        }
      }

      return $result;
    }

    function isWritable($location) {
      if ( !file_exists($location) ) {
        while ( true ) {
          $location = dirname($location);

          if ( file_exists($location) ) {
            break;
          }
        }
      }

      return is_writable($location);
    }

    function rmdir($dir) {
      foreach ( scandir($dir) as $file ) {
        if ( !in_array($file, array('.', '..')) ) {
          if ( is_dir($dir . '/' . $file) ) {
            $this->rmdir($dir . '/' . $file);
          } else {
            unlink($dir . '/' . $file);
          }
        }
      }

      return rmdir($dir);
    }

    function displayPath($pathname) {
      if ( DIRECTORY_SEPARATOR == '/' ) {
        return $pathname;
      }

      return str_replace('/', DIRECTORY_SEPARATOR, $pathname);
    }
// OSCOM v2.2rc2a compatibility
    function getIpAddress() {
      if ( function_exists('tep_get_ip_address') ) {
        return tep_get_ip_address();
      }
      global $HTTP_SERVER_VARS;
      $ip_address = null;
      $ip_addresses = array();
      if (isset($HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR']) && !empty($HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'])) {
        foreach ( array_reverse(explode(',', $HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'])) as $x_ip ) {
          $x_ip = trim($x_ip);
          if ($this->isValidIpAddress($x_ip)) {
            $ip_addresses[] = $x_ip;
          }
        }
      }
      if (isset($HTTP_SERVER_VARS['HTTP_CLIENT_IP']) && !empty($HTTP_SERVER_VARS['HTTP_CLIENT_IP'])) {
        $ip_addresses[] = $HTTP_SERVER_VARS['HTTP_CLIENT_IP'];
      }
      if (isset($HTTP_SERVER_VARS['HTTP_X_CLUSTER_CLIENT_IP']) && !empty($HTTP_SERVER_VARS['HTTP_X_CLUSTER_CLIENT_IP'])) {
        $ip_addresses[] = $HTTP_SERVER_VARS['HTTP_X_CLUSTER_CLIENT_IP'];
      }
      if (isset($HTTP_SERVER_VARS['HTTP_PROXY_USER']) && !empty($HTTP_SERVER_VARS['HTTP_PROXY_USER'])) {
        $ip_addresses[] = $HTTP_SERVER_VARS['HTTP_PROXY_USER'];
      }
      $ip_addresses[] = $HTTP_SERVER_VARS['REMOTE_ADDR'];
      foreach ( $ip_addresses as $ip ) {
        if (!empty($ip) && $this->isValidIpAddress($ip)) {
          $ip_address = $ip;
          break;
        }
      }
      return $ip_address;
    }
// OSCOM v2.2rc2a compatibility
    function isValidIpAddress($ip_address) {
      if ( function_exists('tep_validate_ip_address') ) {
        return tep_validate_ip_address($ip_address);
      }
      if (function_exists('filter_var') && defined('FILTER_VALIDATE_IP')) {
        return filter_var($ip_address, FILTER_VALIDATE_IP, array('flags' => FILTER_FLAG_IPV4));
      }
      if (preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $ip_address)) {
        $parts = explode('.', $ip_address);
        foreach ($parts as $ip_parts) {
          if ( (intval($ip_parts) > 255) || (intval($ip_parts) < 0) ) {
            return false; // number is not within 0-255
          }
        }
        return true;
      }
      return false;
    }
  }
?>
