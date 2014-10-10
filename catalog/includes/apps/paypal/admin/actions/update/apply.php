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

    $with_compress = array_search('GZ', Phar::getSupportedCompression()) !== false;

    $filepath = DIR_FS_CATALOG . 'includes/apps/paypal/work/update.osc' . ($with_compress === true ? '.gz' : '');

// reset the log
    if ( file_exists(DIR_FS_CATALOG . 'includes/apps/paypal/work/update_log-' . $update_version . '.php') && is_writable(DIR_FS_CATALOG . 'includes/apps/paypal/work/update_log-' . $update_version . '.php') ) {
      unlink(DIR_FS_CATALOG . 'includes/apps/paypal/work/update_log-' . $update_version . '.php');
    }

    if ( file_exists($filepath) ) {
      $phar_can_open = true;

      try {
        $phar = @new PharData($filepath);
      } catch ( Exception $e ) {
        $phar_can_open = false;
      }

      if ( $phar_can_open === true ) {
        $meta_pass = true;

        $meta = $phar->getMetadata();

        $check_against = array('provider' => 'paypal',
                               'code' => 'app',
                               'version' => $HTTP_GET_VARS['v'],
                               'req' => '2.300');

        foreach ( $check_against as $key => $value ) {
          if ( !isset($meta[$key]) || ($meta[$key] != $value) ) {
            $meta_pass = false;
            break;
          }
        }

        if ( $meta_pass === true ) {
          $meta_pass = false;

          if ( isset($meta['sig']) && file_exists(DIR_FS_CATALOG . 'includes/apps/paypal/work/paypal.pubkey') ) {
            if ( @openssl_verify($check_against['provider'] . '-' . $check_against['code'] . '-' . $check_against['version'] . '-' . $check_against['req'], base64_decode($meta['sig']), file_get_contents(DIR_FS_CATALOG . 'includes/apps/paypal/work/paypal.pubkey')) === 1 ) {
              $meta_pass = true;
            }
          }
        }

        if ( $meta_pass === true ) {
          $work_dir = DIR_FS_CATALOG . 'includes/apps/paypal/work/update_contents';

          if ( file_exists($work_dir) ) {
            $OSCOM_PayPal->rmdir($work_dir);
          }

          mkdir($work_dir);

          if ( $phar->extractTo($work_dir) ) {
            unset($phar);

            $errors = array();

            if ( !$OSCOM_PayPal->isWritable(DIR_FS_CATALOG . 'includes/apps/paypal/version.txt') ) {
              $errors[] = realpath(DIR_FS_CATALOG . 'includes/apps/paypal/version.txt');
            }

            $contents = new RecursiveDirectoryIterator($work_dir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS);

            foreach ( new RecursiveIteratorIterator($contents) as $i ) {
              $pathname = substr($i->getPathName(), strlen($work_dir . '/'));

              if ( substr($pathname, 0, 8) == 'catalog/' ) {
                if ( !$OSCOM_PayPal->isWritable(DIR_FS_CATALOG . substr($pathname, 8)) || !$OSCOM_PayPal->isWritable(DIR_FS_CATALOG . dirname(substr($pathname, 8))) ) {
                  $errors[] = realpath(DIR_FS_CATALOG . substr($pathname, 8));
                }
              } elseif ( substr($pathname, 0, 6) == 'admin/' ) {
                if ( !$OSCOM_PayPal->isWritable(DIR_FS_ADMIN . substr($pathname, 6)) || !$OSCOM_PayPal->isWritable(DIR_FS_ADMIN . dirname(substr($pathname, 6))) ) {
                  $errors[] = realpath(DIR_FS_ADMIN . substr($pathname, 6));
                }
              }
            }

            if ( empty($errors) ) {
              $OSCOM_PayPal->logUpdate('##### UPDATE TO ' . $update_version . ' STARTED', $update_version);

              foreach ( new RecursiveIteratorIterator($contents) as $i ) {
                $pathname = substr($i->getPathName(), strlen($work_dir . '/'));

                if ( substr($pathname, 0, 8) == 'catalog/' ) {
                  $target = dirname(substr($pathname, 8));

                  if ( $target == '.' ) {
                    $target = '';
                  }

                  if ( !file_exists(DIR_FS_CATALOG . $target) ) {
                    mkdir(DIR_FS_CATALOG . $target, 0777, true);
                  }

                  if ( substr($target, -1) != '/' ) {
                    $target .= '/';
                  }

                  if ( copy($i->getPathName(), DIR_FS_CATALOG . $target . basename($pathname)) ) {
                    $OSCOM_PayPal->logUpdate('Updated: ' . realpath(DIR_FS_CATALOG . substr($pathname, 8)), $update_version);
                  } else {
                    $errors[] = realpath(DIR_FS_CATALOG . substr($pathname, 8));

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

                  if ( substr($target, -1) != '/' ) {
                    $target .= '/';
                  }

                  if ( copy($i->getPathName(), DIR_FS_ADMIN . $target . basename($pathname)) ) {
                    $OSCOM_PayPal->logUpdate('Updated: ' . realpath(DIR_FS_ADMIN . substr($pathname, 6)), $update_version);
                  } else {
                    $errors[] = realpath(DIR_FS_ADMIN . substr($pathname, 6));

                    break;
                  }
                }
              }

              if ( file_put_contents(DIR_FS_CATALOG . 'includes/apps/paypal/version.txt', $HTTP_GET_VARS['v']) ) {
                $OSCOM_PayPal->logUpdate('Updated: ' . realpath(DIR_FS_CATALOG . 'includes/apps/paypal/version.txt'), $update_version);
              } else {
                $errors[] = realpath(DIR_FS_CATALOG . 'includes/apps/paypal/version.txt');
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

          $OSCOM_PayPal->rmdir($work_dir);
        } else {
          $OSCOM_PayPal->logUpdate('Update Package Verification Failed', $update_version);
        }
      } else {
        $OSCOM_PayPal->logUpdate('Invalid Update Package Format', $update_version);
      }

      unlink($filepath);
    } else {
      $OSCOM_PayPal->logUpdate('Update Package Does Not Exist: '. $filepath, $update_version);
    }
  }

  echo json_encode($ppUpdateApplyResult);

  exit;
?>
