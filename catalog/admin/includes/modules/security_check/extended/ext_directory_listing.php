<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class securityCheckExtended_ext_directory_listing {
    var $type = 'warning';
    var $has_doc = true;

    function securityCheckExtended_ext_directory_listing() {
      global $language;

      include(DIR_FS_ADMIN . 'includes/languages/' . $language . '/modules/security_check/extended/ext_directory_listing.php');

      $this->title = MODULE_SECURITY_CHECK_EXTENDED_EXT_DIRECTORY_LISTING_TITLE;
    }

    function pass() {
      $request = $this->getHttpRequest(tep_catalog_href_link('ext/'));

      return $request['http_code'] != 200;
    }

    function getMessage() {
      return MODULE_SECURITY_CHECK_EXTENDED_EXT_DIRECTORY_LISTING_HTTP_200;
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

      $result = curl_exec($curl);

      $info = curl_getinfo($curl);

      curl_close($curl);

      return $info;
    }
  }
?>
