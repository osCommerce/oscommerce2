<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class securityCheckExtended_admin_backup_file {
    var $type = 'error';
    var $has_doc = true;

    function securityCheckExtended_admin_backup_file() {
      global $language;

      include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/security_check/extended/admin_backup_file.php');

      $this->title = MODULE_SECURITY_CHECK_EXTENDED_ADMIN_BACKUP_FILE_TITLE;
    }

    function pass() {
      $backup_file = null;

      if ( is_dir(DIR_FS_BACKUP) ) {
        $dir = dir(DIR_FS_BACKUP);
        $contents = array();
        while ($file = $dir->read()) {
          if ( !is_dir(DIR_FS_BACKUP . $file) ) {
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
        $request = $this->getHttpRequest(tep_href_link('backups/' . $backup_file));

        $result = ($request['http_code'] != 200);
      }

      return $result;
    }

    function getMessage() {
      return MODULE_SECURITY_CHECK_EXTENDED_ADMIN_BACKUP_FILE_HTTP_200;
    }

    function getHttpRequest($url) {
      global $HTTP_SERVER_VARS;

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

      if ( isset($HTTP_SERVER_VARS['PHP_AUTH_USER']) && isset($HTTP_SERVER_VARS['PHP_AUTH_PW']) ) {
        curl_setopt($curl, CURLOPT_USERPWD, $HTTP_SERVER_VARS['PHP_AUTH_USER'] . ':' . $HTTP_SERVER_VARS['PHP_AUTH_PW']);

        $this->type = 'warning';
      }

      $result = curl_exec($curl);

      $info = curl_getinfo($curl);

      curl_close($curl);

      return $info;
    }
  }
?>
