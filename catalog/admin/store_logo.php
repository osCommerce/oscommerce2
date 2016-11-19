<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\FileSystem;
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
        $store_logo->set_destination(OSCOM::getConfig('dir_root', 'Shop') . 'images/');

        if ($store_logo->parse()) {
          if ($store_logo->save()) {
            $OSCOM_Db->save('configuration', [
              'configuration_value' => $store_logo->filename,
              'last_modified' => 'now()'
            ], [
              'configuration_key' => 'STORE_LOGO'
            ]);

            $OSCOM_MessageStack->add(OSCOM::getDef('success_logo_updated'), 'success');
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

  if (!FileSystem::isWritable(OSCOM::getConfig('dir_root', 'Shop') . 'images/')) {
    $OSCOM_MessageStack->add(OSCOM::getDef('error_images_directory_not_writeable', ['sec_dir_permissions_link' => OSCOM::link(FILENAME_SEC_DIR_PERMISSIONS)]), 'error');
  }

  require($oscTemplate->getFile('template_top.php'));
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo OSCOM::getDef('heading_title'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo HTML::image(OSCOM::linkImage('Shop/' . STORE_LOGO)); ?></td>
      </tr>
      <tr>
        <td><?php echo HTML::form('logo', OSCOM::link(FILENAME_STORE_LOGO, 'action=save'), 'post', 'enctype="multipart/form-data"'); ?>
          <table border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td class="main" valign="top"><?php echo OSCOM::getDef('text_logo_image'); ?></td>
              <td class="main"><?php echo HTML::fileField('store_logo'); ?></td>
              <td class="smallText"><?php echo HTML::button(OSCOM::getDef('image_save'), 'fa fa-save'); ?></td>
            </tr>
          </table>
        </form></td>
      </tr>
      <tr>
        <td class="main"><?php echo OSCOM::getDef('text_format_and_location'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo OSCOM::getConfig('dir_root', 'Shop') . 'images/' . STORE_LOGO; ?></td>
      </tr>
    </table>

<?php
  require($oscTemplate->getFile('template_bottom.php'));
  require('includes/application_bottom.php');
?>
