<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM;

use OSC\OM\OSCOM;

class Hash
{
    public static function encrypt($plain, $algo = null)
    {
        if (!isset($algo) || ($algo == 'default') || ($algo == 'bcrypt')) {
            if (!isset($algo) || ($algo == 'default')) {
                $algo = PASSWORD_DEFAULT;
            } else {
                $algo = PASSWORD_BCRYPT;
            }

            return password_hash($plain, $algo);
        }

        if ($algo == 'phpass') {
            if (!class_exists('PasswordHash', false)) {
                include(OSCOM::getConfig('dir_root', 'Shop') . 'includes/third_party/PasswordHash.php');
            }

            $hasher = new \PasswordHash(10, true);

            return $hasher->HashPassword($plain);
        }

        if ($algo == 'salt') {
            $password = '';

            for ($i=0; $i<10; $i++) {
                $password .= static::getRandomInt();
            }

            $salt = substr(md5($password), 0, 2);

            $password = md5($salt . $plain) . ':' . $salt;

            return $password;
        }

        trigger_error('OSC\\OM\\Hash::encrypt() Algorithm "' . $algo . '" unknown.');

        return false;
    }

    public static function verify($plain, $hash)
    {
        $result = false;

        if ((strlen($plain) > 0) && (strlen($hash) > 0)) {
            switch (static::getType($hash)) {
                case 'phpass':
                    if (!class_exists('PasswordHash', false)) {
                        include(OSCOM::getConfig('dir_root', 'Shop') . 'includes/third_party/PasswordHash.php');
                    }

                    $hasher = new \PasswordHash(10, true);

                    $result = $hasher->CheckPassword($plain, $hash);

                    break;

                case 'salt':
                    // split apart the hash / salt
                    $stack = explode(':', $hash, 2);

                    if (count($stack) === 2) {
                        $result = (md5($stack[1] . $plain) == $stack[0]);
                    } else {
                        $result = false;
                    }

                    break;

                default:
                    $result = password_verify($plain, $hash);

                    break;
            }
        }

        return $result;
    }

    public static function needsRehash($hash, $algo = null)
    {
        if (!isset($algo) || ($algo == 'default')) {
            $algo = PASSWORD_DEFAULT;
        } elseif ($algo == 'bcrypt') {
            $algo = PASSWORD_BCRYPT;
        }

        if (!is_int($algo)) {
            trigger_error('OSC\OM\Hash::needsRehash() Algorithm "' . $algo . '" not supported.');
        }

        return password_needs_rehash($hash, $algo);
    }

    public static function getType($hash)
    {
        $info = password_get_info($hash);

        if ($info['algo'] > 0) {
            return $info['algoName'];
        }

        if (substr($hash, 0, 3) == '$P$') {
            return 'phpass';
        }

        if (preg_match('/^[A-Z0-9]{32}\:[A-Z0-9]{2}$/i', $hash) === 1) {
            return 'salt';
        }

        trigger_error('OSC\OM\Hash::getType() hash type not found for "' . substr($hash, 0, 5) . '"');

        return '';
    }

    public static function getRandomInt($min = null, $max = null, $secure = true)
    {
        if (!isset($min)) {
            $min = 0;
        }

        if (!isset($max)) {
            $max = PHP_INT_MAX;
        }

        try {
            $result = random_int($min, $max);
        } catch (\Exception $e) {
            if ($secure === true) {
                throw $e;
            }

            $result = mt_rand($min, $max);
        }

        return $result;
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

    public static function getRandomBytes($length, $secure = true)
    {
        try {
            $result = random_bytes($length);
        } catch (\Exception $e) {
            if ($secure === true) {
                throw $e;
            }

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
