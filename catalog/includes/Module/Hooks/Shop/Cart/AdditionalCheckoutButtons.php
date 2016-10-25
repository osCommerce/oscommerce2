<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM\Module\Hooks\Shop\Cart;

class AdditionalCheckoutButtons
{
    public function display() {
        global $payment_modules;

        return implode('', $payment_modules->checkout_initialization_method());
    }
}
