<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

  class securityCheckExtended_admin_backup_file {
    var $type = 'error';
    var $has_doc = true;

    protected $lang;

    function __construct() {
      $this->lang = Registry::get('Language');

      $this->lang->loadDefinitions('modules/security_check/extended/admin_backup_file');

      $this->title = OSCOM::getDef('module_security_check_extended_admin_backup_file_title');
    }

    function pass() {
      $backup_directory = OSCOM::getConfig('dir_root') . 'includes/backups/';

      $backup_file = null;

      if ( is_dir($backup_directory) ) {
        $dir = dir($backup_directory);
        $contents = array();
        while ($file = $dir->read()) {
          if ( !is_dir($backup_directory . $file) ) {
            $ext = substr($file, strrpos($file, '.') + 1);

            if ( in_array($ext, array('zip', 'sql', 'gz')) && !isset($contents[$ext]) ) {
              $contents[$ext] = $file;

              if ( $ext != 'sql' ) { // zip and gz (binaries) are prioritized over sql (plain text)
                break;
              }
            }
          }
        }

        if ( isset($contents['zip']) ) {
          $backup_file = $contents['zip'];
        } elseif ( isset($contents['gz']) ) {
          $backup_file = $contents['gz'];
        } elseif ( isset($contents['sql']) ) {
          $backup_file = $contents['sql'];
        }
      }

      $result = true;

      if ( isset($backup_file) ) {
        $request = $this->getHttpRequest(OSCOM::link('includes/backups/' . $backup_file));

        $result = ($request['http_code'] != 200);
      }

      return $result;
    }

    function getMessage() {
      return OSCOM::getDef('module_security_check_extended_admin_backup_file_http_200', [
        'backups_path' => OSCOM::getConfig('http_path', 'Admin') . 'includes/backups/'
      ]);
    }

    function getHttpRequest($url) {

      $server = parse_url($url);

      if (isset($server['port']) === false) {
        $server['port'] = ($server['scheme'] == 'https') ? 443 : 80;
      }

      if (isset($server['path']) === false) {
        $server['path'] = '/';
      }

      $curl = curl_init($server['scheme'] . '://' . $server['host'] . $server['path'] . (isset($server['query']) ? '?' . $server['query'] : ''));
      curl_setopt($curl, CURLOPT_PORT, $server['port']);
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
      curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
      curl_setopt($curl, CURLOPT_NOBODY, true);

      if ( isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) ) {
        curl_setopt($curl, CURLOPT_USERPWD, $_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);

        $this->type = 'warning';
      }

      $result = curl_exec($curl);

      $info = curl_getinfo($curl);

      curl_close($curl);

      return $info;
    }
  }
?>
