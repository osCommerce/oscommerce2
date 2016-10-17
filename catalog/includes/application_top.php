<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\OSCOM;
  use OSC\OM\Registry;

// start the timer for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());
  define('OSCOM_BASE_DIR', __DIR__ . '/OSC/');

// set the level of error reporting
  error_reporting(E_ALL & ~E_DEPRECATED);

  require(OSCOM_BASE_DIR . 'OM/OSCOM.php');
  spl_autoload_register('OSC\OM\OSCOM::autoload');

  OSCOM::initialize();

  if (!OSCOM::configExists('db_server') || (strlen(OSCOM::getConfig('db_server')) < 1)) {
    if (is_dir('install')) {
      header('Location: install/index.php');
      exit;
    }
  }

  require('includes/functions/general.php');
  require('includes/classes/shopping_cart.php');
  require('includes/classes/navigation_history.php');
  require('includes/classes/currencies.php');
  require('includes/classes/mime.php');
  require('includes/classes/email.php');
  require('includes/classes/language.php');
  require('includes/classes/action_recorder.php');
  require('includes/classes/alertbox.php');
  require('includes/classes/message_stack.php');
  require('includes/functions/whos_online.php');
  require('includes/functions/password_funcs.php');
  require('includes/functions/validations.php');
  require('includes/functions/banner.php');
  require('includes/functions/specials.php');
  require('includes/classes/osc_template.php');
  require('includes/classes/category_tree.php');
  require('includes/classes/breadcrumb.php');

  OSCOM::loadSite('Shop');

  $OSCOM_Db = Registry::get('Db');

  Registry::get('Hooks')->watch('Session', 'Recreated', 'execute', function($parameters) {
    tep_whos_online_update_session_id($parameters['old_id'], session_id());
  });

// if gzip_compression is enabled, start to buffer the output
  if ((GZIP_COMPRESSION == 'true') && extension_loaded('zlib') && !headers_sent()) {
    if ((int)ini_get('zlib.output_compression') < 1) {
      ob_start('ob_gzhandler');
    }
  } elseif (function_exists('ini_set')) {
    ini_set('zlib.output_compression_level', GZIP_LEVEL);
  }

