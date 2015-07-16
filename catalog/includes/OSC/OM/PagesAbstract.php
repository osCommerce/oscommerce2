<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

use OSC\OM\HTML;
use OSC\OM\OSCOM;

abstract class PagesAbstract implements \OSC\OM\PagesInterface
{
    protected $code;
    protected $file = 'main.php';
    protected $site;
    protected $actions_run = [];
    protected $ignored_actions = [];
    protected $is_rpc = false;

    final public function __construct(\OSC\OM\SitesInterface $site)
    {
        $this->code = (new \ReflectionClass($this))->getShortName();
        $this->site = $site;

        $this->init();
    }

    protected function init()
    {
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getFile()
    {
        if (isset($this->file)) {
            return OSCOM::BASE_DIR . (new \ReflectionClass($this))->getNamespaceName() . '/templates/' . $this->file;
        }
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function runActions()
    {
        $furious_pete = [];

        if (count($_GET) > $this->site->actions_index) {
            $furious_pete = array_keys(array_slice($_GET, $this->site->actions_index, null, true));
        }

        foreach ($furious_pete as $action) {
            $action = HTML::sanitize(basename($action));

            $this->actions_run[] = $action;

// get namespace from class name
            $class = (new \ReflectionClass($this))->getNamespaceName() . '\\Actions\\' . implode('\\', $this->actions_run);

            if (!in_array($action, $this->ignored_actions) && $this->actionExists($class)) {
                $action = new $class($this);
                $action->execute();
            } else {
                array_pop($this->actions_run);

                break;
            }
        }
    }

    public function actionExists($action)
    {
        if (class_exists($action)) {
            if (is_subclass_of($action, 'OSC\OM\PagesActionsInterface')) {
                return true;
            } else {
                trigger_error('OSC\OM\PagesAbstract::actionExists() - ' . $action . ': Action does not implement OSC\OM\PagesActionInterface and cannot be loaded.');
            }
        }

        return false;
    }

    public function isRPC()
    {
        return $this->is_rpc;
    }

    public function setRPC($boolean)
    {
        $this->is_rpc = ($boolean === true);
    }
}
