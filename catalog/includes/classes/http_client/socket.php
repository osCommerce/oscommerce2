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
      $add_connection = true;
      $headers = '';

      if (!empty($parameters['header'])) {
        foreach ($parameters['header'] as $h) {
          $headers .= $h . "\r\n";

          if (strtolower(substr($h, 0, 5)) == 'host:') {
            $add_host = false;
          } elseif (strtolower(substr($h, 0, 11)) == 'Connection:') {
            $add_connection = false;
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

      if ($add_connection === true) {
        $command .= 'Connection: Close' . "\r\n";
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

      $counter = 0;

      do {
        $status = socket_get_status($agro);
        if ($status['eof'] == 1) {
          break;
        }

        if ($status['unread_bytes'] > 0) {
          $buffer = fread($agro, $status['unread_bytes']);
          $counter = 0;
        } else {
          $buffer = fread($agro, 128);
          $counter++;
          usleep(2);
        }

        $result .= $buffer;
      } while ( ($status['unread_bytes'] > 0) || ($counter++ < 10) );

      fclose($agro);

      list($headers, $body) = explode("\r\n\r\n", $result, 2);

      if (strpos(strtolower($headers), 'transfer-encoding: chunked') !== false) {
        $body = http_chunked_decode($body);
      }

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

  if (!function_exists('http_chunked_decode')) {
    function http_chunked_decode($str) {
      for ($res = ''; !empty($str); $str = trim($str)) {
        $pos = strpos($str, "\r\n");
        $len = hexdec(substr($str, 0, $pos));
        $res .= substr($str, $pos + 2, $len);
        $str = substr($str, $pos + 2 + $len);
      }

      return $res;
    }
  }
?>
