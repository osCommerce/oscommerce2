<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class hook_shop_cart_alternative_checkout_buttons {
    function listen_displayAlternativeCheckoutButtons() {
      global $payment_modules;

      $initialize_checkout_methods = $payment_modules->checkout_initialization_method();

      if ( !empty($initialize_checkout_methods) ) {
        $output = '<p align="right" style="clear: both; padding: 15px 50px 0 0;">' . TEXT_ALTERNATIVE_CHECKOUT_METHODS . '</p>';

        foreach ( $initialize_checkout_methods as $value ) {
          $output .= '<p align="right">' . $value . '</p>';
        }

        return $output;
      }
    }
  }
?>
