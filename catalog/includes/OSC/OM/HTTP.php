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
    public static function redirect($url)
    {
        global $request_type;

        if ( (strstr($url, "\n") != false) || (strstr($url, "\r") != false) ) {
            tep_redirect(tep_href_link('index.php', '', 'NONSSL', false));
        }

        if ( (ENABLE_SSL == true) && ($request_type == 'SSL') ) { // We are loading an SSL page
            if (substr($url, 0, strlen(HTTP_SERVER . DIR_WS_HTTP_CATALOG)) == HTTP_SERVER . DIR_WS_HTTP_CATALOG) { // NONSSL url
                $url = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG . substr($url, strlen(HTTP_SERVER . DIR_WS_HTTP_CATALOG)); // Change it to SSL
            }
        }

        if ( strpos($url, '&amp;') !== false ) {
            $url = str_replace('&amp;', '&', $url);
        }

        header('Location: ' . $url);

        tep_exit();
    }
}
