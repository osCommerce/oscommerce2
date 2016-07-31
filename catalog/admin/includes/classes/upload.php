<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

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
              $OSCOM_MessageStack->add(ERROR_FILETYPE_NOT_ALLOWED, 'error');
            } else {
              $OSCOM_MessageStack->add(ERROR_FILETYPE_NOT_ALLOWED, 'error');
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
          $OSCOM_MessageStack->add(WARNING_NO_FILE_UPLOADED, 'warning');
        } else {
          $OSCOM_MessageStack->add(WARNING_NO_FILE_UPLOADED, 'warning');
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
          $OSCOM_MessageStack->add(SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');
        } else {
          $OSCOM_MessageStack->add(SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');
        }

        return true;
      } else {
        if ($this->message_location == 'direct') {
          $OSCOM_MessageStack->add(ERROR_FILE_NOT_SAVED, 'error');
        } else {
          $OSCOM_MessageStack->add(ERROR_FILE_NOT_SAVED, 'error');
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

      if (!tep_is_writable($this->destination)) {
        if (is_dir($this->destination)) {
          if ($this->message_location == 'direct') {
            $OSCOM_MessageStack->add(sprintf(ERROR_DESTINATION_NOT_WRITEABLE, $this->destination), 'error');
          } else {
            $OSCOM_MessageStack->add(sprintf(ERROR_DESTINATION_NOT_WRITEABLE, $this->destination), 'error');
          }
        } else {
          if ($this->message_location == 'direct') {
            $OSCOM_MessageStack->add(sprintf(ERROR_DESTINATION_DOES_NOT_EXIST, $this->destination), 'error');
          } else {
            $OSCOM_MessageStack->add(sprintf(ERROR_DESTINATION_DOES_NOT_EXIST, $this->destination), 'error');
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
