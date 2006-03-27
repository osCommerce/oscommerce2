<?php
/*
  $Id: backup.php,v 1.21 2002/06/15 11:02:56 harley_vb Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_TITLE', 'Datenbanksicherung'); 

define('TABLE_HEADING_TITLE', 'Titel');
define('TABLE_HEADING_FILE_DATE', 'Datum');
define('TABLE_HEADING_FILE_SIZE', 'Gr&ouml;sse');
define('TABLE_HEADING_ACTION', 'Aktion');

define('TEXT_INFO_HEADING_NEW_BACKUP', 'Neue Sicherung');
define('TEXT_INFO_HEADING_RESTORE_LOCAL', 'Lokal wiederherstellen');
define('TEXT_INFO_NEW_BACKUP', 'Bitte den Sicherungsprozess AUF KEINEN FALL unterbrechen. Dieser kann einige Minuten in Anspruch nehmen.');
define('TEXT_INFO_UNPACK', '<br><br>(nach dem die Dateien aus dem Archiv extrahiert wurden)');
define('TEXT_INFO_RESTORE', 'Den Wiederherstellungsprozess AUF KEINEN FALL unterbrechen.<br><br>Je gr&ouml;sser die Sicherungsdatei - desto l&auml;nger dauert die Wiederherstellung!<br><br>Bitte wenn m&ouml;glich den mysql client benutzen.<br><br>Beispiel:<br><br><b>mysql -h' . DB_SERVER . ' -u' . DB_SERVER_USERNAME . ' -p ' . DB_DATABASE . ' < %s </b> %s');
define('TEXT_INFO_RESTORE_LOCAL', 'Den Wiederherstellungsprozess AUF KEINEN FALL unterbrechen.<br><br>Je gr&ouml;sser die Sicherungsdatei - desto l&auml;nger dauert die Wiederherstellung!');
define('TEXT_INFO_RESTORE_LOCAL_RAW_FILE', 'Die Datei, welche hochgeladen wird muss eine sog. raw sql Datei sein (nur Text).');
define('TEXT_INFO_DATE', 'Datum:');
define('TEXT_INFO_SIZE', 'Gr&ouml;sse:');
define('TEXT_INFO_COMPRESSION', 'Komprimieren:');
define('TEXT_INFO_USE_GZIP', 'Mit GZIP');
define('TEXT_INFO_USE_ZIP', 'Mit ZIP');
define('TEXT_INFO_USE_NO_COMPRESSION', 'Keine Komprimierung (Raw SQL)');
define('TEXT_INFO_DOWNLOAD_ONLY', 'Nur herunterladen (nicht auf dem Server speichern)');
define('TEXT_INFO_BEST_THROUGH_HTTPS', 'Sichere HTTPS Verbindung verwenden!');
define('TEXT_NO_EXTENSION', 'Keine');
define('TEXT_BACKUP_DIRECTORY', 'Sicherungsverzeichnis:');
define('TEXT_LAST_RESTORATION', 'Letzte Wiederherstellung:');
define('TEXT_FORGET', '(<u> vergessen</u>)');
define('TEXT_DELETE_INTRO', 'Sind Sie sicher, dass Sie diese Sicherung l&ouml;schen m&ouml;chten?');

define('ERROR_BACKUP_DIRECTORY_DOES_NOT_EXIST', 'Fehler: Das Sicherungsverzeichnis ist nicht vorhanden.');
define('ERROR_BACKUP_DIRECTORY_NOT_WRITEABLE', 'Fehler: Das Sicherungsverzeichnis ist schreibgesch&uuml;tzt.');
define('ERROR_DOWNLOAD_LINK_NOT_ACCEPTABLE', 'Fehler: Download Link nicht akzeptabel.');

define('SUCCESS_LAST_RESTORE_CLEARED', 'Erfolg: Das letzte Wiederherstellungdatum wurde gel&ouml;scht.');
define('SUCCESS_DATABASE_SAVED', 'Erfolg: Die Datenbank wurde gesichert.');
define('SUCCESS_DATABASE_RESTORED', 'Erfolg: Die Datenbank wurde wiederhergestellt.');
define('SUCCESS_BACKUP_DELETED', 'Erfolg: Die Sicherungsdatei wurde gel&ouml;scht.');
?>