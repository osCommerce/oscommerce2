<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM;

class FileSystem
{
    public static function getDirectoryContents($base)
    {
        $base = str_replace('\\', '/', $base); // Unix style directory separator "/"

        $dir = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_SELF | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS));

        $result = [];

        foreach ($dir as $file) {
            $result[] = $file->getPathName();
        }

        return $result;
    }

    public static function isWritable($location, $recursive_check = false)
    {
        if ($recursive_check === true) {
            if (!file_exists($location)) {
                while (true) {
                    $location = dirname($location);

                    if (file_exists($location)) {
                        break;
                    }
                }
            }
        }

        return is_writable($location);
    }

    public static function rmdir($dir, $dry_run = false)
    {
        $result = [];

        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if (!in_array($file, ['.', '..'])) {
                    if (is_dir($dir . '/' . $file)) {
                        $result = array_merge($result, static::rmdir($dir . '/' . $file, $dry_run));
                    } else {
                        $result[] = [
                            'type' => 'file',
                            'source' => $dir . '/' . $file,
                            'result' => ($dry_run === false) ? unlink($dir . '/' . $file) : static::isWritable($dir . '/' . $file)
                        ];
                    }
                }
            }

            $result[] = [
                'type' => 'directory',
                'source' => $dir,
                'result' => ($dry_run === false) ? rmdir($dir) : static::isWritable($dir)
            ];
        }

        return $result;
    }

    public static function displayPath($pathname)
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $pathname);
    }
}
