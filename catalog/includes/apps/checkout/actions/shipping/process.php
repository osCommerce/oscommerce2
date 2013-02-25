<?php
/**
 * osCommerce Online Merchant
 * 
 * @copyright Copyright (c) 2013 osCommerce; http://www.oscommerce.com
 * @license GNU General Public License; http://www.oscommerce.com/gpllicense.txt
 */

  class app_checkout_action_shipping_process {
    public static function execute(app $app) {
      global $free_shipping, $shipping_modules;

      if ( isset($_POST['formid']) && ($_POST['formid'] == $_SESSION['sessiontoken']) ) {
        if ( osc_not_null($_POST['comments']) ) {
          $_SESSION['comments'] = trim($_POST['comments']);
        }

        if ( (osc_count_shipping_modules() > 0) || ($free_shipping == true) ) {
          if ( (isset($_POST['shipping'])) && (strpos($_POST['shipping'], '_')) ) {
            $_SESSION['shipping'] = $_POST['shipping'];

            list($module, $method) = explode('_', $_SESSION['shipping']);
            if ( is_object($GLOBALS[$module]) || ($_SESSION['shipping'] == 'free_free') ) {
              if ( $_SESSION['shipping'] == 'free_free' ) {
                $quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
                $quote[0]['methods'][0]['cost'] = '0';
              } else {
                $quote = $shipping_modules->quote($method, $module);
              }

              if ( isset($quote['error']) ) {
                unset($_SESSION['shipping']);
              } else {
                if ( (isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost'])) ) {
                  $_SESSION['shipping'] = array('id' => $_SESSION['shipping'],
                                                'title' => (($free_shipping == true) ?  $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' (' . $quote[0]['methods'][0]['title'] . ')'),
                                                'cost' => $quote[0]['methods'][0]['cost']);

                  osc_redirect(osc_href_link('checkout', 'payment', 'SSL'));
                }
              }
            } else {
              unset($_SESSION['shipping']);
            }
          }
        } else {
          $_SESSION['shipping'] = false;

          osc_redirect(osc_href_link('checkout', 'payment', 'SSL'));
        }
      }

      osc_redirect(osc_href_link('checkout', '', 'SSL'));
    }
  }
?>
