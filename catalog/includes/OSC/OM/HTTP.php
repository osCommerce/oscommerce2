<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

class HTTP
{
    public static function redirect($url, $http_response_code = null)
    {
        if ((strstr($url, "\n") === false) && (strstr($url, "\r") === false)) {
            if ( strpos($url, '&amp;') !== false ) {
                $url = str_replace('&amp;', '&', $url);
            }

            header('Location: ' . $url, true, $http_response_code);
        }

        exit;
    }

    /**
     * @param array $parameters url, headers, parameters, method, verify_ssl, cafile, certificate, proxy
     */

    public static function getResponse(array $parameters)
    {
        $parameters['server'] = parse_url($parameters['url']);

        if (!isset($parameters['server']['port'])) {
            $parameters['server']['port'] = ($parameters['server']['scheme'] == 'https') ? 443 : 80;
        }

        if (!isset($parameters['server']['path'])) {
            $parameters['server']['path'] = '/';
        }

        if (isset($parameters['server']['user']) && isset($parameters['server']['pass'])) {
            $parameters['headers'][] = 'Authorization: Basic ' . base64_encode($parameters['server']['user'] . ':' . $parameters['server']['pass']);
        }

        unset($parameters['url']);

        if (!isset($parameters['headers']) || !is_array($parameters['headers'])) {
            $parameters['headers'] = [];
        }

        if (!isset($parameters['method'])) {
            if (isset($parameters['parameters'])) {
                $parameters['method'] = 'post';
            } else {
                $parameters['method'] = 'get';
            }
        }

        $curl = curl_init($parameters['server']['scheme'] . '://' . $parameters['server']['host'] . $parameters['server']['path'] . (isset($parameters['server']['query']) ? '?' . $parameters['server']['query'] : ''));

        $curl_options = [
            CURLOPT_PORT => $parameters['server']['port'],
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_ENCODING => '', // disable gzip
            CURLOPT_FOLLOWLOCATION => false // does not work with open_basedir so a workaround is implemented below
        ];

        if (!empty($parameters['headers'])) {
            $curl_options[CURLOPT_HTTPHEADER] = $parameters['headers'];
        }

        if ($parameters['server']['scheme'] == 'https') {
            if (!isset($parameters['verify_ssl']) || ($parameters['verify_ssl'] === true)) {
                $curl_options[CURLOPT_SSL_VERIFYPEER] = true;
                $curl_options[CURLOPT_SSL_VERIFYHOST] = 2;
            } else {
                $curl_options[CURLOPT_SSL_VERIFYPEER] = false;
                $curl_options[CURLOPT_SSL_VERIFYHOST] = false;
            }

            if (!isset($parameters['cafile'])) {
                $parameters['cafile'] = OSCOM::getConfig('dir_root', 'Shop') . 'includes/cacert.pem';
            }

            if (is_file($parameters['cafile'])) {
                $curl_options[CURLOPT_CAINFO] = $parameters['cafile'];
            }

            if (isset($parameters['certificate'])) {
                $curl_options[CURLOPT_SSLCERT] = $parameters['certificate'];
            }
        }

        if ($parameters['method'] == 'post') {
            if (!isset($parameters['parameters'])) {
                $parameters['parameters'] = '';
            }

            $curl_options[CURLOPT_POST] = true;
            $curl_options[CURLOPT_POSTFIELDS] = $parameters['parameters'];
        }

        if (isset($parameters['proxy']) && !empty($parameters['proxy'])) {
            $curl_options[CURLOPT_HTTPPROXYTUNNEL] = true;
            $curl_options[CURLOPT_PROXY] = $parameters['proxy'];
        }

        curl_setopt_array($curl, $curl_options);
        $result = curl_exec($curl);

        if ($result === false) {
            trigger_error(curl_error($curl));

            curl_close($curl);

            return false;
        }

        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headers = trim(substr($result, 0, $header_size));
        $body = substr($result, $header_size);

        curl_close($curl);

        if (($http_code == 301) || ($http_code == 302)) {
            if (!isset($parameters['redir_counter']) || ($parameters['redir_counter'] < 6)) {
                if (!isset($parameters['redir_counter'])) {
                    $parameters['redir_counter'] = 0;
                }

                $matches = [];
                preg_match('/(Location:|URI:)(.*?)\n/i', $headers, $matches);

                $redir_url = trim(array_pop($matches));

                $parameters['redir_counter']++;

                $redir_params = [
                    'url' => $redir_url,
                    'method' => $parameters['method'],
                    'redir_counter', $parameters['redir_counter']
                ];

                $body = static::getResponse($redir_params);
            }
        }

        return $body;
    }
}
