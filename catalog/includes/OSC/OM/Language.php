<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license GPL; https://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

use OSC\OM\OSCOM;

class Language
{
    protected $definitions = [];

    public function getDef($key, $values = null, $scope = 'global')
    {
        if (isset($this->definitions[$scope][$key])) {
            $def = $this->definitions[$scope][$key];

            if (is_array($values) && !empty($values)) {
                $def = str_replace(array_keys($values), array_values($values), $def);
            }

            return $def;
        }

        return $key;
    }

    public function loadDefinitionFile($filename, $language = null, $scope = 'global')
    {
        $language = isset($language) ? basename($language) : basename($_SESSION['language']);

        if ($language != 'english') {
            $this->loadDefinitionFile($filename, 'english', $scope);
        }

        if ((strpos($filename, '/') !== false) && (preg_match('/^([A-Z][A-Za-z0-9-_]*)\/(.*)$/', $filename, $matches) === 1) && OSCOM::siteExists($matches[1])) {
            $site = $matches[1];
            $filename = $matches[2];
        }

        $pathname = OSCOM::getConfig('dir_root') . 'includes/languages/' . $language . '/' . $filename;

        if (is_file($pathname)) {
            $this->loadDefinitionsFromFile($pathname, $scope);
        } else {
            trigger_error('OSC\OM\Language::loadDefinitionFile() - Filename does not exist: ' . $pathname);
        }
    }

    public function loadDefinitionsFromFile($filename, $scope = 'global')
    {
        $defs = [];

        foreach (file($filename) as $line) {
            $line = trim($line);

            if (!empty($line) && (substr($line, 0, 1) != '#')) {
                $delimiter = strpos($line, '=');

                if (($delimiter !== false) && (preg_match('/^[A-Za-z0-9_-]/', substr($line, 0, $delimiter)) === 1) && (substr_count(substr($line, 0, $delimiter), ' ') === 1)) {
                    $key = trim(substr($line, 0, $delimiter));
                    $value = trim(substr($line, $delimiter + 1));

                    $defs[$key] = $value;
                } elseif (isset($key)) {
                    $defs[$key] .= "\n" . $line;
                }
            }
        }

        if (isset($this->definitions[$scope])) {
            $this->definitions[$scope] = array_merge($this->definitions[$scope], $defs);
        } else {
            $this->definitions[$scope] = $defs;
        }
    }
}
