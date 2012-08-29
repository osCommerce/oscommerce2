<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

  class httpClient {
    var $drivers = array('curl', 'stream');
    var $driver;
    var $url; // array containg scheme, host, port, user, pass, path, query, fragment (from parse_url())
    var $proxyHost, $proxyPort;
    var $protocolVersion;
    var $requestHeaders, $requestBody;
    var $reply; // response code
    var $replyString; // full response

    var $params = array();
    var $response = array();

    function httpClient($host = null, $port = null) {
      if (PHP_VERSION >= 5) {
        array_unshift($this->drivers, 'http_request');
      }

      if (isset($host)) {
        $this->connect($host, $port);
      }
    }

    function connect($host, $port = null) {
      $this->url = parse_url($host);

      if (isset($port)) {
        $this->url['port'] = $port;
      }
    }

    function setProxy($proxyHost, $proxyPort) {
      $this->proxyHost = $proxyHost;
      $this->proxyPort = $proxyPort;
    }

    function makeUri($uri) {
      $a = parse_url($uri);

      if ( (isset($a['scheme'])) && (isset($a['host'])) ) {
        $this->url = $a;
      } else {
        unset($this->url['query']);
        unset($this->url['fragment']);
        $this->url = array_merge($this->url, $a);
      }

      if (isset($this->proxyHost)) {
        $requesturi = 'http://' . $this->url['host'] . (empty($this->url['port']) ? '' : ':' . $this->url['port']) . $this->url['path'] . (empty($this->url['query']) ? '' : '?' . $this->url['query']);
      } else {
        $requesturi = $this->url['path'] . (empty($this->url['query']) ? '' : '?' . $this->url['query']);
      }

      return $requesturi;
    }

    function processReply() {
      $this->replyString = trim(substr($this->response['headers'], 0, strpos($this->response['headers'], "\n")));

      if (preg_match('|^HTTP/\S+ (\d+) |i', $this->replyString, $a )) {
        $this->reply = $a[1];
      } else {
        $this->reply = 'Bad Response';
      }

//get response headers and body
      $this->responseHeaders = $this->processHeader();
      $this->responseBody = $this->processBody();

      return $this->reply;
    }

    function setDriver($driver = null) {
      if (empty($driver)) {
        foreach ($this->drivers as $d) {
          if (file_exists(DIR_FS_CATALOG . 'includes/classes/http_client/' . $d . '.php')) {
            if (!class_exists('httpClient_' . $d)) {
              include(DIR_FS_CATALOG . 'includes/classes/http_client/' . $d . '.php');
            }

            $class_name = 'httpClient_' . $d;
            $ecce_homo_fresco = new $class_name();

            if ($ecce_homo_fresco->can_use() === true) {
              $this->driver = $ecce_homo_fresco;
              break;
            }
          }
        }
      } else {
        $driver = basename($driver);

        if (in_array($driver, $this->drivers) && file_exists(DIR_FS_CATALOG . 'includes/classes/http_client/' . $driver . '.php')) {
          if (!class_exists('httpClient_' . $driver)) {
            include(DIR_FS_CATALOG . 'includes/classes/http_client/' . $driver . '.php');
          }

          $class_name = 'httpClient_' . $driver;
          $ecce_homo_fresco = new $class_name();

          if ($ecce_homo_fresco->can_use() === true) {
            $this->driver = $ecce_homo_fresco;
          } else {
            trigger_error('httpClient() cannot use manually set "' . $driver . '"');
          }
        } else {
          trigger_error('httpClient() manually set "' . $driver . '" driver does not exist');
        }
      }
    }

    function get($url) {
      if (!isset($this->driver)) {
        $this->setDriver();
      }

      $uri = $this->makeUri($url);

      $this->params['server'] = $this->url;
      $this->params['method'] = 'get';
      $this->params['header'] = array();
      $this->params['version'] = $this->protocolVersion;

      if (isset($this->proxyHost)) {
        $this->params['proxy'] = $this->proxyHost . (!empty($this->proxyPort) ? ':' . $this->proxyPort : '');
      }

      if (isset($this->requestHeaders) && is_array($this->requestHeaders)) {
        foreach ($this->requestHeaders as $k => $v) {
          $this->params['header'][] = $k . ': ' . $v;
        }
      }

      $this->responseHeaders = $this->responseBody = '';

      $this->response = $this->driver->execute($this->params);

      if (is_array($this->response) && isset($this->response['code'])) {
        $this->processReply();
      }

      return $this->reply;
    }

    function post($uri, $query_params = null) {
      if (!isset($this->driver)) {
        $this->setDriver();
      }

      $uri = $this->makeUri($uri);

      if (isset($query_params) && is_array($query_params)) {
        $postArray = array();
        foreach ($query_params as $k => $v) {
          $postArray[] = urlencode($k) . '=' . urlencode($v);
        }

        $this->requestBody = implode('&', $postArray);
      }

      $this->params['server'] = $this->url;
      $this->params['method'] = 'post';
      $this->params['header'] = array();
      $this->params['version'] = $this->protocolVersion;
      $this->params['parameters'] = '';

      if (isset($this->proxyHost)) {
        $this->params['proxy'] = $this->proxyHost . (!empty($this->proxyPort) ? ':' . $this->proxyPort : '');
      }

      if (!empty($this->requestBody)) {
        $this->params['parameters'] = $this->requestBody;
      }

      if (isset($this->requestHeaders) && is_array($this->requestHeaders)) {
        foreach ($this->requestHeaders as $k => $v) {
          $this->params['header'][] = $k . ': ' . $v;
        }
      }

      $this->responseHeaders = $this->responseBody = '';

      $this->response = $this->driver->execute($this->params);

      if (is_array($this->response) && isset($this->response['code'])) {
        $this->processReply();
      }

      return $this->reply;
    }

    function head($uri) {
      return $this->get($uri);
    }

    function processHeader() {
      $headers = array();

      foreach (explode("\n", $this->response['headers']) as $h) {
        list($hdr, $value) = explode(': ', $h, 2);
// nasty workaround broken multiple same headers (eg. Set-Cookie headers)
        if (isset($headers[$hdr])) {
          $headers[$hdr] .= '; ' . trim($value);
        } else {
          $headers[$hdr] = trim($value);
        }
      }

      return $headers;
    }

    function processBody() {
      return $this->response['body'];
    }

    function getHeaders() {
      return $this->responseHeaders;
    }

    function getHeader($headername) {
      return $this->responseHeaders[$headername];
    }

    function getBody() {
      return $this->responseBody;
    }

    function getStatus() {
      return $this->reply;
    }

    function getStatusMessage() {
      return $this->replyString;
    }

    function setCredentials($username, $password) {
      $this->addHeader('Authorization', 'Basic ' . base64_encode($username . ':' . $password));
    }

    function setHeaders($headers) {
      if (is_array($headers)) {
        foreach ($headers as $name => $value) {
          $this->requestHeaders[$name] = $value;
        }
      }
    }

    function addHeader($headerName, $headerValue) {
      $this->requestHeaders[$headerName] = $headerValue;
    }

    function removeHeader($headerName) {
      if (isset($this->requestHeaders[$headerName])) {
        unset($this->requestHeaders[$headerName]);
      }
    }

    function setProtocolVersion($version) {
      $this->protocolVersion = $version;
    }

    function put($uri, $filecontent) {
      if (!isset($this->driver)) {
        $this->setDriver();
      }

      $uri = $this->makeUri($uri);

      $this->params['server'] = $this->url;
      $this->params['method'] = 'put';
      $this->params['header'] = array();
      $this->params['version'] = $this->protocolVersion;
      $this->params['parameters'] = $filecontent;

      if (isset($this->proxyHost)) {
        $this->params['proxy'] = $this->proxyHost . (!empty($this->proxyPort) ? ':' . $this->proxyPort : '');
      }

      if (isset($this->requestHeaders) && is_array($this->requestHeaders)) {
        foreach ($this->requestHeaders as $k => $v) {
          $this->params['header'][] = $k . ': ' . $v;
        }
      }

      $this->responseHeaders = $this->responseBody = '';

      $this->response = $this->driver->execute($this->params);

      if (is_array($this->response) && isset($this->response['code'])) {
        $this->processReply();
      }

      return $this->reply;
    }

// Deprecated
    function sendCommand($command) {
      trigger_error('httpClient::sendCommand is deprecated.');
    }

// Deprecated
    function disconnect() {
      trigger_error('httpClient::disconnect is deprecated.');
    }
  }
?>
