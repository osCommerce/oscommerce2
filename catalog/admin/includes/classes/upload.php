<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

  use OSC\OM\FileSystem;
  use OSC\OM\Registry;

  class upload {
    var $file, $filename, $destination, $permissions, $extensions, $tmp_filename, $message_location;

    function upload($file = '', $destination = '', $permissions = '777', $extensions = '') {
      $this->set_file($file);
      $this->set_destination($destination);
      $this->set_permissions($permissions);
      $this->set_extensions($extensions);

      $this->set_output_messages('direct');

      if (tep_not_null($this->file) && tep_not_null($this->destination)) {
        $this->set_output_messages('session');

        if ( ($this->parse() == true) && ($this->save() == true) ) {
          return true;
        } else {
          return false;
        }
      }
    }

    function parse() {
      $OSCOM_MessageStack = Registry::get('MessageStack');

      $file = array();

      if (isset($_FILES[$this->file])) {
        $file = array('name' => $_FILES[$this->file]['name'],
                      'type' => $_FILES[$this->file]['type'],
                      'size' => $_FILES[$this->file]['size'],
                      'tmp_name' => $_FILES[$this->file]['tmp_name']);
      } elseif (isset($_FILES[$this->file])) {
        $file = array('name' => $_FILES[$this->file]['name'],
                      'type' => $_FILES[$this->file]['type'],
                      'size' => $_FILES[$this->file]['size'],
                      'tmp_name' => $_FILES[$this->file]['tmp_name']);
      }

      if ( isset($file['tmp_name']) && tep_not_null($file['tmp_name']) && ($file['tmp_name'] != 'none') && is_uploaded_file($file['tmp_name']) ) {
        if (sizeof($this->extensions) > 0) {
          if (!in_array(strtolower(substr($file['name'], strrpos($file['name'], '.')+1)), $this->extensions)) {
            if ($this->message_location == 'direct') {
              $OSCOM_MessageStack->add(OSCOM::getDef('error_filetype_not_allowed'), 'error');
            } else {
              $OSCOM_MessageStack->add(OSCOM::getDef('error_filetype_not_allowed'), 'error');
            }

            return false;
          }
        }

        $this->set_file($file);
        $this->set_filename($file['name']);
        $this->set_tmp_filename($file['tmp_name']);

        return $this->check_destination();
      } else {
        if ($this->message_location == 'direct') {
          $OSCOM_MessageStack->add(OSCOM::getDef('warning_no_file_uploaded'), 'warning');
        } else {
          $OSCOM_MessageStack->add(OSCOM::getDef('warning_no_file_uploaded'), 'warning');
        }

        return false;
      }
    }

    function save() {
      $OSCOM_MessageStack = Registry::get('MessageStack');

      if (substr($this->destination, -1) != '/') $this->destination .= '/';

      if (move_uploaded_file($this->file['tmp_name'], $this->destination . $this->filename)) {
        chmod($this->destination . $this->filename, $this->permissions);

        if ($this->message_location == 'direct') {
          $OSCOM_MessageStack->add(OSCOM::getDef('success_file_saved_successfully'), 'success');
        } else {
          $OSCOM_MessageStack->add(OSCOM::getDef('success_file_saved_successfully'), 'success');
        }

        return true;
      } else {
        if ($this->message_location == 'direct') {
          $OSCOM_MessageStack->add(OSCOM::getDef('error_file_not_saved'), 'error');
        } else {
          $OSCOM_MessageStack->add(OSCOM::getDef('error_file_not_saved'), 'error');
        }

        return false;
      }
    }

    function set_file($file) {
      $this->file = $file;
    }

    function set_destination($destination) {
      $this->destination = $destination;
    }

    function set_permissions($permissions) {
      $this->permissions = octdec($permissions);
    }

    function set_filename($filename) {
      $this->filename = $filename;
    }

    function set_tmp_filename($filename) {
      $this->tmp_filename = $filename;
    }

    function set_extensions($extensions) {
      if (tep_not_null($extensions)) {
        if (is_array($extensions)) {
          $this->extensions = $extensions;
        } else {
          $this->extensions = array($extensions);
        }
      } else {
        $this->extensions = array();
      }
    }

    function check_destination() {
      $OSCOM_MessageStack = Registry::get('MessageStack');

      if (!FileSystem::isWritable($this->destination)) {
        if (is_dir($this->destination)) {
          if ($this->message_location == 'direct') {
            $OSCOM_MessageStack->add(OSCOM::getDef('error_destination_not_writeable', ['destination' => $this->destination]), 'error');
          } else {
            $OSCOM_MessageStack->add(OSCOM::getDef('error_destination_not_writeable', ['destination' => $this->destination]), 'error');
          }
        } else {
          if ($this->message_location == 'direct') {
            $OSCOM_MessageStack->add(OSCOM::getDef('error_destination_does_not_exist', ['destination' => $this->destination]), 'error');
          } else {
            $OSCOM_MessageStack->add(OSCOM::getDef('error_destination_does_not_exist', ['destination' => $this->destination]), 'error');
          }
        }

        return false;
      } else {
        return true;
      }
    }

    function set_output_messages($location) {
      switch ($location) {
        case 'session':
          $this->message_location = 'session';
          break;
        case 'direct':
        default:
          $this->message_location = 'direct';
          break;
      }
    }
  }
?>
