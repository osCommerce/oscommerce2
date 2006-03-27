<?php
/*
  $Id: currencies.php,v 1.16 2003/06/25 20:36:48 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_TITLE', 'W&auml;hrungen');

define('TABLE_HEADING_CURRENCY_NAME', 'W&auml;hrung');
define('TABLE_HEADING_CURRENCY_CODES', 'K&uuml;rzel');
define('TABLE_HEADING_CURRENCY_VALUE', 'Wert');
define('TABLE_HEADING_ACTION', 'Aktion');

define('TEXT_INFO_EDIT_INTRO', 'Bitte f&uuml;hren Sie alle notwendigen &Auml;nderungen durch');
define('TEXT_INFO_CURRENCY_TITLE', 'Name:');
define('TEXT_INFO_CURRENCY_CODE', 'K&uuml;rzel:');
define('TEXT_INFO_CURRENCY_SYMBOL_LEFT', 'Symbol Links:');
define('TEXT_INFO_CURRENCY_SYMBOL_RIGHT', 'Symbol Rechts:');
define('TEXT_INFO_CURRENCY_DECIMAL_POINT', 'Dezimalkomma:');
define('TEXT_INFO_CURRENCY_THOUSANDS_POINT', 'Tausenderpunkt:');
define('TEXT_INFO_CURRENCY_DECIMAL_PLACES', 'Dezimalstellen:');
define('TEXT_INFO_CURRENCY_LAST_UPDATED', 'letzte &Auml;nderung:');
define('TEXT_INFO_CURRENCY_VALUE', 'Wert:');
define('TEXT_INFO_CURRENCY_EXAMPLE', 'Beispiel:');
define('TEXT_INFO_INSERT_INTRO', 'Bitte geben Sie die neue W&auml;hrung mit allen relevanten Daten ein');
define('TEXT_INFO_DELETE_INTRO', 'Sind Sie sicher, dass Sie diese W&auml;hrung l&ouml;schen m&ouml;chten?');
define('TEXT_INFO_HEADING_NEW_CURRENCY', 'neue W&auml;hrung');
define('TEXT_INFO_HEADING_EDIT_CURRENCY', 'W&auml;hrung bearbeiten');
define('TEXT_INFO_HEADING_DELETE_CURRENCY', 'W&auml;hrung l&ouml;schen');
define('TEXT_INFO_SET_AS_DEFAULT', TEXT_SET_DEFAULT . ' (manuelles Aktualisieren der Wechselkurse erforderlich.)');
define('TEXT_INFO_CURRENCY_UPDATED', 'The exchange rate for %s (%s) was updated successfully via %s.');

define('ERROR_REMOVE_DEFAULT_CURRENCY', 'Fehler: Die Standardw&auml;hrung darf nicht gel&ouml;scht werden. Bitte definieren Sie eine neue Standardw&auml;hrung und wiederholen Sie den Vorgang.');
define('ERROR_CURRENCY_INVALID', 'Error: The exchange rate for %s (%s) was not updated via %s. Is it a valid currency code?');
define('WARNING_PRIMARY_SERVER_FAILED', 'Warning: The primary exchange rate server (%s) failed for %s (%s) - trying the secondary exchange rate server.');
?>