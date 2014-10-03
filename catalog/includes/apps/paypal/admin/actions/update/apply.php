<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  $ppUpdateApplyResult = array('rpcStatus' => -1);

  if ( $HTTP_GET_VARS['v'] > $OSCOM_PayPal->getVersion() ) {
    $with_compress = array_search('GZ', Phar::getSupportedCompression()) !== false;

    $filename = 'update.phar' . ($with_compress === true ? '.gz' : '');
    $filepath = DIR_FS_CATALOG . 'includes/apps/paypal/work/' . $filename;

    if ( file_exists($filepath) ) {
      $files = array();

      $phar_can_open = true;

      try {
        $phar = new Phar($filepath);
      } catch ( Exception $e ) {
        $phar_can_open = false;
      }

      if ( $phar_can_open === true ) {
        $meta = $phar->getMetadata();

        $check_against = array('provider' => 'paypal',
                               'code' => 'app',
                               'version' => $HTTP_GET_VARS['v'],
                               'req' => '2.300');

        $check_diff = array_diff_assoc($meta, $check_against);

        if ( empty($check_diff) ) {
          $update_version = $meta['version'];

          $errors = array();

          $work_dir = DIR_FS_CATALOG . 'includes/apps/paypal/work/phar_contents';

          if ( file_exists($work_dir) ) {
            $OSCOM_PayPal->rmdir($work_dir);
          }

          mkdir($work_dir);

          if ( $phar->extractTo($work_dir) ) {
            unset($phar);

            $contents = new RecursiveDirectoryIterator($work_dir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS);

            foreach ( new RecursiveIteratorIterator($contents) as $i ) {
              $pathname = substr($i->getPathName(), strlen($work_dir . '/'));

              if ( substr($pathname, 0, 8) == 'catalog/' ) {
                if ( !$OSCOM_PayPal->isWritable(DIR_FS_CATALOG . substr($pathname, 8)) || !$OSCOM_PayPal->isWritable(DIR_FS_CATALOG . dirname(substr($pathname, 8))) ) {
                  $errors[] = DIR_FS_CATALOG . substr($pathname, 8);
                }
              } elseif ( substr($pathname, 0, 6) == 'admin/' ) {
                if ( !$OSCOM_PayPal->isWritable(DIR_FS_ADMIN . substr($pathname, 6)) || !$OSCOM_PayPal->isWritable(DIR_FS_ADMIN . dirname(substr($pathname, 6))) ) {
                  $errors[] = DIR_FS_ADMIN . substr($pathname, 6);
                }
              }
            }

            if ( empty($errors) ) {
// reset the log
              if ( file_exists(DIR_FS_CATALOG . 'includes/apps/paypal/work/update-' . $update_version . '.txt') && is_writable(DIR_FS_CATALOG . 'includes/apps/paypal/work/update-' . $update_version . '.txt') ) {
                unlink(DIR_FS_CATALOG . 'includes/apps/paypal/work/update-' . $update_version . '.txt');
              }

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
                    $OSCOM_PayPal->logUpdate('Extracted: ' . DIR_FS_CATALOG . substr($pathname, 8), $update_version);
                  } else {
                    $OSCOM_PayPal->logUpdate('*** Could Not Extract: ' . DIR_FS_CATALOG . substr($pathname, 8), $update_version);

                    $errors[] = DIR_FS_CATALOG . substr($pathname, 8);

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
                    $OSCOM_PayPal->logUpdate('Extracted: ' . DIR_FS_ADMIN . substr($pathname, 6), $update_version);
                  } else {
                    $OSCOM_PayPal->logUpdate('*** Could Not Extract: ' . DIR_FS_ADMIN . substr($pathname, 6), $update_version);

                    $errors[] = DIR_FS_ADMIN . substr($pathname, 6);

                    break;
                  }
                }
              }

              if ( empty($errors) ) {
                $OSCOM_PayPal->logUpdate('##### UPDATE TO ' . $update_version . ' COMPLETED', $update_version);
              } else {
                $OSCOM_PayPal->logUpdate('##### UPDATE TO ' . $update_version . ' FAILED', $update_version);
              }
            }
          }

          $OSCOM_PayPal->rmdir($work_dir);

          if ( empty($errors) ) {
            $ppUpdateApplyResult['rpcStatus'] = 1;
          }
        }
      }

      unlink($filepath);
    }
  }

  echo json_encode($ppUpdateApplyResult);

  exit;
?>
