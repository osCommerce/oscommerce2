<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license GPL; https://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Module\Hooks\Shop\Session;

use OSC\OM\OSCOM;
use OSC\OM\Registry;

class StartAfter
{
    public function execute() {
        global $request_type;

        $OSCOM_Session = Registry::get('Session');

// initialize a session token
        if (!isset($_SESSION['sessiontoken'])) {
            $_SESSION['sessiontoken'] = md5(tep_rand() . tep_rand() . tep_rand() . tep_rand());
        }

// verify the ssl_session_id if the feature is enabled
        if (($request_type == 'SSL') && (SESSION_CHECK_SSL_SESSION_ID == 'True') && (OSCOM::getConfig('ssl', 'Shop') == 'true') && $OSCOM_Session->hasStarted()) {
            if (!isset($_SESSION['SSL_SESSION_ID'])) {
                $_SESSION['SESSION_SSL_ID'] = $_SERVER['SSL_SESSION_ID'];
            }

            if ($_SESSION['SESSION_SSL_ID'] != $_SERVER['SSL_SESSION_ID']) {
                $OSCOM_Session->kill();

                OSCOM::redirect('ssl_check.php');
            }
        }

// verify the browser user agent if the feature is enabled
        if (SESSION_CHECK_USER_AGENT == 'True') {
            if (!isset($_SESSION['SESSION_USER_AGENT'])) {
                $_SESSION['SESSION_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
            }

            if ($_SESSION['SESSION_USER_AGENT'] != $_SERVER['HTTP_USER_AGENT']) {
                $OSCOM_Session->kill();

                OSCOM::redirect('login.php', null, 'SSL');
            }
        }

// verify the IP address if the feature is enabled
        if (SESSION_CHECK_IP_ADDRESS == 'True') {
            if (!isset($_SESSION['SESSION_IP_ADDRESS'])) {
                $_SESSION['SESSION_IP_ADDRESS'] = tep_get_ip_address();
            }

            if ($_SESSION['SESSION_IP_ADDRESS'] != tep_get_ip_address()) {
                $OSCOM_Session->kill();

                OSCOM::redirect('login.php', null, 'SSL');
            }
        }
    }
}
