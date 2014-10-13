<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $ppUpdateApplyResult = array('rpcStatus' => -1);

  if ( isset($HTTP_GET_VARS['v']) && is_numeric($HTTP_GET_VARS['v']) && ($HTTP_GET_VARS['v'] > $OSCOM_PayPal->getVersion()) ) {
    $update_version = basename($HTTP_GET_VARS['v']);

    $update_zip = DIR_FS_CATALOG . 'includes/apps/paypal/work/update.zip';

// reset the log
    if ( file_exists(DIR_FS_CATALOG . 'includes/apps/paypal/work/update_log-' . $update_version . '.php') && is_writable(DIR_FS_CATALOG . 'includes/apps/paypal/work/update_log-' . $update_version . '.php') ) {
      unlink(DIR_FS_CATALOG . 'includes/apps/paypal/work/update_log-' . $update_version . '.php');
    }

    if ( file_exists($update_zip) ) {
      $work_dir = DIR_FS_CATALOG . 'includes/apps/paypal/work/update_contents';

      if ( file_exists($work_dir) ) {
        $OSCOM_PayPal->rmdir($work_dir);
      }

      mkdir($work_dir);

      $zip = new ZipArchive();

      if ( $zip->open($update_zip) === true ) {
        $zip->extractTo($work_dir);
        $zip->close();
      }

      unset($zip);
      unlink($update_zip);

      $update_pkg = $work_dir . '/' . $update_version . '.zip';

      if ( file_exists($update_pkg) && file_exists($update_pkg . '.sig') ) {
        $public = openssl_get_publickey(file_get_contents(DIR_FS_CATALOG . 'includes/apps/paypal/work/paypal.pubkey'));

        if ( openssl_verify(sha1_file($update_pkg), file_get_contents($update_pkg . '.sig'), $public) === 1 ) {
          mkdir($work_dir . '/' . $update_version);

          $zip = new ZipArchive();

          if ( $zip->open($update_pkg) === true ) {
            $zip->extractTo($work_dir . '/' . $update_version);
            $zip->close();
          }

          unset($zip);
          unlink($update_pkg);

          $meta = array();
          $meta_pass = true;

          if ( file_exists($work_dir . '/' . $update_version . '/oscom_update.json') ) {
            $meta = @json_decode(file_get_contents($work_dir . '/' . $update_version . '/oscom_update.json'), true);
          }

          $check_against = array('provider' => 'paypal',
                                 'app' => 'app',
                                 'version' => $update_version,
                                 'req' => '2.300');

          foreach ( $check_against as $key => $value ) {
            if ( !isset($meta[$key]) || ($meta[$key] != $value) ) {
              $meta_pass = false;
              break;
            }
          }

          if ( $meta_pass === true ) {
            $errors = array();

            if ( !$OSCOM_PayPal->isWritable(DIR_FS_CATALOG . 'includes/apps/paypal/version.txt') ) {
              $errors[] = $OSCOM_PayPal->displayPath(DIR_FS_CATALOG . 'includes/apps/paypal/version.txt');
            }

            $update_pkg_contents = $OSCOM_PayPal->getDirectoryContents($work_dir . '/' . $update_version);

            foreach ( $update_pkg_contents as $file ) {
              $pathname = substr($file, strlen($work_dir . '/' . $update_version . '/'));

              if ( substr($pathname, 0, 8) == 'catalog/' ) {
                if ( !$OSCOM_PayPal->isWritable(DIR_FS_CATALOG . substr($pathname, 8)) || !$OSCOM_PayPal->isWritable(DIR_FS_CATALOG . dirname(substr($pathname, 8))) ) {
                  $errors[] = $OSCOM_PayPal->displayPath(DIR_FS_CATALOG . substr($pathname, 8));
                }
              } elseif ( substr($pathname, 0, 6) == 'admin/' ) {
                if ( !$OSCOM_PayPal->isWritable(DIR_FS_ADMIN . substr($pathname, 6)) || !$OSCOM_PayPal->isWritable(DIR_FS_ADMIN . dirname(substr($pathname, 6))) ) {
                  $errors[] = $OSCOM_PayPal->displayPath(DIR_FS_ADMIN . substr($pathname, 6));
                }
              }
            }

            if ( empty($errors) ) {
              $OSCOM_PayPal->logUpdate('##### UPDATE TO ' . $update_version . ' STARTED', $update_version);

              foreach ( $update_pkg_contents as $file ) {
                $pathname = substr($file, strlen($work_dir . '/' . $update_version . '/'));

                if ( substr($pathname, 0, 8) == 'catalog/' ) {
                  $target = dirname(substr($pathname, 8));

                  if ( $target == '.' ) {
                    $target = '';
                  }

                  if ( !file_exists(DIR_FS_CATALOG . $target) ) {
                    mkdir(DIR_FS_CATALOG . $target, 0777, true);
                  }

                  if ( !empty($target) && (substr($target, -1) != DIRECTORY_SEPARATOR) ) {
                    $target .= DIRECTORY_SEPARATOR;
                  }

                  if ( copy($file, DIR_FS_CATALOG . $target . basename($pathname)) ) {
                    $OSCOM_PayPal->logUpdate('Updated: ' . $OSCOM_PayPal->displayPath(DIR_FS_CATALOG . $target . basename($pathname)), $update_version);
                  } else {
                    $errors[] = $OSCOM_PayPal->displayPath(DIR_FS_CATALOG . $target . basename($pathname));

                    break;
                  }
                } elseif ( substr($pathname, 0, 6) == 'admin/' ) {
                  $target = dirname(substr($pathname, 6));

                  if ( $target == '.' ) {
                    $target = '';
                  }

                  if ( !file_exists(DIR_FS_ADMIN . $target) ) {
                    mkdir(DIR_FS_ADMIN . $target, 0777, true);
                  }

                  if ( !empty($target) && (substr($target, -1) != DIRECTORY_SEPARATOR) ) {
                    $target .= DIRECTORY_SEPARATOR;
                  }

                  if ( copy($file, DIR_FS_ADMIN . $target . basename($pathname)) ) {
                    $OSCOM_PayPal->logUpdate('Updated: ' . $OSCOM_PayPal->displayPath(DIR_FS_ADMIN . $target . basename($pathname)), $update_version);
                  } else {
                    $errors[] = $OSCOM_PayPal->displayPath(DIR_FS_ADMIN . $target . basename($pathname));

                    break;
                  }
                }
              }

              if ( empty($errors) ) {
                if ( file_put_contents(DIR_FS_CATALOG . 'includes/apps/paypal/version.txt', $update_version) ) {
                  $OSCOM_PayPal->logUpdate('Updated: ' . $OSCOM_PayPal->displayPath(DIR_FS_CATALOG . 'includes/apps/paypal/version.txt'), $update_version);
                } else {
                  $errors[] = $OSCOM_PayPal->displayPath(DIR_FS_CATALOG . 'includes/apps/paypal/version.txt');
                }
              }

              if ( empty($errors) ) {
                $OSCOM_PayPal->logUpdate('##### UPDATE TO ' . $update_version . ' COMPLETED', $update_version);

                $ppUpdateApplyResult['rpcStatus'] = 1;
              } else {
                $OSCOM_PayPal->logUpdate('##### UPDATE TO ' . $update_version . ' FAILED', $update_version);
              }
            }

            if ( !empty($errors) ) {
              $OSCOM_PayPal->logUpdate('*** Could not update the following files. Please update the file and directory permissions to allow write access.', $update_version);

              foreach ( $errors as $e ) {
                $OSCOM_PayPal->logUpdate($e, $update_version);
              }
            }
          } else {
            $OSCOM_PayPal->logUpdate('Update Package Extraction Failed', $update_version);
          }
        } else {
          $OSCOM_PayPal->logUpdate('Update Package Verification Failed', $update_version);
        }
      } else {
        $OSCOM_PayPal->logUpdate('Invalid Update Package Contents', $update_version);
      }

      $OSCOM_PayPal->rmdir($work_dir);
    } else {
      $OSCOM_PayPal->logUpdate('Update Package Does Not Exist: '. $update_zip, $update_version);
    }
  }

  echo json_encode($ppUpdateApplyResult);

  exit;
?>
