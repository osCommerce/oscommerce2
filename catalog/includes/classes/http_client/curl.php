<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

  class httpClient_curl {
    function execute($parameters) {
      $curl = curl_init($parameters['server']['scheme'] . '://' . $parameters['server']['host'] . $parameters['server']['path'] . (isset($parameters['server']['query']) ? '?' . $parameters['server']['query'] : ''));

      $curl_options = array(CURLOPT_PORT => $parameters['server']['port'],
                            CURLOPT_HEADER => true,
                            CURLOPT_SSL_VERIFYPEER => true,
                            CURLOPT_SSL_VERIFYHOST => 2,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_FORBID_REUSE => true,
                            CURLOPT_FRESH_CONNECT => true,
                            CURLOPT_FOLLOWLOCATION => false); // does not work with open_basedir so a workaround is implemented below

      if ( isset($parameters['header']) && !empty($parameters['header']) ) {
        $curl_options[CURLOPT_HTTPHEADER] = $parameters['header'];
      }

      if ( isset($parameters['version']) && !empty($parameters['version']) ) {
        switch ($parameters['version']) {
          case '1.0':
            $curl_options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_0;
            break;

          case '1.1':
            $curl_options[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
            break;
        }
      }

      if ( isset($parameters['proxy']) && !empty($parameters['proxy']) ) {
        $curl_options[CURLOPT_HTTPPROXYTUNNEL] = true;
        $curl_options[CURLOPT_PROXY] = $parameters['proxy'];
      }

      if ( isset($parameters['cafile']) && !empty($parameters['cafile']) && file_exists($parameters['cafile']) ) {
        $curl_options[CURLOPT_CAINFO] = $parameters['cafile'];
      }

      if ( isset($parameters['certificate']) && !empty($parameters['certificate']) ) {
        $curl_options[CURLOPT_SSLCERT] = $parameters['certificate'];
      }

      if ( $parameters['method'] == 'post' ) {
        $curl_options[CURLOPT_POST] = true;
        $curl_options[CURLOPT_POSTFIELDS] = $parameters['parameters'];
      } elseif ( $parameters['method'] == 'put' ) {
        $curl_options[CURLOPT_PUT] = true;

        $file_handle = tmpfile();
        $file_size = fwrite($file_handle, $parameters['parameters']);
        rewind($file_handle);

        $curl_options[CURLOPT_INFILE] = $file_handle;
        $curl_options[CURLOPT_INFILESIZE] = $file_size;
      }

      curl_setopt_array($curl, $curl_options);
      $result = curl_exec($curl);

      if ( $result === false ) {
        trigger_error(curl_error($curl));

        curl_close($curl);

        if ( ($parameters['method'] == 'put') && is_resource($file_handle) ) {
          fclose($file_handle);
        }

        return false;
      }

      $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

      curl_close($curl);

      if ( ($parameters['method'] == 'put') && is_resource($file_handle) ) {
        fclose($file_handle);
      }

      list($headers, $body) = explode("\r\n\r\n", $result, 2);

      $response = array('code' => $http_code,
                        'headers' => $headers,
                        'body' => $body);

      if ( ($http_code == 301) || ($http_code == 302) ) {
        if ( !isset($parameters['redir_counter']) || ($parameters['redir_counter'] < 6) ) {
          if ( !isset($parameters['redir_counter']) ) {
            $parameters['redir_counter'] = 0;
          }

          $matches = array();
          preg_match('/(Location:|URI:)(.*?)\n/i', $headers, $matches);

          $redir_url = trim(array_pop($matches));

          $parameters['redir_counter']++;

          $redir_params = array('url' => $redir_url,
                                'method' => $parameters['method'],
                                'redir_counter', $parameters['redir_counter'],
                                'header' => array(),
                                'parameters' => '',
                                'server' => parse_url($redir_url));

          if ( !isset($redir_params['server']['port']) ) {
            $redir_params['server']['port'] = ($redir_params['server']['scheme'] == 'https') ? 443 : 80;
          }

          if ( !isset($redir_params['server']['path']) ) {
            $redir_params['server']['path'] = '/';
          }

          $h = new httpClient_curl();
          $response = $h->execute($redir_params);
        }
      }

      return $response;
    }

    function can_use($with_ssl = false) {
      return function_exists('curl_init') && (($with_ssl === false) || defined('CURL_VERSION_SSL'));
    }
  }

  if (!function_exists('curl_setopt_array')) {
    function curl_setopt_array(&$ch, $curl_options) {
      foreach ($curl_options as $option => $value) {
        if (!curl_setopt($ch, $option, $value)) {
          return false;
        }
      }

      return true;
    }
  }
?>
