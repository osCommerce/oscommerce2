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
    var $show_choose_audience, $title, $content, $content_html;

    function __construct($title, $content, $content_html = null) {
      $this->show_choose_audience = false;
      $this->title = $title;
      $this->content = $content;
      $this->content_html = $content_html;
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
                        '    <td class="main">' . "\n" .
                        '      <ul class="nav nav-tabs" role="tablist">' . "\n" .
                        '        <li role="presentation" class="active"><a href="#html_preview" aria-controls="html_preview" role="tab" data-toggle="tab">' . OSCOM::getDef('email_type_html') . '</a></li>' . "\n" .
                        '        <li role="presentation"><a href="#plain_preview" aria-controls="plain_preview" role="tab" data-toggle="tab">' . OSCOM::getDef('email_type_plain') . '</a></li>' . "\n" .
                        '      </ul>' . "\n" .
                        '      <div class="tab-content">' . "\n" .
                        '        <div role="tabpanel" class="tab-pane active" id="html_preview">' . "\n" .
                        '          <iframe id="emailHtmlPreviewContent" style="width: 100%; height: 400px; border: 0;"></iframe>' . "\n" .
                        '          <script id="emailHtmlPreview" type="x-tmpl-mustache">' . "\n" .
                        '            ' . HTML::outputProtected($this->content_html) . "\n" .
                        '          </script>' . "\n" .
                        '          <script>' . "\n" .
                        '            $(function() {' . "\n" .
                        '              var content = $(\'<div />\').html($(\'#emailHtmlPreview\').html()).text();' . "\n" .
                        '              $(\'#emailHtmlPreviewContent\').contents().find(\'html\').html(content);' . "\n" .
                        '            });' . "\n" .
                        '          </script>' . "\n" .
                        '        </div>' . "\n" .
                        '        <div role="tabpanel" class="tab-pane" id="plain_preview">' . "\n" .
                        '          ' . nl2br(HTML::outputProtected($this->content)) . "\n" .
                        '        </div>' . "\n" .
                        '      </div>' . "\n" .
                        '    </td>' . "\n" .
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

      if (!empty($this->content)) {
        $newsletterEmail->setBodyPlain($this->content);
      }

      if (!empty($this->content_html)) {
        $newsletterEmail->setBodyHTML($this->content_html);
      }

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
