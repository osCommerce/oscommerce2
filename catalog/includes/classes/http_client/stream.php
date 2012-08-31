<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

  class httpClient_stream {
    function execute($parameters) {
      $http_response_header = array();

      $url = $parameters['server']['scheme'] . '://' . $parameters['server']['host'] . (isset($parameters['server']['port']) ? ':' . $parameters['server']['port'] : '') . $parameters['server']['path'] . (isset($parameters['server']['query']) ? '?' . $parameters['server']['query'] : '');

      $options = array('http' => array('method' => 'GET',
                                       'protocol_version' => '1.1',
                                       'request_fulluri' => true,
                                       'follow_location' => true,
                                       'max_redirects' => 5));

      if (isset($parameters['parameters'])) {
        $options['http']['content'] = $parameters['parameters'];
      }

      if ($parameters['method'] == 'post') {
        $options['http']['method'] = 'POST';
      } elseif ($parameters['method'] == 'put') {
        $options['http']['method'] = 'PUT';
      }

      if ( (strlen($options['http']['content']) < 1) && ($options['http']['method'] == 'POST') ) {
        $options['http']['method'] = 'GET';
      }

      if ( !isset($parameters['header']) ) {
        $parameters['header'] = array();
      }

      if ($parameters['method'] == 'post') {
        $parameters['header'][] = 'Content-Type: application/x-www-form-urlencoded';
      }

      if (isset($parameters['parameters'])) {
        $parameters['header'][] = 'Content-Length: ' . strlen($parameters['parameters']);
      }

      $add_host = true;
      $add_connection = true;

      foreach ($parameters['header'] as $h) {
        if (strtolower(substr($h, 0, 5)) == 'host:') {
          $add_host = false;
        } elseif (strtolower(substr($h, 0, 11)) == 'Connection:') {
          $add_connection = false;
        }
      }

      if ($add_host === true) {
        $parameters['header'][] = 'Host: ' . $parameters['server']['host'] . ':' . $parameters['server']['port'];
      }

      if ($add_connection === true) {
        $parameters['header'][] = 'Connection: Close';
      }

      $options['http']['header'] = implode("\r\n", $parameters['header']);

      if ( isset($parameters['version']) && !empty($parameters['version']) ) {
        switch ($parameters['version']) {
          case '1.0':
            $options['http']['protocol_version'] = '1.0';
            break;

          case '1.1':
            $options['http']['protocol_version'] = '1.1';
            break;
        }
      }

      if ( isset($parameters['proxy']) && !empty($parameters['proxy']) ) {
        $options['http']['proxy'] = $parameters['proxy'];
      }

      if ( $parameters['server']['scheme'] === 'https' ) {
        $options['ssl'] = array('verify_peer' => true);

        if ( isset($parameters['cafile']) && file_exists($parameters['cafile']) ) {
          $options['ssl']['cafile'] = $parameters['cafile'];
        }

        if ( isset($parameters['certificate']) ) {
          $options['ssl']['local_cert'] = $parameters['certificate'];
        }
      }

      $context = stream_context_create($options);

      $result = file_get_contents($url, false, $context);

      if ($result === false) {
        return false;
      }

      $response = array('code' => null,
                        'headers' => implode("\n", $http_response_header), // $http_response_header is automatically set by PHP
                        'body' => $result);

      if (preg_match('|^HTTP/\S+ (\d+) |i', $http_response_header[0], $a )) {
        $response['code'] = (int)$a[1];
      }

      return $response;
    }

    function can_use() {
      return (PHP_VERSION >= 5) && extension_loaded('openssl') && ((bool)ini_get('allow_url_fopen') === true);
    }
  }
?>
