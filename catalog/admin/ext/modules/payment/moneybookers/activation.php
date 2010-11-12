<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  chdir('../../../../');
  require('includes/application_top.php');
  require('../includes/languages/' . $language . '/modules/payment/moneybookers.php');
  require('../includes/modules/payment/moneybookers.php');

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');
  $pass = false;

  switch ($action) {
    case 'verifyEmail':
      $mb = new moneybookers();
      $result = $mb->sendTransactionToGateway('https://www.moneybookers.com/app/email_check.pl', 'email=' . $HTTP_POST_VARS['mb_email'] . '&cust_id=2167348&password=281f2d9f44066eab75db5afb063952b1');
      $result = explode(',', $result, 2);

      if ( (sizeof($result) == 2) && ($result[0] == 'OK') ) {
        $pass = true;

        $email_body = 'Store Name: ' . STORE_NAME . ' (powered by osCommerce Online Merchant (' . $mb->signature . '))' . "\n" .
                      'Merchant Name: ' . STORE_OWNER . "\n" .
                      'Moneybookers E-Mail Address: ' . $HTTP_POST_VARS['mb_email'] . "\n" .
                      'Moneybookers Customer ID: ' . $result[1] . "\n" .
                      'Store URL: ' . tep_catalog_href_link() . "\n" .
                      'Language: ' . $language . "\n";

        tep_mail('', 'ecommerce@moneybookers.com', 'Quick Checkout Account Activation', $email_body, '', $HTTP_POST_VARS['mb_email']);
      }

      break;

    case 'testSecretWord':
      $mb = new moneybookers();
      $result = $mb->sendTransactionToGateway('https://www.moneybookers.com/app/secret_word_check.pl', 'email=' . MODULE_PAYMENT_MONEYBOOKERS_PAY_TO . '&secret=' . md5('281f2d9f44066eab75db5afb063952b1' . md5(MODULE_PAYMENT_MONEYBOOKERS_SECRET_WORD)) . '&cust_id=2167348');

      if ($result == 'OK') {
        $pass = true;
      }

      break;

    case 'coreRequired':
      break;

    default:
      $action = 'verifyEmail';
      break;
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo MB_ACTIVATION_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td class="main">
<?php
  if ($action == 'verifyEmail') {
    if (isset($result)) {
      if ($pass == true) {
?>
          <p><strong><u><?php echo MB_ACTIVATION_ACTIVATE_TITLE; ?></u></strong></p>
          <p><?php echo MB_ACTIVATION_ACTIVATE_TEXT; ?></p>
          <form name="activation" action="<?php echo tep_href_link(FILENAME_MODULES, 'set=payment&module=moneybookers&action=install&active=true&email=' . $HTTP_POST_VARS['mb_email'] . '&custid=' . $result[1]); ?>" method="post">
            <p><input type="submit" value="<?php echo MB_ACTIVATION_CONTINUE_BUTTON; ?>"></p>
          </form>
<?php
      } else {
?>
          <div style="padding: 5px; background-color: #ffcccc;">
            <p><strong><u><?php echo MB_ACTIVATION_NONEXISTING_ACCOUNT_TITLE; ?></u></strong></p>
            <p><?php echo MB_ACTIVATION_NONEXISTING_ACCOUNT_TEXT; ?></p>
          </div>
<?php
      }
    }

    if (!isset($result) || ($pass == false)) {
?>
          <p><strong><u><?php echo MB_ACTIVATION_ACCOUNT_TITLE; ?></u></strong></p>
          <p><?php echo MB_ACTIVATION_ACCOUNT_TEXT; ?></p>
          <form name="activation" action="<?php echo tep_href_link('ext/modules/payment/moneybookers/activation.php', 'action=verifyEmail'); ?>" method="post">
            <p><?php echo MB_ACTIVATION_EMAIL_ADDRESS . ' ' . tep_draw_input_field('mb_email', STORE_OWNER_EMAIL_ADDRESS); ?></p>
            <p><input type="submit" value="<?php echo MB_ACTIVATION_VERIFY_ACCOUNT_BUTTON; ?>"></p>
          </form>
<?php
    }
  } elseif ($action == 'testSecretWord') {
    if (isset($result) && ($pass == true)) {
?>
          <p><strong><u><?php echo MB_ACTIVATION_SECRET_WORD_TITLE; ?></u></strong></p>
          <p><?php echo MB_ACTIVATION_SECRET_WORD_SUCCESS_TEXT; ?></p>
          <form name="activation" action="<?php echo tep_href_link(FILENAME_MODULES, 'set=payment&module=moneybookers&action=edit'); ?>" method="post">
            <p><input type="submit" value="<?php echo MB_ACTIVATION_CONTINUE_BUTTON; ?>"></p>
          </form>
<?php
    } else {
      if ($result == 'VELOCITY_CHECK_EXCEEDED') {
?>
          <div style="padding: 5px; background-color: #ff9999;">
            <p><strong><u><?php echo MB_ACTIVATION_SECRET_WORD_ERROR_TITLE; ?></u></strong></p>
            <p><?php echo MB_ACTIVATION_SECRET_WORD_ERROR_EXCEEDED; ?></p>
          </div>
<?php
      }
?>
          <p><strong><u><?php echo MB_ACTIVATION_SECRET_WORD_TITLE; ?></u></strong></p>
          <p><?php echo MB_ACTIVATION_SECRET_WORD_FAIL_TEXT; ?></p>
          <form name="activation" action="<?php echo tep_href_link(FILENAME_MODULES, 'set=payment&module=moneybookers&action=edit'); ?>" method="post">
            <p><input type="submit" value="<?php echo MB_ACTIVATION_CONTINUE_BUTTON; ?>"></p>
          </form>
<?php
    }
  } elseif ($action == 'coreRequired') {
?>
          <p><strong><u><?php echo MB_ACTIVATION_CORE_REQUIRED_TITLE; ?></u></strong></p>
          <p><?php echo MB_ACTIVATION_CORE_REQUIRED_TEXT; ?></p>
          <form name="activation" action="<?php echo tep_href_link('ext/modules/payment/moneybookers/activation.php'); ?>" method="post">
            <p><input type="submit" value="<?php echo MB_ACTIVATION_CONTINUE_BUTTON; ?>"></p>
          </form>
<?php
  }
?>
          <div style="padding: 5px; background-color: #ffcc99;">
            <p><strong><u><?php echo MB_ACTIVATION_SUPPORT_TITLE; ?></u></strong></p>
            <p><?php echo MB_ACTIVATION_SUPPORT_TEXT; ?></p>
          </div>
        </td>
      </tr>
    </table>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
