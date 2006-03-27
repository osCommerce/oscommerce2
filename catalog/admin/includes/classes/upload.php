<?php
/*
  $Id: upload.php,v 1.2 2003/06/20 00:18:30 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

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
      global $messageStack;

      if (isset($_FILES[$this->file])) {
        $file = array('name' => $_FILES[$this->file]['name'],
                      'type' => $_FILES[$this->file]['type'],
                      'size' => $_FILES[$this->file]['size'],
                      'tmp_name' => $_FILES[$this->file]['tmp_name']);
      } elseif (isset($GLOBALS['HTTP_POST_FILES'][$this->file])) {
        global $HTTP_POST_FILES;

        $file = array('name' => $HTTP_POST_FILES[$this->file]['name'],
                      'type' => $HTTP_POST_FILES[$this->file]['type'],
                      'size' => $HTTP_POST_FILES[$this->file]['size'],
                      'tmp_name' => $HTTP_POST_FILES[$this->file]['tmp_name']);
      } else {
        $file = array('name' => (isset($GLOBALS[$this->file . '_name']) ? $GLOBALS[$this->file . '_name'] : ''),
                      'type' => (isset($GLOBALS[$this->file . '_type']) ? $GLOBALS[$this->file . '_type'] : ''),
                      'size' => (isset($GLOBALS[$this->file . '_size']) ? $GLOBALS[$this->file . '_size'] : ''),
                      'tmp_name' => (isset($GLOBALS[$this->file]) ? $GLOBALS[$this->file] : ''));
      }

      if ( tep_not_null($file['tmp_name']) && ($file['tmp_name'] != 'none') && is_uploaded_file($file['tmp_name']) ) {
        if (sizeof($this->extensions) > 0) {
          if (!in_array(strtolower(substr($file['name'], strrpos($file['name'], '.')+1)), $this->extensions)) {
            if ($this->message_location == 'direct') {
              $messageStack->add(ERROR_FILETYPE_NOT_ALLOWED, 'error');
            } else {
              $messageStack->add_session(ERROR_FILETYPE_NOT_ALLOWED, 'error');
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
          $messageStack->add(WARNING_NO_FILE_UPLOADED, 'warning');
        } else {
          $messageStack->add_session(WARNING_NO_FILE_UPLOADED, 'warning');
        }

        return false;
      }
    }

    function save() {
      global $messageStack;

      if (substr($this->destination, -1) != '/') $this->destination .= '/';

      if (move_uploaded_file($this->file['tmp_name'], $this->destination . $this->filename)) {
        chmod($this->destination . $this->filename, $this->permissions);

        if ($this->message_location == 'direct') {
          $messageStack->add(SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');
        } else {
          $messageStack->add_session(SUCCESS_FILE_SAVED_SUCCESSFULLY, 'success');
        }

        return true;
      } else {
        if ($this->message_location == 'direct') {
          $messageStack->add(ERROR_FILE_NOT_SAVED, 'error');
        } else {
          $messageStack->add_session(ERROR_FILE_NOT_SAVED, 'error');
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
      global $messageStack;

      if (!is_writeable($this->destination)) {
        if (is_dir($this->destination)) {
          if ($this->message_location == 'direct') {
            $messageStack->add(sprintf(ERROR_DESTINATION_NOT_WRITEABLE, $this->destination), 'error');
          } else {
            $messageStack->add_session(sprintf(ERROR_DESTINATION_NOT_WRITEABLE, $this->destination), 'error');
          }
        } else {
          if ($this->message_location == 'direct') {
            $messageStack->add(sprintf(ERROR_DESTINATION_DOES_NOT_EXIST, $this->destination), 'error');
          } else {
            $messageStack->add_session(sprintf(ERROR_DESTINATION_DOES_NOT_EXIST, $this->destination), 'error');
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
