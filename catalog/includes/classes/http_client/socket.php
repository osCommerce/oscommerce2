<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

  class httpClient_socket {
    function execute($parameters) {
      if ( isset($parameters['proxy']) && !empty($parameters['proxy']) ) {
        list($proxy_server, $proxy_port) = explode(':', $parameters['proxy'], 2);

        if (empty($proxy_port)) {
          $proxy_port = '80';
        }

        $conn_server = $proxy_server;
        $conn_port = $proxy_port;

        $request_url = $parameters['server']['scheme'] . '://' . $parameters['server']['host'] . (isset($parameters['server']['port']) ? ':' . $parameters['server']['port'] : '') . $parameters['server']['path'] . (isset($parameters['server']['query']) ? '?' . $parameters['server']['query'] : '');
      } else {
        $conn_server = ($parameters['server']['scheme'] == 'https' ? 'ssl://' : '') . $parameters['server']['host'];
        $conn_port = $parameters['server']['port'];

        $request_url = $parameters['server']['path'] . (isset($parameters['server']['query']) ? '?' . $parameters['server']['query'] : '');
      }

      $protocol_version = ( isset($parameters['version']) && !empty($parameters['version']) ) ? $parameters['version'] : '1.1';

      $command = strtoupper($parameters['method']) . ' ' . $request_url . ' HTTP/' . $protocol_version . "\r\n";

      $add_host = true;
      $headers = '';

      if (!empty($parameters['header'])) {
        foreach ($parameters['header'] as $h) {
          $headers .= $h . "\r\n";

          if (strtolower(substr($h, 0, 5)) == 'host:') {
            $add_host = false;
          }
        }
      }

      if ($add_host === true) {
        $command .= 'Host: ' . $parameters['server']['host'] . ':' . $parameters['server']['port'] . "\r\n";
      }

      if (isset($parameters['parameters'])) {
        $command .= 'Content-Length: ' . strlen($parameters['parameters']) . "\r\n";
      }

      if ($parameters['method'] == 'post') {
        $command .= 'Content-Type: application/x-www-form-urlencoded' . "\r\n";
      }

      $command .= $headers . "\r\n";

      if (isset($parameters['parameters'])) {
        $command .= $parameters['parameters'];
      }

      $agro = fsockopen($conn_server, $conn_port);

      if ($agro === false) {
        return false;
      }

      fwrite($agro, $command);

      $result = '';

      while ( !feof($agro) ) {
        $result .= fgets($agro, 1024);
      }

      fclose($agro);

      list($headers, $body) = explode("\r\n\r\n", $result, 2);

      $response = array('code' => null,
                        'headers' => $headers,
                        'body' => $body);

      if (preg_match('|^HTTP/\S+ (\d+) |i', substr($headers, 0, strpos($headers, "\n")), $a )) {
        $response['code'] = (int)$a[1];
      }

      return $response;
    }

    function can_use() {
      return true;
    }
  }
?>