// Shopping cart actions
  if ( isset($_GET['action']) ) {
// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled
    if ( Registry::get('Session')->hasStarted() === false ) {
      OSCOM::redirect('cookie_usage.php');
    }

    if ( DISPLAY_CART == 'true' ) {
      $goto =  'shopping_cart.php';
      $parameters = array('action', 'cPath', 'products_id', 'pid');
    } else {
      $goto = $PHP_SELF;

      if ( ($_GET['action'] == 'buy_now') || ($_GET['action'] == 'remove_product') ) {
        $parameters = array('action', 'pid', 'products_id');
      } else {
        $parameters = array('action', 'pid');
      }
    }

    switch ( $_GET['action'] ) {
      // customer wants to update the product quantity in their shopping cart
      case 'update_product' : for ($i=0, $n=sizeof($_POST['products_id']); $i<$n; $i++) {
                                $attributes = ($_POST['id'][$_POST['products_id'][$i]]) ? $_POST['id'][$_POST['products_id'][$i]] : '';
                                $_SESSION['cart']->add_cart($_POST['products_id'][$i], $_POST['cart_quantity'][$i], $attributes, false);
                                $messageStack->add_session('product_action', sprintf(PRODUCT_ADDED, tep_get_products_name((int)$_POST['products_id'][$i])), 'success');
                              }
                              OSCOM::redirect($goto, tep_get_all_get_params($parameters));
                              break;
      // customer adds a product from the products page
      case 'add_product' :    if (isset($_POST['products_id']) && is_numeric($_POST['products_id'])) {
                                $attributes = isset($_POST['id']) ? $_POST['id'] : '';
                                $_SESSION['cart']->add_cart($_POST['products_id'], $_SESSION['cart']->get_quantity(tep_get_uprid($_POST['products_id'], $attributes))+1, $attributes);
                                $messageStack->add_session('product_action', sprintf(PRODUCT_ADDED, tep_get_products_name((int)$_POST['products_id'])), 'success');
                              }
                              OSCOM::redirect($goto, tep_get_all_get_params($parameters));
                              break;
      // customer removes a product from their shopping cart
      case 'remove_product' : if (isset($_GET['products_id'])) {
                                $_SESSION['cart']->remove($_GET['products_id']);
                                $messageStack->add_session('product_action', sprintf(PRODUCT_REMOVED, tep_get_products_name($_GET['products_id'])), 'warning');
                              }
                              OSCOM::redirect($goto, tep_get_all_get_params($parameters));
                              break;
      // performed by the 'buy now' button in product listings and review page
      case 'buy_now' :        if (isset($_GET['products_id'])) {
                                if (tep_has_product_attributes($_GET['products_id'])) {
                                  OSCOM::redirect('product_info.php', 'products_id=' . $_GET['products_id']);
                                } else {
                                  $_SESSION['cart']->add_cart($_GET['products_id'], $_SESSION['cart']->get_quantity($_GET['products_id'])+1);
                                  $messageStack->add_session('product_action', sprintf(PRODUCT_ADDED, tep_get_products_name((int)$_GET['products_id'])), 'success');
                                }
                              }
                              OSCOM::redirect($goto, tep_get_all_get_params($parameters));
                              break;
      case 'notify' :         if ( isset($_SESSION['customer_id']) ) {
                                if (isset($_GET['products_id'])) {
                                  $notify = $_GET['products_id'];
                                } elseif (isset($_GET['notify'])) {
                                  $notify = $_GET['notify'];
                                } elseif (isset($_POST['notify'])) {
                                  $notify = $_POST['notify'];
                                } else {
                                  OSCOM::redirect($PHP_SELF, tep_get_all_get_params(array('action', 'notify')));
                                }
                                if (!is_array($notify)) $notify = array($notify);
                                for ($i=0, $n=sizeof($notify); $i<$n; $i++) {
                                  $Qcheck = $OSCOM_Db->get('products_notifications', 'products_id', ['customers_id' => $_SESSION['customer_id'], 'products_id' => $notify[$i]]);

                                  if ($Qcheck->fetch() === false) {
                                    $OSCOM_Db->save('products_notifications', ['products_id' => $notify[$i], 'customers_id' => $_SESSION['customer_id'], 'date_added' => 'now()']);
                                    $messageStack->add_session('product_action', sprintf(PRODUCT_SUBSCRIBED, tep_get_products_name((int)$notify[$i])), 'success');
                                  }
                                }
                                OSCOM::redirect($PHP_SELF, tep_get_all_get_params(array('action', 'notify')));
                              } else {
                                $_SESSION['navigation']->set_snapshot();
                                OSCOM::redirect('login.php', '', 'SSL');
                              }
                              break;
      case 'notify_remove' :  if ( isset($_SESSION['customer_id']) && isset($_GET['products_id'])) {
                                $Qcheck = $OSCOM_Db->get('products_notifications', 'products_id', ['customers_id' => $_SESSION['customer_id'], 'products_id' => $_GET['products_id']]);

                                if ($Qcheck->fetch() !== false) {
                                  $OSCOM_Db->delete('products_notifications', ['customers_id' => $_SESSION['customer_id'], 'products_id' => $_GET['products_id']]);
                                  $messageStack->add_session('product_action', sprintf(PRODUCT_UNSUBSCRIBED, tep_get_products_name((int)$_GET['products_id'])), 'warning');
                                }
                                OSCOM::redirect($PHP_SELF, tep_get_all_get_params(array('action')));
                              } else {
                                $_SESSION['navigation']->set_snapshot();
                                OSCOM::redirect('login.php', '', 'SSL');
                              }
                              break;
      case 'cust_order' :     if ( isset($_SESSION['customer_id']) && isset($_GET['pid']) ) {
                                if (tep_has_product_attributes($_GET['pid'])) {
                                  OSCOM::redirect('product_info.php', 'products_id=' . $_GET['pid']);
                                } else {
                                  $_SESSION['cart']->add_cart($_GET['pid'], $_SESSION['cart']->get_quantity($_GET['pid'])+1);
                                }
                              }
                              OSCOM::redirect($goto, tep_get_all_get_params($parameters));
                              break;
    }
  }

// calculate category path
  if ( isset($_GET['cPath']) ) {
    $cPath = $_GET['cPath'];
  } elseif ( isset($_GET['products_id']) && !isset($_GET['manufacturers_id']) ) {
    $cPath = tep_get_product_path($_GET['products_id']);
  } else {
    $cPath = '';
  }

  if ( !empty($cPath) ) {
    $cPath_array = tep_parse_category_path($cPath);
    $cPath = implode('_', $cPath_array);
    $current_category_id = $cPath_array[(sizeof($cPath_array)-1)];
  } else {
    $current_category_id = 0;
  }

// add category names or the manufacturer name to the breadcrumb trail
  if ( isset($cPath_array) ) {
    for ( $i=0, $n=sizeof($cPath_array); $i<$n; $i++ ) {
      $Qcategories = $OSCOM_Db->get('categories_description', 'categories_name', ['categories_id' => $cPath_array[$i], 'language_id' => $_SESSION['languages_id']]);

      if ($Qcategories->fetch() !== false) {
        $breadcrumb->add($Qcategories->value('categories_name'), OSCOM::link('index.php', 'cPath=' . implode('_', array_slice($cPath_array, 0, ($i+1)))));
      } else {
        break;
      }
    }
  } elseif ( isset($_GET['manufacturers_id']) ) {
    $Qmanufacturer = $OSCOM_Db->get('manufacturers', 'manufacturers_name', ['manufacturers_id' => $_GET['manufacturers_id']]);

    if ($Qmanufacturer->fetch() !== false) {
      $breadcrumb->add($Qmanufacturer->value('manufacturers_name'), OSCOM::link('index.php', 'manufacturers_id=' . $_GET['manufacturers_id']));
    }
  }
?>
