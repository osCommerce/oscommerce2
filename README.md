osCommerce Online Merchant v2.x
===============================

osCommerce Online Merchant is a free feature-complete self-hosted online store solution that contains both a catalog frontend and an administration tool backend which can be easily installed and configured through a web-based installation procedure. It is released under the Open Source GNU General Public License and is available for free with absolutely no software costs, license fees, or usage limitations involved.

The success of osCommerce Online Merchant is secured by a dedicated team that focus on the core features and by an active community of store owners, developers, and service providers that focus on additional features. To date, the community has provided over 7,000 Add-Ons available for free that extend on the core feature set of osCommerce Online Merchant to meet the individual requirements of store owners.

osCommerce Online Merchant is built with the powerful PHP web scriping language and uses the fast MySQL database server for the online store data. The combination of PHP and MySQL allows osCommerce Online Merchant to run on any webserver environment that supports PHP and MySQL, which includes Linux, Solaris, BSD, Mac OS X, and Microsoft Windows environments.

osCommerce started in March 2000 and has since matured to a solution that is powering many hundreds and thousands of live shops around the world.

Requirements
==============================
osCommerce Online Merchant can be installed on any web server that has PHP installed and has access to a database server. This includes shared servers, dedicated servers, cloud instances, and local installations running on Linux, Unix, BSD, Mac OS X, and Microsoft Windows operating systems.

Web Server
==============================
The web server must support PHP either as a module or allow execution of CGI scripts. For performance reasons FastCGI is recommended over CGI.

PHP
==============================
osCommerce Online Merchant is compatible with PHP 5.3. For performance and security reasons it is recommended to use the latest PHP 5 version on the web server.

The following PHP options are recommended to be set in the php.ini configuration file:

PHP Setting Value     | Value
--------------------- | -------------
register_globals      | Off
magic_quotes_gpc      | Off
file_uploads          | On
session.auto_start    | Off
session.use_trans_sid | Off

**Required PHP Extensions**
- Mysqli

**Recommended PHP Extensions**
- GD	
- cURL	
- OpenSSL

MySQL Database Server
==============================
The minimum MySQL version required is v3.23. It is recommended to use the latest MySQL 4 or MySQL 5 version on the database server.

The following MySQL storage engines are supported:
- MyISAM

**Website:** http://www.oscommerce.com

**Support Forums:** http://forums.oscommerce.com

**Documentation:** http://library.oscommerce.com/
