<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
  * @license MIT; https://www.oscommerce.com/license/mit.txt
  */

namespace OSC\OM;

use OSC\OM\Cache;
use OSC\OM\HTML;
use OSC\OM\OSCOM;
use OSC\OM\Registry;

class Language
{
    protected $language;
    protected $languages = [];
    protected $definitions = [];
    protected $detectors = [];
    protected $use_cache = false;
    protected $db;

    public function __construct($code = null)
    {
        $this->db = Registry::get('Db');

        $Qlanguages = $this->db->prepare('select languages_id, name, code, image, directory from :table_languages order by sort_order');
        $Qlanguages->setCache('languages-system');
        $Qlanguages->execute();

        while ($Qlanguages->fetch()) {
            $this->languages[$Qlanguages->value('code')] = [
                'id' => $Qlanguages->valueInt('languages_id'),
                'code' => $Qlanguages->value('code'),
                'name' => $Qlanguages->value('name'),
                'image' => $Qlanguages->value('image'),
                'directory' => $Qlanguages->value('directory')
            ];
        }

        $this->detectors = [
            'af' => 'af|afrikaans',
            'ar' => 'ar([-_][[:alpha:]]{2})?|arabic',
            'be' => 'be|belarusian',
            'bg' => 'bg|bulgarian',
            'br' => 'pt[-_]br|brazilian portuguese',
            'ca' => 'ca|catalan',
            'cs' => 'cs|czech',
            'da' => 'da|danish',
            'de' => 'de([-_][[:alpha:]]{2})?|german',
            'el' => 'el|greek',
            'en' => 'en([-_][[:alpha:]]{2})?|english',
            'es' => 'es([-_][[:alpha:]]{2})?|spanish',
            'et' => 'et|estonian',
            'eu' => 'eu|basque',
            'fa' => 'fa|farsi',
            'fi' => 'fi|finnish',
            'fo' => 'fo|faeroese',
            'fr' => 'fr([-_][[:alpha:]]{2})?|french',
            'ga' => 'ga|irish',
            'gl' => 'gl|galician',
            'he' => 'he|hebrew',
            'hi' => 'hi|hindi',
            'hr' => 'hr|croatian',
            'hu' => 'hu|hungarian',
            'id' => 'id|indonesian',
            'it' => 'it|italian',
            'ja' => 'ja|japanese',
            'ko' => 'ko|korean',
            'ka' => 'ka|georgian',
            'lt' => 'lt|lithuanian',
            'lv' => 'lv|latvian',
            'mk' => 'mk|macedonian',
            'mt' => 'mt|maltese',
            'ms' => 'ms|malaysian',
            'nl' => 'nl([-_][[:alpha:]]{2})?|dutch',
            'no' => 'no|norwegian',
            'pl' => 'pl|polish',
            'pt' => 'pt([-_][[:alpha:]]{2})?|portuguese',
            'ro' => 'ro|romanian',
            'ru' => 'ru|russian',
            'sk' => 'sk|slovak',
            'sq' => 'sq|albanian',
            'sr' => 'sr|serbian',
            'sv' => 'sv|swedish',
            'sz' => 'sz|sami',
            'sx' => 'sx|sutu',
            'th' => 'th|thai',
            'ts' => 'ts|tsonga',
            'tr' => 'tr|turkish',
            'tn' => 'tn|tswana',
            'uk' => 'uk|ukrainian',
            'ur' => 'ur|urdu',
            'vi' => 'vi|vietnamese',
            'tw' => 'zh[-_]tw|chinese traditional',
            'zh' => 'zh|chinese simplified',
            'ji' => 'ji|yiddish',
            'zu' => 'zu|zulu'
        ];

        if (!isset($code) || !$this->exists($code)) {
            if (isset($_SESSION['language'])) {
                $code = $_SESSION['language'];
            } else {
                $client = $this->getClientPreference();

                $code = ($client !== false) ? $client : DEFAULT_LANGUAGE;
            }
        }

        $this->set($code);
    }

    public function set($code)
    {
        if ($this->exists($code)) {
            $this->language = $code;
        } else {
            trigger_error('OSC\OM\Language::set() - The language does not exist: ' . $code);
        }
    }

    public function get($data = null, $language_code = null)
    {
        if (!isset($data)) {
            $data = 'code';
        }

        if (!isset($language_code)) {
            $language_code = $this->language;
        }

        return $this->languages[$language_code][$data];
    }

    public function getId($language_code = null)
    {
        return (int)$this->get('id', $language_code);
    }

    public function getAll()
    {
        return $this->languages;
    }

    public function exists($code)
    {
        return isset($this->languages[$code]);
    }

    public function getImage($language_code, $width = null, $height = null)
    {
        if (!isset($width) || !is_int($width)) {
            $width = 16;
        }

        if (!isset($height) || !is_int($height)) {
            $height = 12;
        }

        return HTML::image(OSCOM::link('Shop/public/third_party/flag-icon-css/flags/4x3/' . $this->get('image', $language_code) . '.svg', null, false), $this->get('name', $language_code), $width, $height);
    }

