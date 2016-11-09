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
  use OSC\OM\Registry;

  class newsletter {
    var $show_choose_audience, $title, $content;

    function newsletter($title, $content) {
      $this->show_choose_audience = false;
      $this->title = $title;
      $this->content = $content;
    }

    function choose_audience() {
      return false;
    }

    function confirm() {
      $OSCOM_Db = Registry::get('Db');

      $Qmail = $OSCOM_Db->get('customers', 'count(*) as count', ['customers_newsletter' => '1']);

      $confirm_string = '<table border="0" cellspacing="0" cellpadding="2">' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td class="main"><font color="#ff0000"><strong>' . OSCOM::getDef('text_count_customers', ['count' => $Qmail->valueInt('count')]) . '</strong></font></td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td>&nbsp;</td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td class="main"><strong>' . $this->title . '</strong></td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td>&nbsp;</td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td class="main"><tt>' . nl2br($this->content) . '</tt></td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td>&nbsp;</td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td class="smallText" align="right">' . HTML::button(OSCOM::getDef('image_send'), 'fa fa-envelope', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'] . '&action=confirm_send')) . HTML::button(OSCOM::getDef('image_cancel'), 'fa fa-close', OSCOM::link(FILENAME_NEWSLETTERS, 'page=' . $_GET['page'] . '&nID=' . $_GET['nID'])) . '</td>' . "\n" .
                        '  </tr>' . "\n" .
                        '</table>';

      return $confirm_string;
    }

    function send($newsletter_id) {
      $OSCOM_Db = Registry::get('Db');

      $newsletterEmail = new Mail();
      $newsletterEmail->setFrom(STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER);
      $newsletterEmail->setSubject($this->title);
      $newsletterEmail->setBody($this->content);

      $Qmail = $OSCOM_Db->get('customers', [
        'customers_firstname',
        'customers_lastname',
        'customers_email_address'
      ], [
        'customers_newsletter' => '1'
      ]);

      while ($Qmail->fetch()) {
        $newsletterEmail->clearTo();

        $newsletterEmail->addTo($Qmail->value('customers_email_address'), $Qmail->value('customers_firstname') . ' ' . $Qmail->value('customers_lastname'));

        $newsletterEmail->send();
      }

      $OSCOM_Db->save('newsletters', [
        'date_sent' => 'now()',
        'status' => '1'
      ], [
        'newsletters_id' => (int)$newsletter_id
      ]);
    }
  }
?>
