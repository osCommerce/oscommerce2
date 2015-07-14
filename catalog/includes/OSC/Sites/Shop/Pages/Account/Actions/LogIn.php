<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\Sites\Shop\Pages\Account\Actions;

use OSC\OM\OSCOM;
use OSC\OM\Registry;

class LogIn extends \OSC\OM\PagesActionsAbstract
{
    public function execute()
    {
        global $login_customer_id, $oscTemplate, $breadcrumb;

        $this->page->setFile('login.php');

// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled (or the session has not started)
        if (session_status() !== PHP_SESSION_ACTIVE) {
            if (!isset($_GET['cookie_test'])) {
                $all_get = tep_get_all_get_params([
                    'Account',
                    'LogIn',
                    'Process'
                ]);

                OSCOM::redirect('index.php', 'Account&LogIn&' . $all_get . (empty($all_get) ? '' : '&') . 'cookie_test=1', 'SSL');
            }

            OSCOM::redirect('cookie_usage.php');
        }

// login content module must return $login_customer_id as an integer after successful customer authentication
        $login_customer_id = false;

        $this->page->data['content'] = $oscTemplate->getContent('login');

        require(OSCOM::BASE_DIR . 'languages/' . $_SESSION['language'] . '/login.php');

        $breadcrumb->add(NAVBAR_TITLE, OSCOM::link('index.php', 'Account&LogIn', 'SSL'));
    }
}
