<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2012 osCommerce

  Released under the GNU General Public License
*/

  class httpClient_http_request {
    function execute($parameters) {
      $_methods = array('get' => HTTP_METH_GET,
                        'post' => HTTP_METH_POST,
                        'put' => HTTP_METH_PUT);

      $h = new HttpRequest($parameters['server']['scheme'] . '://' . $parameters['server']['host'] . $parameters['server']['path'] . (isset($parameters['server']['query']) ? '?' . $parameters['server']['query'] : ''), $_methods[$parameters['method']], array('redirect' => 5));

      $h->setOptions(array('port' => $parameters['server']['port']));

      if ( isset($parameters['version']) && !empty($parameters['version']) ) {
        switch ($parameters['version']) {
          case '1.0':
            $h->setOptions(array('protocol' => HTTP_VERSION_1_0));
            break;

          case '1.1':
            $h->setOptions(array('protocol' => HTTP_VERSION_1_1));
            break;
        }
      }

      if ( isset($parameters['proxy']) && !empty($parameters['proxy']) ) {
        $h->setOptions(array('proxyhost' => $parameters['proxy']));
      }

      if ( $parameters['method'] == 'post' ) {
        $post_params = array();

        parse_str($parameters['parameters'], $post_params);

        $h->setPostFields($post_params);
      } elseif ( $parameters['method'] == 'put' ) {
        $h->setPutData($parameters['parameters']);
      }

      if ( $parameters['server']['scheme'] === 'https' ) {
        $h->addSslOptions(array('verifypeer' => true,
                                'verifyhost' => true));

        if ( isset($parameters['cafile']) && file_exists($parameters['cafile']) ) {
          $h->addSslOptions(array('cainfo' => $parameters['cafile']));
        }

        if ( isset($parameters['certificate']) ) {
          $h->addSslOptions(array('cert' => $parameters['certificate']));
        }
      }

      $response = array();

      try {
        $h->send();

        $result = $h->getRawResponseMessage();

        list($headers, $body) = explode("\r\n\r\n", $result, 2);

        $response = array('code' => $h->getResponseCode(),
                          'headers' => $headers,
                          'body' => $body);
      } catch ( \Exception $e ) {
        if ( isset($e->innerException) ) {
          trigger_error($e->innerException->getMessage());
        } else {
          trigger_error($e->getMessage());
        }
      }

      return $response;
    }

    function can_use() {
      return class_exists('HttpRequest');
    }
  }
?>
