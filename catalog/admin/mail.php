<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2010 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if ( ($action == 'send_email_to_user') && isset($_POST['customers_email_address']) && !isset($_POST['back_x']) ) {
    switch ($_POST['customers_email_address']) {
      case '***':
        $Qmail = $OSCOM_Db->get('customers', [
          'customers_firstname',
          'customers_lastname',
          'customers_email_address'
        ]);

        $mail_sent_to = TEXT_ALL_CUSTOMERS;
        break;
      case '**D':
        $Qmail = $OSCOM_Db->get('customers', [
          'customers_firstname',
          'customers_lastname',
          'customers_email_address'
        ], [
          'customers_newsletter' => '1'
        ]);

        $mail_sent_to = TEXT_NEWSLETTER_CUSTOMERS;
        break;
      default:
        $customers_email_address = HTML::sanitize($_POST['customers_email_address']);

        $Qmail = $OSCOM_Db->get('customers', [
          'customers_firstname',
          'customers_lastname',
          'customers_email_address'
        ], [
          'customers_email_address' => $customers_email_address
        ]);

        $mail_sent_to = $customers_email_address;
        break;
    }

    $from = HTML::sanitize($_POST['from']);
    $subject = HTML::sanitize($_POST['subject']);
    $message = HTML::sanitize($_POST['message']);

    //Let's build a message object using the email class
    $mimemessage = new email(array('X-Mailer: osCommerce'));

    // Build the text version
    $text = strip_tags($message);
    if (EMAIL_USE_HTML == 'true') {
      $mimemessage->add_html($message, $text);
    } else {
      $mimemessage->add_text($text);
    }

    $mimemessage->build_message();

    while ($Qmail->fetch()) {
      $mimemessage->send($Qmail->value('customers_firstname') . ' ' . $Qmail->value('customers_lastname'), $Qmail->value('customers_email_address'), '', $from, $subject);
    }

    OSCOM::redirect(FILENAME_MAIL, 'mail_sent_to=' . urlencode($mail_sent_to));
  }

  if ( ($action == 'preview') && !isset($_POST['customers_email_address']) ) {
    $messageStack->add(ERROR_NO_CUSTOMER_SELECTED, 'error');
  }

  if (isset($_GET['mail_sent_to'])) {
    $messageStack->add(sprintf(NOTICE_EMAIL_SENT_TO, $_GET['mail_sent_to']), 'success');
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  if ( ($action == 'preview') && isset($_POST['customers_email_address']) ) {
    switch ($_POST['customers_email_address']) {
      case '***':
        $mail_sent_to = TEXT_ALL_CUSTOMERS;
        break;
      case '**D':
        $mail_sent_to = TEXT_NEWSLETTER_CUSTOMERS;
        break;
      default:
        $mail_sent_to = $_POST['customers_email_address'];
        break;
    }
?>
          <tr><?php echo HTML::form('mail', OSCOM::link(FILENAME_MAIL, 'action=send_email_to_user')); ?>
            <td><table border="0" width="100%" cellpadding="0" cellspacing="2">
              <tr>
                <td class="smallText"><strong><?php echo TEXT_CUSTOMER; ?></strong><br /><?php echo $mail_sent_to; ?></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td class="smallText"><strong><?php echo TEXT_FROM; ?></strong><br /><?php echo htmlspecialchars(stripslashes($_POST['from'])); ?></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td class="smallText"><strong><?php echo TEXT_SUBJECT; ?></strong><br /><?php echo htmlspecialchars(stripslashes($_POST['subject'])); ?></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td class="smallText"><strong><?php echo TEXT_MESSAGE; ?></strong><br /><?php echo nl2br(htmlspecialchars(stripslashes($_POST['message']))); ?></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td class="smallText" align="right">
<?php
/* Re-Post all POST'ed variables */
    foreach ( $_POST as $key => $value ) {
      if (!is_array($_POST[$key])) {
        echo HTML::hiddenField($key, htmlspecialchars(stripslashes($value)));
      }
    }

    echo HTML::button(IMAGE_SEND_EMAIL, 'fa fa-envelope', null, 'primary') . HTML::button(IMAGE_CANCEL, 'fa fa-close', OSCOM::link(FILENAME_MAIL));
?>
                </td>
              </tr>
            </table></td>
          </form></tr>
<?php
  } else {
?>
          <tr><?php echo HTML::form('mail', OSCOM::link(FILENAME_MAIL, 'action=preview')); ?>
            <td><table border="0" cellpadding="0" cellspacing="2">
<?php
    $customers = [
      [
        'id' => '',
        'text' => TEXT_SELECT_CUSTOMER
      ],
      [
        'id' => '***',
        'text' => TEXT_ALL_CUSTOMERS
      ],
      [
        'id' => '**D',
        'text' => TEXT_NEWSLETTER_CUSTOMERS
      ]
    ];

    $Qcustomers = $OSCOM_Db->get('customers', [
      'customers_email_address',
      'customers_firstname',
      'customers_lastname'
    ], null, 'customers_lastname');

    while ($Qcustomers->fetch()) {
      $customers[] = [
        'id' => $Qcustomers->value('customers_email_address'),
        'text' => $Qcustomers->value('customers_lastname') . ', ' . $Qcustomers->value('customers_firstname') . ' (' . $Qcustomers->value('customers_email_address') . ')'
      ];
    }
?>
              <tr>
                <td class="main"><?php echo TEXT_CUSTOMER; ?></td>
                <td><?php echo HTML::selectField('customers_email_address', $customers, (isset($_GET['customer']) ? $_GET['customer'] : ''));?></td>
              </tr>
              <tr>
                <td colspan="2">&nbsp;</td>
              </tr>
              <tr>
                <td class="main"><?php echo TEXT_FROM; ?></td>
                <td><?php echo HTML::inputField('from', EMAIL_FROM); ?></td>
              </tr>
              <tr>
                <td colspan="2">&nbsp;</td>
              </tr>
              <tr>
                <td class="main"><?php echo TEXT_SUBJECT; ?></td>
                <td><?php echo HTML::inputField('subject'); ?></td>
              </tr>
              <tr>
                <td colspan="2">&nbsp;</td>
              </tr>
              <tr>
                <td valign="top" class="main"><?php echo TEXT_MESSAGE; ?></td>
                <td><?php echo HTML::textareaField('message', '60', '15'); ?></td>
              </tr>
              <tr>
                <td colspan="2">&nbsp;</td>
              </tr>
              <tr>
                <td class="smallText" colspan="2" align="right"><?php echo HTML::button(IMAGE_PREVIEW, 'fa fa-file-o', null, 'primary'); ?></td>
              </tr>
            </table></td>
          </form></tr>
<?php
  }
?>
        </table></td>
      </tr>
    </table>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
