<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM\Module\Hooks\Shop\Session;

use OSC\OM\OSCOM;

class StartBefore
{
    public function execute($parameters) {
        if (SESSION_BLOCK_SPIDERS == 'True') {
            $user_agent = '';

            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
            }

            if (!empty($user_agent)) {
                foreach (file(OSCOM::getConfig('dir_root') . 'includes/spiders.txt') as $spider) {
                    if (!empty($spider)) {
                        if (strpos($user_agent, $spider) !== false) {
                            $parameters['can_start'] = false;
                            break;
                        }
                    }
                }
            }
        }
    }
}
