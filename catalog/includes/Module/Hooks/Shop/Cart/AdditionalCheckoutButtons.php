<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM\Module\Hooks\Shop\Cart;

class AdditionalCheckoutButtons
{
    public function display() {
        global $payment_modules;

        return implode('', $payment_modules->checkout_initialization_method());
    }
}