    public function getClientPreference()
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $client = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

            foreach ($client as $c) {
                foreach ($this->detectors as $code => $value) {
                    if (preg_match('/^(' . $value . ')(;q=[0-9]\\.[0-9])?$/i', $c) && $this->exists($code)) {
                        return $code;
                    }
                }
            }
        }

        return false;
    }

    public function getDef($key, $values = null, $scope = 'global')
    {
        if (isset($this->definitions[$scope][$key])) {
            $def = $this->definitions[$scope][$key];

            if (is_array($values) && !empty($values)) {
                $def = $this->parseDefinition($def, $values);
            }

            return $def;
        }

        return $key;
    }

    public static function parseDefinition($string, $values)
    {
        if (is_array($values) && !empty($values)) {
            $string = preg_replace_callback('/\{\{([A-Za-z0-9-_]+)\}\}/', function($matches) use ($values) {
                return isset($values[$matches[1]]) ? $values[$matches[1]] : $matches[1];
            }, $string);
        }

        return $string;
    }

    public function definitionsExist($group, $language_code = null)
    {
        $language_code = isset($language_code) && $this->exists($language_code) ? $language_code : $this->get('code');

        $site = OSCOM::getSite();

        if ((strpos($group, '/') !== false) && (preg_match('/^([A-Z][A-Za-z0-9-_]*)\/(.*)$/', $group, $matches) === 1) && OSCOM::siteExists($matches[1])) {
            $site = $matches[1];
            $group = $matches[2];
        }

        $pathname = OSCOM::getConfig('dir_root', $site) . 'includes/languages/' . $this->get('directory', $language_code) . '/' . $group;

        // legacy
        if (is_file($pathname . '.php')) {
            return true;
        }

        $pathname .= '.txt';

        if (is_file($pathname)) {
            return true;
        }

        if ($language_code != 'en') {
            return call_user_func([$this, __FUNCTION__], $group, 'en');
        }

        return false;
    }

    public function loadDefinitions($group, $language_code = null, $scope = null)
    {
        $language_code = isset($language_code) && $this->exists($language_code) ? $language_code : $this->get('code');

        if (!isset($scope)) {
            $scope = 'global';
        }

        $site = OSCOM::getSite();

        if ((strpos($group, '/') !== false) && (preg_match('/^([A-Z][A-Za-z0-9-_]*)\/(.*)$/', $group, $matches) === 1) && OSCOM::siteExists($matches[1])) {
            $site = $matches[1];
            $group = $matches[2];
        }

        $pathname = OSCOM::getConfig('dir_root', $site) . 'includes/languages/' . $this->get('directory', $language_code) . '/' . $group;

        // legacy
        if (is_file($pathname . '.php')) {
            include($pathname . '.php');
            return true;
        }

        $pathname .= '.txt';

        if ($language_code != 'en') {
            call_user_func([$this, __FUNCTION__], $group, 'en', $scope);
        }

        $defs = $this->getDefinitions($group, $language_code, $pathname);

        $this->injectDefinitions($defs, $scope);
    }

    public function getDefinitions($group, $language_code, $pathname)
    {
        $defs = [];

        $group_key = str_replace(['/', '\\'], '-', $group);

        if ($this->use_cache === false) {
            return $this->getDefinitionsFromFile($pathname);
        }

        $DefCache = new Cache('languages-defs-' . $group_key . '-lang' . $this->getId($language_code));

        if ($DefCache->exists()) {
            $defs = $DefCache->get();
        } else {
            $Qdefs = $this->db->get('languages_definitions', [
                'definition_key',
                'definition_value'
            ], [
                'languages_id' => $this->getId($language_code),
                'content_group' => $group_key
            ]);

            while ($Qdefs->fetch()) {
                $defs[$Qdefs->value('definition_key')] = $Qdefs->value('definition_value');
            }

            if (empty($defs)) {
                $defs = $this->getDefinitionsFromFile($pathname);

                foreach ($defs as $key => $value) {
                    $this->db->save('languages_definitions', [
                        'languages_id' => $this->getId($language_code),
                        'content_group' => $group_key,
                        'definition_key' => $key,
                        'definition_value' => $value
                    ]);
                }
            }

            $DefCache->save($defs);
        }

        return $defs;
    }

    public function getDefinitionsFromFile($filename)
    {
        $defs = [];

        if (is_file($filename)) {
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
        }

        return $defs;
    }

    public function injectDefinitions($defs, $scope)
    {
        if (isset($this->definitions[$scope])) {
            $this->definitions[$scope] = array_merge($this->definitions[$scope], $defs);
        } else {
            $this->definitions[$scope] = $defs;
        }
    }

    public function setUseCache($flag)
    {
        $this->use_cache = ($flag === true);
    }
}
