<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  use OSC\OM\HTML;
  use OSC\OM\OSCOM;

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'save':
        $error = false;

        $store_logo = new upload('store_logo');
        $store_logo->set_extensions(array('png', 'gif', 'jpg'));
        $store_logo->set_destination(DIR_FS_CATALOG_IMAGES);

        if ($store_logo->parse()) {
          if ($store_logo->save()) {
            $OSCOM_Db->save('configuration', [
              'configuration_value' => $store_logo->filename,
              'last_modified' => 'now()'
            ], [
              'configuration_key' => 'STORE_LOGO'
            ]);

            $messageStack->add_session(SUCCESS_LOGO_UPDATED, 'success');
          } else {
            $error = true;
          }
        } else {
          $error = true;
        }

        if ($error == false) {
          OSCOM::redirect(FILENAME_STORE_LOGO);
        }
        break;
    }
  }

  if (!tep_is_writable(DIR_FS_CATALOG_IMAGES)) {
    $messageStack->add(sprintf(ERROR_IMAGES_DIRECTORY_NOT_WRITEABLE, OSCOM::link(FILENAME_SEC_DIR_PERMISSIONS)), 'error');
  }

  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo HTML::image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . STORE_LOGO); ?></td>
      </tr>
      <tr>
        <td><?php echo HTML::form('logo', OSCOM::link(FILENAME_STORE_LOGO, 'action=save'), 'post', 'enctype="multipart/form-data"'); ?>
          <table border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td class="main" valign="top"><?php echo TEXT_LOGO_IMAGE; ?></td>
              <td class="main"><?php echo HTML::fileField('store_logo'); ?></td>
              <td class="smallText"><?php echo HTML::button(IMAGE_SAVE, 'fa fa-save', null, 'primary'); ?></td>
            </tr>
          </table>
        </form></td>
      </tr>
      <tr>
        <td class="main"><?php echo TEXT_FORMAT_AND_LOCATION; ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo DIR_FS_CATALOG_IMAGES . STORE_LOGO; ?></td>
      </tr>
    </table>

<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
