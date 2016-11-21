<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\HTML;
  use OSC\OM\Mail;
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

        $mail_sent_to = OSCOM::getDef('text_all_customers');
        break;
      case '**D':
        $Qmail = $OSCOM_Db->get('customers', [
          'customers_firstname',
          'customers_lastname',
          'customers_email_address'
        ], [
          'customers_newsletter' => '1'
        ]);

        $mail_sent_to = OSCOM::getDef('text_newsletter_customers');
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

    $customerEmail = new Mail();
    $customerEmail->setFrom($_POST['from']);
    $customerEmail->setSubject($_POST['subject']);

    if (!empty($_POST['message'])) {
      $customerEmail->setBodyPlain($_POST['message']);
    }

    if (!empty($_POST['message_html'])) {
      $customerEmail->setBodyHTML($_POST['message_html']);
    }

    while ($Qmail->fetch()) {
      $customerEmail->clearTo();

      $customerEmail->addTo($Qmail->value('customers_email_address'), $Qmail->value('customers_firstname') . ' ' . $Qmail->value('customers_lastname'));

      $customerEmail->send();
    }

    OSCOM::redirect(FILENAME_MAIL, 'mail_sent_to=' . urlencode($mail_sent_to));
  }

  if ( ($action == 'preview') && !isset($_POST['customers_email_address']) ) {
    $OSCOM_MessageStack->add(OSCOM::getDef('error_no_customer_selected'), 'error');
  }

  if (isset($_GET['mail_sent_to'])) {
    $OSCOM_MessageStack->add(OSCOM::getDef('notice_email_sent_to', ['mail_sent_to' => $_GET['mail_sent_to']]), 'success');
  }

  require($oscTemplate->getFile('template_top.php'));
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo OSCOM::getDef('heading_title'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  if ( ($action == 'preview') && isset($_POST['customers_email_address']) ) {
    switch ($_POST['customers_email_address']) {
      case '***':
        $mail_sent_to = OSCOM::getDef('text_all_customers');
        break;
      case '**D':
        $mail_sent_to = OSCOM::getDef('text_newsletter_customers');
        break;
      default:
        $mail_sent_to = $_POST['customers_email_address'];
        break;
    }
?>
          <tr><?php echo HTML::form('mail', OSCOM::link(FILENAME_MAIL, 'action=send_email_to_user')); ?>
            <td><table border="0" width="100%" cellpadding="0" cellspacing="2">
              <tr>
                <td class="smallText"><strong><?php echo OSCOM::getDef('text_customer'); ?></strong><br /><?php echo HTML::outputProtected($mail_sent_to); ?></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td class="smallText"><strong><?php echo OSCOM::getDef('text_from'); ?></strong><br /><?php echo HTML::outputProtected($_POST['from']); ?></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td class="smallText"><strong><?php echo OSCOM::getDef('text_subject'); ?></strong><br /><?php echo HTML::outputProtected($_POST['subject']); ?></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td class="smallText">
                  <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#html_preview" aria-controls="html_preview" role="tab" data-toggle="tab"><?= OSCOM::getDef('email_type_html'); ?></a></li>
                    <li role="presentation"><a href="#plain_preview" aria-controls="plain_preview" role="tab" data-toggle="tab"><?= OSCOM::getDef('email_type_plain'); ?></a></li>
                  </ul>

                  <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="html_preview">
                      <iframe id="emailHtmlPreviewContent" style="width: 100%; height: 400px; border: 0;"></iframe>

                      <script id="emailHtmlPreview" type="x-tmpl-mustache">
                        <?= HTML::outputProtected($_POST['message_html']); ?>
                      </script>

                      <script>
                        $(function() {
                          var content = $('<div />').html($('#emailHtmlPreview').html()).text();
                          $('#emailHtmlPreviewContent').contents().find('html').html(content);
                        });
                      </script>
                    </div>

                    <div role="tabpanel" class="tab-pane" id="plain_preview">
                      <?= nl2br(HTML::outputProtected($_POST['message'])); ?>
                    </div>
                  </div>
                </td>
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
        echo HTML::hiddenField($key, $value);
      }
    }

    echo HTML::button(OSCOM::getDef('image_send_email'), 'fa fa-envelope') . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_MAIL));
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
        'text' => OSCOM::getDef('text_select_customer')
      ],
      [
        'id' => '***',
        'text' => OSCOM::getDef('text_all_customers')
      ],
      [
        'id' => '**D',
        'text' => OSCOM::getDef('text_newsletter_customers')
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
                <td class="main"><?php echo OSCOM::getDef('text_customer'); ?></td>
                <td><?php echo HTML::selectField('customers_email_address', $customers, (isset($_GET['customer']) ? $_GET['customer'] : ''));?></td>
              </tr>
              <tr>
                <td colspan="2">&nbsp;</td>
              </tr>
              <tr>
                <td class="main"><?php echo OSCOM::getDef('text_from'); ?></td>
                <td><?php echo HTML::inputField('from', EMAIL_FROM); ?></td>
              </tr>
              <tr>
                <td colspan="2">&nbsp;</td>
              </tr>
              <tr>
                <td class="main"><?php echo OSCOM::getDef('text_subject'); ?></td>
                <td><?php echo HTML::inputField('subject'); ?></td>
              </tr>
              <tr>
                <td colspan="2">&nbsp;</td>
              </tr>
              <tr>
                <td valign="top" class="main"><?php echo OSCOM::getDef('text_message'); ?></td>
                <td>
                  <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#html_email" aria-controls="html_email" role="tab" data-toggle="tab"><?= OSCOM::getDef('email_type_html'); ?></a></li>
                    <li role="presentation"><a href="#plain_email" aria-controls="plain_email" role="tab" data-toggle="tab"><?= OSCOM::getDef('email_type_plain'); ?></a></li>
                  </ul>

                  <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="html_email">
                      <?= HTML::textareaField('message_html', '60', '15'); ?>
                    </div>

                    <div role="tabpanel" class="tab-pane" id="plain_email">
                      <?= HTML::textareaField('message', '60', '15'); ?>
                    </div>
                  </div>
                </td>
              </tr>
              <tr>
                <td colspan="2">&nbsp;</td>
              </tr>
              <tr>
                <td class="smallText" colspan="2" align="right"><?php echo HTML::button(OSCOM::getDef('image_preview'), 'fa fa-file-o'); ?></td>
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
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
