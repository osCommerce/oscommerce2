<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2006 - 2007 Henri Schmidhuber (http://www.in-solution.de)
  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

  if (!defined('MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_STATUS') && function_exists('tep_catalog_href_link')) {  // we are in admin and module not installed -> show autoinstaller
    define('MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_DESCRIPTION', '<div align="center"><a href=' . tep_href_link('sofortueberweisung_install.php', 'install=sofortueberweisungredirect', 'SSL') . '>' . tep_image(DIR_WS_IMAGES . 'icons/sofortueberweisung_autoinstaller.gif', 'Autoinstaller (empfohlen)') . '</a><br><b>Direktes Bezahlen mit Sofortüberweisung.</b><br><br><small>Der Kunde wird vor Abschluss des Bestellvorgangs zur Sofortüberweisungseite geleitet. Mit Abschluss der Zahlung wird die Bestellung in die Shopdatenbank geschrieben. Bricht der Kunde ab kommt er zurück zur Zahlungsausswahlseite des Shops.<br><b>Hinweis zu diesem Modul:</b><br>Schliest der Kunde bei Sofortüberweisung den Browser, bzw. scheitert der Rücksprung wird keine Bestellung im Shop aufgenommen.</small><br><b>Bei gleichzeitiger Verwendung mit einem der anderen Sofortüberweisungsmodule muß ein eigenes Projekt bei Sofortüberweisung angelegt werden.</b></div>');
  } else {
    define('MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_DESCRIPTION', '<b>Direktes Bezahlen mit Sofortüberweisung.</b><br><br><small>Der Kunde wird vor Abschluss des Bestellvorgangs zur Sofortüberweisungseite geleitet. Mit Abschluss der Zahlung wird die Bestellung in die Shopdatenbank geschrieben. Bricht der Kunde ab kommt er zurück zur Zahlungsausswahlseite des Shops.<br><b>Hinweis zu diesem Modul:</b><br>Schliest der Kunde bei Sofortüberweisung den Browser, bzw. scheitert der Rücksprung wird keine Bestellung im Shop aufgenommen.</small><br><b>Bei gleichzeitiger Verwendung mit einem der anderen Sofortüberweisungsmodule muß ein eigenes Projekt bei Sofortüberweisung angelegt werden.</b>');
  }

  define('MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_TITLE', 'Direktes Bezahlen mit Sofortüberweisung (empfohlen)');

  // checkout_payment Informationen via Bild
  define('MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_DESCRIPTION_CHECKOUT_PAYMENT', '
    <table border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td><a href="#" onclick="window.open(\'https://www.sofort-ueberweisung.de/paynetag/anbieter/download/informationen.html\', \'Popup\',\'toolbar=yes,status=no,menubar=no,scrollbars=yes,width=690,height=500\'); return false;">' . tep_image(DIR_WS_LANGUAGES . $language . '/images/buttons/' . 'sofortueberweisung.gif', 'Sofortüberweisung ist der kostenlose, TÜV-zertifizierte Zahlungsdienst der Payment Network AG. Ihre Vorteile: keine zusätzliche Registrierung, automatische Abbuchung von Ihrem Online-Bankkonto, höchste Sicherheitsstandards und sofortiger Versand von Lagerware. Für die Bezahlung mit Sofortüberweisung benötigen Sie Ihre eBanking Zugangsdaten, d.h. Bankverbindung, Kontonummer, PIN und TAN.') . '</a></td>
      </tr>
    </table>');

  define('MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_DESCRIPTION_CHECKOUT_CONFIRMATION', '
    <table border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="main">Sofortüberweisung ist der kostenlose, <a href="#" onclick="window.open(\'https://www.sofortueberweisung.de/cms/index.php?plink=tuev-zertifikat&alink=sicherheit&fs=&l=0\', \'Popup\',\'toolbar=yes,status=no,menubar=no,scrollbars=yes,width=690,height=500\'); return false;">TÜV-zertifizierte</a> Zahlungsdienst der Payment Network AG. Ihre Vorteile: keine zusätzliche Registrierung, automatische Abbuchung von Ihrem Online-Bankkonto, höchste Sicherheitsstandards und sofortiger Versand von Lagerware. Für die Bezahlung mit Sofortüberweisung benötigen Sie Ihre eBanking Zugangsdaten, d.h. Bankverbindung, Kontonummer, PIN und TAN. Mehr Informationen finden Sie hier: <a href="#" onclick="window.open(\'https://www.sofort-ueberweisung.de/paynetag/anbieter/download/informationen.html\', \'Popup\',\'toolbar=yes,status=no,menubar=no,scrollbars=yes,width=690,height=500\'); return false;">www.sofortueberweisung.de</a>.</td>
      </tr>
    </table>');

 // im Verwendungszweck werden folgende Platzhalter ersetzt:
 // {{order_date}} durch Bestelldatum
 // {{customer_id}} durch Kundennummer der Datenbank
 // {{customer_name}}  durch Kundenname
 // {{customer_company}} durch Kundenfirma
 // {{customer_email}} durch Email des Kunden

  define('MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_V_ZWECK_1', 'Bestellung bei ' . STORE_NAME);  // max 27 Zeichen
  define('MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_V_ZWECK_2', 'Kd-Nr. {{customer_id}} {{customer_name}}'); // max 27 Zeichen
  define('MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_EMAIL_FOOTER', '');
  define('MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_REDIRECT', 'Sie werden nun zu Sofortueberweisung.de weitergeleitet. Sollte dies nicht geschehen bitte den Button drücken');

  define('MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_ERROR_HEADING', 'Folgender Fehler wurde von Sofortüberweisung während des Prozesses gemeldet:');
  define('MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_ERROR_MESSAGE', 'Zahlung via Sofortüberweisung ist leider nicht möglich, oder wurde auf Kundenwunsch abgebrochen. Bitte wählen sie ein andere Zahlungsweise.');
  define('MODULE_PAYMENT_SOFORTUEBERWEISUNG_DIRECT_TEXT_CHECK_ERROR', 'Sofortüberweisungs Transaktionscheck fehlgeschlagen. Bitte manuell überprüfen');
?>