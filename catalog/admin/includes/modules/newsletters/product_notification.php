<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  class product_notification {
    var $show_choose_audience, $title, $content;

    function product_notification($title, $content) {
      $this->show_choose_audience = true;
      $this->title = $title;
      $this->content = $content;
    }

    function choose_audience() {
      global $HTTP_GET_VARS, $languages_id;

      $products_array = array();
      $products_query = tep_db_query("select pd.products_id, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.language_id = '" . $languages_id . "' and pd.products_id = p.products_id and p.products_status = '1' order by pd.products_name");
      while ($products = tep_db_fetch_array($products_query)) {
        $products_array[] = array('id' => $products['products_id'],
                                  'text' => $products['products_name']);
      }

$choose_audience_string = '<script type="text/javascript"><!--
function mover(move) {
  if (move == \'remove\') {
    for (x=0; x<(document.notifications.products.length); x++) {
      if (document.notifications.products.options[x].selected) {
        with(document.notifications.elements[\'chosen[]\']) {
          options[options.length] = new Option(document.notifications.products.options[x].text,document.notifications.products.options[x].value);
        }
        document.notifications.products.options[x] = null;
        x = -1;
      }
    }
  }
  if (move == \'add\') {
    for (x=0; x<(document.notifications.elements[\'chosen[]\'].length); x++) {
      if (document.notifications.elements[\'chosen[]\'].options[x].selected) {
        with(document.notifications.products) {
          options[options.length] = new Option(document.notifications.elements[\'chosen[]\'].options[x].text,document.notifications.elements[\'chosen[]\'].options[x].value);
        }
        document.notifications.elements[\'chosen[]\'].options[x] = null;
        x = -1;
      }
    }
  }
  return true;
}

function selectAll(FormName, SelectBox) {
  temp = "document." + FormName + ".elements[\'" + SelectBox + "\']";
  Source = eval(temp);

  for (x=0; x<(Source.length); x++) {
    Source.options[x].selected = "true";
  }

  if (x<1) {
    alert(\'' . JS_PLEASE_SELECT_PRODUCTS . '\');
    return false;
  } else {
    return true;
  }
}
//--></script>';

      $global_button = tep_draw_button(BUTTON_GLOBAL, 'circle-triangle-n', tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID'] . '&action=confirm&global=true'), 'primary');

      $cancel_button = tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID']));

      $choose_audience_string .= '<form name="notifications" action="' . tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID'] . '&action=confirm') . '" method="post" onsubmit="return selectAll(\'notifications\', \'chosen[]\')"><table border="0" width="100%" cellspacing="0" cellpadding="2">' . "\n" .
                                 '  <tr>' . "\n" .
                                 '    <td align="center" class="smallText"><strong>' . TEXT_PRODUCTS . '</strong><br />' . tep_draw_pull_down_menu('products', $products_array, '', 'size="20" style="width: 20em;" multiple') . '</td>' . "\n" .
                                 '    <td align="center" class="smallText">&nbsp;<br />' . $global_button . '<br /><br /><br /><input type="button" value="' . BUTTON_SELECT . '" style="width: 8em;" onClick="mover(\'remove\');"><br /><br /><input type="button" value="' . BUTTON_UNSELECT . '" style="width: 8em;" onClick="mover(\'add\');"><br /><br /><br />' . tep_draw_button(IMAGE_SEND, 'mail-closed', null, 'primary') . '<br /><br />' . $cancel_button . '</td>' . "\n" .
                                 '    <td align="center" class="smallText"><strong>' . TEXT_SELECTED_PRODUCTS . '</strong><br />' . tep_draw_pull_down_menu('chosen[]', array(), '', 'size="20" style="width: 20em;" multiple') . '</td>' . "\n" .
                                 '  </tr>' . "\n" .
                                 '</table></form>';

      return $choose_audience_string;
    }

    function confirm() {
      global $HTTP_GET_VARS, $HTTP_POST_VARS;

      $audience = array();

      if (isset($HTTP_GET_VARS['global']) && ($HTTP_GET_VARS['global'] == 'true')) {
        $products_query = tep_db_query("select distinct customers_id from " . TABLE_PRODUCTS_NOTIFICATIONS);
        while ($products = tep_db_fetch_array($products_query)) {
          $audience[$products['customers_id']] = '1';
        }

        $customers_query = tep_db_query("select customers_info_id from " . TABLE_CUSTOMERS_INFO . " where global_product_notifications = '1'");
        while ($customers = tep_db_fetch_array($customers_query)) {
          $audience[$customers['customers_info_id']] = '1';
        }
      } else {
        $chosen = $HTTP_POST_VARS['chosen'];

        $ids = implode(',', $chosen);

        $products_query = tep_db_query("select distinct customers_id from " . TABLE_PRODUCTS_NOTIFICATIONS . " where products_id in (" . $ids . ")");
        while ($products = tep_db_fetch_array($products_query)) {
          $audience[$products['customers_id']] = '1';
        }

        $customers_query = tep_db_query("select customers_info_id from " . TABLE_CUSTOMERS_INFO . " where global_product_notifications = '1'");
        while ($customers = tep_db_fetch_array($customers_query)) {
          $audience[$customers['customers_info_id']] = '1';
        }
      }

      $confirm_string = '<table border="0" cellspacing="0" cellpadding="2">' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td class="main"><font color="#ff0000"><strong>' . sprintf(TEXT_COUNT_CUSTOMERS, sizeof($audience)) . '</strong></font></td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td>' . tep_draw_separator('pixel_trans.gif', '1', '10') . '</td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td class="main"><strong>' . $this->title . '</strong></td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td>' . tep_draw_separator('pixel_trans.gif', '1', '10') . '</td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td class="main"><tt>' . nl2br($this->content) . '</tt></td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . "\n" .
                        '    <td>' . tep_draw_separator('pixel_trans.gif', '1', '10') . '</td>' . "\n" .
                        '  </tr>' . "\n" .
                        '  <tr>' . tep_draw_form('confirm', FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID'] . '&action=confirm_send') . "\n" .
                        '    <td class="smallText" align="right">';
      if (sizeof($audience) > 0) {
        if (isset($HTTP_GET_VARS['global']) && ($HTTP_GET_VARS['global'] == 'true')) {
          $confirm_string .= tep_draw_hidden_field('global', 'true');
        } else {
          for ($i = 0, $n = sizeof($chosen); $i < $n; $i++) {
            $confirm_string .= tep_draw_hidden_field('chosen[]', $chosen[$i]);
          }
        }
        $confirm_string .= tep_draw_button(IMAGE_SEND, 'mail-closed', null, 'primary');
      }
      $confirm_string .= tep_draw_button(IMAGE_CANCEL, 'close', tep_href_link(FILENAME_NEWSLETTERS, 'page=' . $HTTP_GET_VARS['page'] . '&nID=' . $HTTP_GET_VARS['nID'] . '&action=send')) . '</td>' . "\n" .
                         '  </tr>' . "\n" .
                         '</table>';

      return $confirm_string;
    }

    function send($newsletter_id) {
      global $HTTP_POST_VARS;

      $audience = array();

      if (isset($HTTP_POST_VARS['global']) && ($HTTP_POST_VARS['global'] == 'true')) {
        $products_query = tep_db_query("select distinct pn.customers_id, c.customers_firstname, c.customers_lastname, c.customers_email_address from " . TABLE_CUSTOMERS . " c, " . TABLE_PRODUCTS_NOTIFICATIONS . " pn where c.customers_id = pn.customers_id");
        while ($products = tep_db_fetch_array($products_query)) {
          $audience[$products['customers_id']] = array('firstname' => $products['customers_firstname'],
                                                       'lastname' => $products['customers_lastname'],
                                                       'email_address' => $products['customers_email_address']);
        }

        $customers_query = tep_db_query("select c.customers_id, c.customers_firstname, c.customers_lastname, c.customers_email_address from " . TABLE_CUSTOMERS . " c, " . TABLE_CUSTOMERS_INFO . " ci where c.customers_id = ci.customers_info_id and ci.global_product_notifications = '1'");
        while ($customers = tep_db_fetch_array($customers_query)) {
          $audience[$customers['customers_id']] = array('firstname' => $customers['customers_firstname'],
                                                        'lastname' => $customers['customers_lastname'],
                                                        'email_address' => $customers['customers_email_address']);
        }
      } else {
        $chosen = $HTTP_POST_VARS['chosen'];

        $ids = implode(',', $chosen);

        $products_query = tep_db_query("select distinct pn.customers_id, c.customers_firstname, c.customers_lastname, c.customers_email_address from " . TABLE_CUSTOMERS . " c, " . TABLE_PRODUCTS_NOTIFICATIONS . " pn where c.customers_id = pn.customers_id and pn.products_id in (" . $ids . ")");
        while ($products = tep_db_fetch_array($products_query)) {
          $audience[$products['customers_id']] = array('firstname' => $products['customers_firstname'],
                                                       'lastname' => $products['customers_lastname'],
                                                       'email_address' => $products['customers_email_address']);
        }

        $customers_query = tep_db_query("select c.customers_id, c.customers_firstname, c.customers_lastname, c.customers_email_address from " . TABLE_CUSTOMERS . " c, " . TABLE_CUSTOMERS_INFO . " ci where c.customers_id = ci.customers_info_id and ci.global_product_notifications = '1'");
        while ($customers = tep_db_fetch_array($customers_query)) {
          $audience[$customers['customers_id']] = array('firstname' => $customers['customers_firstname'],
                                                        'lastname' => $customers['customers_lastname'],
                                                        'email_address' => $customers['customers_email_address']);
        }
      }

      $mimemessage = new email(array('X-Mailer: osCommerce'));

      // Build the text version
      $text = strip_tags($this->content);
      if (EMAIL_USE_HTML == 'true') {
        $mimemessage->add_html($this->content, $text);
      } else {
        $mimemessage->add_text($text);
      }

      $mimemessage->build_message();

      reset($audience);
      while (list($key, $value) = each ($audience)) {
        $mimemessage->send($value['firstname'] . ' ' . $value['lastname'], $value['email_address'], '', EMAIL_FROM, $this->title);
      }

      $newsletter_id = tep_db_prepare_input($newsletter_id);
      tep_db_query("update " . TABLE_NEWSLETTERS . " set date_sent = now(), status = '1' where newsletters_id = '" . tep_db_input($newsletter_id) . "'");
    }
  }
?>
