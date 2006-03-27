<?php
/*
  $Id: index.php,v 1.2 2003/07/11 09:04:22 jan0815 Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

define('TEXT_MAIN', 'Dies ist eine Standardinstallation von osCommerce. Alle hier gezeigten Produkte sind fiktiv zu verstehen. <b>Eine hier get&auml;tigte Bestellung wird NICHT ausgef&uuml;hrt werden, Sie erhalten keine Lieferung oder Rechnung.</b><br><br><table border="0" width="100%" cellspacing="5" cellpadding="2"><tr><td class="main" valign="top">' . tep_image(DIR_WS_IMAGES . 'default/1.gif') . '</td><td class="main" valign="top"><b>Fehlermeldungen</b><br><br>Sollten sich im oberen Bildschirmbereich Fehler oder Warnungen befinden, korrigieren Sie diese bitte entsprechend den dort gegebenen Hinweisen.<br><br>Fehlermeldungen sind durch eine entsprechende <span class="messageStackError">Hintergrundfarbe</span> gekennzeichnet.<br><br>Es werden standardm&auml;ssig verschiedene &Uuml;berpr&uuml;fungen durchgef&uuml;hrt. Sie k&ouml;nnen diese Checks durch &Auml;nderungen in der Datei includes/application_top.php abschalten.</td></tr><td class="main" valign="top">' . tep_image(DIR_WS_IMAGES . 'default/2.gif') . '</td><td class="main" valign="top"><b>Seiteninhalte bearbeiten</b><br><br>Dieser Text befindet sich im jeweiligen Sprachverzeichnis in folgender Datei: <br><br><nobr class="messageStackSuccess">[Pfad zu catalog]/includes/languages/' . $language . '/' . FILENAME_DEFAULT . '</nobr><br><br>Sie k&ouml;nnen diese Datei entweder direkt mit einem Editor bearbeiten, oder im Administrations-Modus &uuml;ber <nobr class="messageStackSuccess">Languages->' . ucfirst($language) . '->Sprachen</nobr> oder &uuml;ber <nobr class="messageStackSuccess">Hilfsprogramme->Datei-Manager</nobr>.<br><br>Der Text wird folgendermassen gesetzt: <br><br><nobr>define(\'TEXT_MAIN\', \'<span class="messageStackSuccess">Dies ist eine Standardinstallation von osCommerce ...</span>\');</nobr><br><br>Der gr&uuml;n hinterlegte Teil soll dabei ge&auml;ndert werden - wicthig ist, dass Sie auf keinen Fall den define()-Befehl f&uml;r TEXT_MAIN editieren. Um den kompletten Text zu entfernen, setzen Sie TEXT_MAIN folgendermassen: <br><br><nobr>define(\'TEXT_MAIN\', \'\');</nobr><br><br>Mehr Informationen &uuml;ber diesen Befehl erhalten Sie <a href="http://www.php.net/define" target="_blank"><u>hier</u></a>.</td></tr><tr><td class="main" valign="top">' . tep_image(DIR_WS_IMAGES . 'default/3.gif') . '</td><td class="main" valign="top"><b>Das Administrationswerkzeug sichern</b><br><br>Nach einer Standardinstallation von osCommerce ist das Administrationswerkzeug NICHT gesch&uuml;tzt. Es ist daher sehr wichtig diesen Teil von osCommerce abzusichern.</td></tr><tr><td class="main" valign="top">' . tep_image(DIR_WS_IMAGES . 'default/4.gif') . '</td><td class="main" valign="top"><b>Online-Handbuch</b><br><br>Unser Online-Handbuch befindet sich unter <a href="http://de.oscommerce.info" target="_blank"><u>osCommerce Knowledge Base</u></a>.<br><br>Unsere deutschsprachige Support Community finden Sie unter <a href="http://forums.oscommerce.de" target="_blank"><u>osCommerce German Community Support Forums</u></a>.</td></tr></table><br>Wenn Sie dass System hinter diesem Shop downloaden wollen, oder wenn Sie L&ouml;sungen zu osCommerce beitragen wollen, besuchen Sie uns bitte unter <a href="http://www.oscommerce.com" target="_blank"><u>der Support Site von osCommerce</u></a>. Dieser Shop verwendet osCommerce in der Version <font color="#f0000"><b>' . PROJECT_VERSION . '</b></font>.');
define('TABLE_HEADING_NEW_PRODUCTS', 'Neue Produkte im %s');
define('TABLE_HEADING_UPCOMING_PRODUCTS', 'Wann ist was verf&uuml;gbar');
define('TABLE_HEADING_DATE_EXPECTED', 'Datum');

if ( ($category_depth == 'products') || (isset($HTTP_GET_VARS['manufacturers_id'])) ) {
  define('HEADING_TITLE', 'Unser Angebot');
  define('TABLE_HEADING_IMAGE', '');
  define('TABLE_HEADING_MODEL', 'Artikel-Nr.');
  define('TABLE_HEADING_PRODUCTS', 'Produkte');
  define('TABLE_HEADING_MANUFACTURER', 'Hersteller');
  define('TABLE_HEADING_QUANTITY', 'Anzahl');
  define('TABLE_HEADING_PRICE', 'Preis');
  define('TABLE_HEADING_WEIGHT', 'Gewicht');
  define('TABLE_HEADING_BUY_NOW', 'Bestellen');
  define('TEXT_NO_PRODUCTS', 'Es gibt keine Produkte in dieser Kategorie.');
  define('TEXT_NO_PRODUCTS2', 'Es gibt kein Produkt, das von diesem Hersteller stammt.');
  define('TEXT_NUMBER_OF_PRODUCTS', 'Artikel: ');
  define('TEXT_SHOW', '<b>Darstellen:</b>');
  define('TEXT_BUY', '1 x \'');
  define('TEXT_NOW', '\' bestellen!');
  define('TEXT_ALL_CATEGORIES', 'Alle Kategorien');
  define('TEXT_ALL_MANUFACTURERS', 'Alle Hersteller');
} elseif ($category_depth == 'top') {
  define('HEADING_TITLE', 'Unser Angebot');
} elseif ($category_depth == 'nested') {
  define('HEADING_TITLE', 'Kategorien');
}
?>
