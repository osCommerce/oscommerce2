<?php
/*
  $Id: checkout_success.php,v 1.17 2003/02/16 00:42:03 harley_vb Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

define('NAVBAR_TITLE_1', 'Kasse');
define('NAVBAR_TITLE_2', 'Erfolg');

define('HEADING_TITLE', 'Ihr Bestellung ist ausgef&uuml;hrt worden.');

define('TEXT_SUCCESS', 'Ihre Bestellung ist eingegangen und wird bearbeitet! Die Lieferung erfolgt innerhalb von ca. 2-5 Werktagen.');
define('TEXT_NOTIFY_PRODUCTS', 'Bitte benachrichtigen Sie mich &uuml;ber Aktuelles zu folgenden Produkten:');
define('TEXT_SEE_ORDERS', 'Sie k&ouml;nnen Ihre Bestellung(en) auf der Seite <a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '"><u>\'Ihr Konto\'</a></u> jederzeit einsehen und sich dort auch Ihre <a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') . '"><u>\'Bestell&uuml;bersicht\'</u></a> anzeigen lassen.');
define('TEXT_CONTACT_STORE_OWNER', 'Falls Sie Fragen bez&uuml;glich Ihrer Bestellung haben, wenden Sie sich an unseren <a href="' . tep_href_link(FILENAME_CONTACT_US) . '"><u>Vertrieb</u></a>.');
define('TEXT_THANKS_FOR_SHOPPING', 'Wir danken Ihnen f&uuml;r Ihren Online-Einkauf!');

define('TABLE_HEADING_DOWNLOAD_DATE', 'herunterladen m&ouml;glich bis:');
define('TABLE_HEADING_DOWNLOAD_COUNT', 'max. Anz. Downloads');
define('HEADING_DOWNLOAD', 'Artikel herunterladen:');
define('FOOTER_DOWNLOAD', 'Sie k&ouml;nnen Ihre Artikel auch sp&auml;ter unter \'%s\' herunterladen');
?>