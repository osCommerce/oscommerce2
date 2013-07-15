<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2013 osCommerce

  Released under the GNU General Public License
*/

  $dir_fs_document_root = $HTTP_POST_VARS['DIR_FS_DOCUMENT_ROOT'];
  if ((substr($dir_fs_document_root, -1) != '\\') && (substr($dir_fs_document_root, -1) != '/')) {
    if (strrpos($dir_fs_document_root, '\\') !== false) {
      $dir_fs_document_root .= '\\';
    } else {
      $dir_fs_document_root .= '/';
    }
  }
?>

<div class="mainBlock">
  <div class="stepsBox">
    <ol>
      <li>Database Server</li>
      <li>Web Server</li>
      <li style="font-weight: bold;">Online Store Settings</li>
      <li>Finished!</li>
    </ol>
  </div>

  <h1>New Installation</h1>

  <p>This web-based installation routine will correctly setup and configure osCommerce Online Merchant to run on this server.</p>
  <p>Please follow the on-screen instructions that will take you through the database server, web server, and store configuration options. If help is needed at any stage, please consult the documentation or seek help at the community support forums.</p>
</div>

<div class="contentBlock">
  <div class="infoPane">
    <h3>Step 3: Online Store Settings</h3>

    <div class="infoPaneContents">
      <p>Here you can define the name of your online store and the contact information for the store owner.</p>
      <p>The administrator username and password are used to log into the protected administration tool section.</p>
    </div>
  </div>

  <div class="contentPane">
    <h2>Online Store Settings</h2>

    <form name="install" id="installForm" action="install.php?step=4" method="post">

    <table border="0" width="99%" cellspacing="0" cellpadding="5" class="inputForm">
      <tr>
        <td class="inputField"><?php echo 'Store Name<br />' . osc_draw_input_field('CFG_STORE_NAME', null, 'class="text"'); ?></td>
        <td class="inputDescription">The name of the online store that is presented to the public.</td>
      </tr>
      <tr>
        <td class="inputField"><?php echo 'Store Owner Name<br />' . osc_draw_input_field('CFG_STORE_OWNER_NAME', null, 'class="text"'); ?></td>
        <td class="inputDescription">The name of the store owner that is presented to the public.</td>
      </tr>
      <tr>
        <td class="inputField"><?php echo 'Store Owner E-Mail Address<br />' . osc_draw_input_field('CFG_STORE_OWNER_EMAIL_ADDRESS', null, 'class="text"'); ?></td>
        <td class="inputDescription">The e-mail address of the store owner that is presented to the public.</td>
      </tr>
      <tr>
        <td class="inputField"><?php echo 'Administrator Username<br />' . osc_draw_input_field('CFG_ADMINISTRATOR_USERNAME', null, 'class="text"'); ?></td>
        <td class="inputDescription">The administrator username to use for the administration tool.</td>
      </tr>
      <tr>
        <td class="inputField"><?php echo 'Administrator Password<br />' . osc_draw_input_field('CFG_ADMINISTRATOR_PASSWORD', null, 'class="text"'); ?></td>
        <td class="inputDescription">The password to use for the administrator account.</td>
      </tr>

<?php
  if (osc_is_writable($dir_fs_document_root) && osc_is_writable($dir_fs_document_root . 'admin')) {
?>
      <tr>
        <td class="inputField"><?php echo 'Administration Directory Name<br />' . osc_draw_input_field('CFG_ADMIN_DIRECTORY', 'admin', 'class="text"'); ?></td>
        <td class="inputDescription">This is the directory where the administration section will be installed. You should change this for security reasons.</td>
      </tr>
<?php
  }

  if (PHP_VERSION >= '5.2') {
?>
      <tr>
        <td class="inputField"><?php echo 'Time Zone<br />' . osc_draw_time_zone_select_menu('CFG_TIME_ZONE'); ?></td>
        <td class="inputDescription">The time zone to base the date and time on.</td>
      </tr>
<?php
  }
?>

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
