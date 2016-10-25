<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

use OSC\OM\OSCOM;

class Hash
{
    public static function encrypt($plain)
    {
        if (!class_exists('PasswordHash', false)) {
            include(OSCOM::getConfig('dir_root', 'Shop') . 'includes/classes/passwordhash.php');
        }

        $hasher = new \PasswordHash(10, true);

        return $hasher->HashPassword($plain);
    }

    public static function getRandomString($length, $type = 'mixed')
    {
        if (!in_array($type, [
            'mixed',
            'chars',
            'digits'
        ])) {
            trigger_error('Hash::getRandomString() $type not recognized: ' . $type, E_USER_ERROR);

            return false;
        }

        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';

        $base = '';

        if (($type == 'mixed') || ($type == 'chars')) {
            $base .= $chars;
        }

        if (($type == 'mixed') || ($type == 'digits')) {
            $base .= $digits;
        }

        $rand_value = '';

        do {
            $random = base64_encode(static::getRandomBytes($length));

            for ($i=0, $n=strlen($random); $i<$n; $i++) {
                $char = substr($random, $i, 1);

                if (strpos($base, $char) !== false) {
                    $rand_value .= $char;
                }
            }
        } while (strlen($rand_value) < $length);

        if (strlen($rand_value) > $length) {
            $rand_value = substr($rand_value, 0, $length);
        }

        return $rand_value;
    }

    public static function getRandomBytes($length)
    {
        static $random_state;

        if (!isset($random_state)) {
            $random_state = microtime();

            if (function_exists('getmypid')) {
                $random_state .= getmypid();
            }
        }

        $result = '';

        if (function_exists('openssl_random_pseudo_bytes')) {
            $result = openssl_random_pseudo_bytes($length, $orpb_secure);

            if ($orpb_secure != true) {
                $result = '';
            }
        } elseif (defined('MCRYPT_DEV_URANDOM')) {
            $result = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
        } elseif (@is_readable('/dev/urandom') && ($fh = @fopen('/dev/urandom', 'rb'))) {
            if (function_exists('stream_set_read_buffer')) {
                stream_set_read_buffer($fh, 0);
            }

            $result = fread($fh, $length);

            fclose($fh);
        }

        if (strlen($result) < $length) {
            $result = '';

            for ($i=0; $i<$length; $i+=16) {
                $random_state = md5(microtime() . $random_state);

                $result .= pack('H*', md5($random_state));
            }

            $result = substr($result, 0, $length);
        }

        return $result;
    }
}
