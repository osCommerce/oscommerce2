<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

class OSCOM
{
    public static function link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true)
    {
        global $request_type, $session_started, $SID;

        $page = tep_output_string($page);

        if (!tep_not_null($page)) {
            die('</td></tr></table></td></tr></table><br /><br /><font color="#ff0000"><strong>Error!</strong></font><br /><br /><strong>Unable to determine the page link!<br /><br />');
        }

        if ($connection == 'NONSSL') {
            $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
        } elseif ($connection == 'SSL') {
            if (ENABLE_SSL == true) {
                $link = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
            } else {
                $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
            }
        } else {
            die('</td></tr></table></td></tr></table><br /><br /><font color="#ff0000"><strong>Error!</strong></font><br /><br /><strong>Unable to determine connection method on a link!<br /><br />Known methods: NONSSL SSL</strong><br /><br />');
        }

        if (tep_not_null($parameters)) {
            $link .= $page . '?' . tep_output_string($parameters);
            $separator = '&';
        } else {
            $link .= $page;
            $separator = '?';
        }

        while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);

    // Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
        if ( ($add_session_id == true) && ($session_started == true) && (SESSION_FORCE_COOKIE_USE == 'False') ) {
            if (tep_not_null($SID)) {
                $_sid = $SID;
            } elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
                if (HTTP_COOKIE_DOMAIN != HTTPS_COOKIE_DOMAIN) {
                    $_sid = session_name() . '=' . session_id();
                }
            }
        }

        if (isset($_sid)) {
            $link .= $separator . tep_output_string($_sid);
        }

        while (strpos($link, '&&') !== false) $link = str_replace('&&', '&', $link);

        if ( (SEARCH_ENGINE_FRIENDLY_URLS == 'true') && ($search_engine_safe == true) ) {
            $link = str_replace('?', '/', $link);
            $link = str_replace('&', '/', $link);
            $link = str_replace('=', '/', $link);
        } else {
            $link = str_replace('&', '&amp;', $link);
        }

        return $link;
    }
}
