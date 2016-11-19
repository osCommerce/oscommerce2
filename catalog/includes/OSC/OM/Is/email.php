<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM\Is;

class email
{
    public static function execute($email, $disable_dns_check = false)
    {
        $email = trim($email);

        if (!empty($email) && (strlen($email) <= 255) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (($disable_dns_check === false) && (ENTRY_EMAIL_ADDRESS_CHECK == 'true')) {
                $domain = explode('@', $email);

                if (!checkdnsrr($domain[1], 'MX') && !checkdnsrr($domain[1], 'A')) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
