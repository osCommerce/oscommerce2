<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  $www_location = 'http://' . $HTTP_SERVER_VARS['HTTP_HOST'];

  if (isset($HTTP_SERVER_VARS['REQUEST_URI']) && !empty($HTTP_SERVER_VARS['REQUEST_URI'])) {
    $www_location .= $HTTP_SERVER_VARS['REQUEST_URI'];
  } else {
    $www_location .= $HTTP_SERVER_VARS['SCRIPT_FILENAME'];
  }

  $www_location = substr($www_location, 0, strpos($www_location, 'install'));

  $dir_fs_www_root = osc_realpath(dirname(__FILE__) . '/../../../') . '/';
?>

<div class="mainBlock">
  <div class="stepsBox">
    <ol>
      <li>Database Server</li>
      <li style="font-weight: bold;">Web Server</li>
      <li>Online Store Settings</li>
      <li>Finished!</li>
    </ol>
  </div>

  <h1>New Installation</h1>

  <p>This web-based installation routine will correctly setup and configure osCommerce Online Merchant to run on this server.</p>
  <p>Please follow the on-screen instructions that will take you through the database server, web server, and store configuration options. If help is needed at any stage, please consult the documentation or seek help at the community support forums.</p>
</div>

<div class="contentBlock">
  <div class="infoPane">
    <h3>Step 2: Web Server</h3>

    <div class="infoPaneContents">
      <p>The web server takes care of serving the pages of your online store to your guests and customers. The web server parameters make sure the links to the pages point to the correct location.</p>
    </div>
  </div>

  <div class="contentPane">
    <h2>Web Server</h2>

    <form name="install" id="installForm" action="install.php?step=3" method="post">

    <table border="0" width="99%" cellspacing="0" cellpadding="5" class="inputForm">
      <tr>
        <td class="inputField"><?php echo 'WWW Address<br />' . osc_draw_input_field('HTTP_WWW_ADDRESS', $www_location, 'class="text"'); ?></td>
        <td class="inputDescription">The web address to the online store.</td>
      </tr>
      <tr>
        <td class="inputField"><?php echo 'Webserver Root Directory<br />' . osc_draw_input_field('DIR_FS_DOCUMENT_ROOT', $dir_fs_www_root, 'class="text"'); ?></td>
        <td class="inputDescription">The directory where the online store is installed on the server.</td>
      </tr>
    </table>

    <p><?php echo osc_draw_button('Continue', 'triangle-1-e', null, 'primary'); ?></p>

<?php
  foreach ( $HTTP_POST_VARS as $key => $value ) {
    if (($key != 'x') && ($key != 'y')) {
      echo osc_draw_hidden_field($key, $value);
    }
  }
?>

    </form>
  </div>
</div>
