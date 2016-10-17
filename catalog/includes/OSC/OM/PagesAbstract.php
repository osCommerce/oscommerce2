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
    public $data = [];

    protected $code;
    protected $file = 'main.php';
    protected $use_site_template = true;
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
            return dirname(OSCOM::BASE_DIR) . '/' . str_replace('\\', '/', (new \ReflectionClass($this))->getNamespaceName()) . '/templates/' . $this->file;
        }
    }

    public function useSiteTemplate()
    {
        return $this->use_site_template;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function isActionRequest()
    {
        $furious_pete = [];

        if (count($_GET) > $this->site->actions_index) {
            $furious_pete = array_keys(array_slice($_GET, $this->site->actions_index, null, true));
        }

        if (!empty($furious_pete)) {
            $action = HTML::sanitize(basename($furious_pete[0]));

            if (!in_array($action, $this->ignored_actions) && $this->actionExists($action)) {
                return true;
            }
        }

        return false;
    }

    public function runAction($actions)
    {
        if (!is_array($actions)) {
            $actions = [
                $actions
            ];
        }

        $run = [];

        foreach ($actions as $action) {
            $run[] = $action;

            if ($this->actionExists($run)) {
                $this->actions_run[] = $action;

                $class = $this->getActionClassName($run);

                $action = new $class($this);

                $action->execute();

                if ($action->isRPC()) {
                    $this->is_rpc = true;
                }
            } else {
                break;
            }
        }
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

            if (!in_array($action, $this->ignored_actions) && $this->actionExists($this->actions_run)) {
                $class = $this->getActionClassName($this->actions_run);

                $action = new $class($this);

                $action->execute();

                if ($action->isRPC()) {
                    $this->is_rpc = true;
                }
            } else {
                array_pop($this->actions_run);

                break;
            }
        }
    }

    public function actionExists($action)
    {
        if (!is_array($action)) {
            $action = [
                $action
            ];
        }

        $class = $this->getActionClassName($action);

        if (class_exists($class)) {
            if (is_subclass_of($class, 'OSC\OM\PagesActionsInterface')) {
                return true;
            } else {
                trigger_error('OSC\OM\PagesAbstract::actionExists() - ' . implode('\\', $action) . ': Action does not implement OSC\OM\PagesActionInterface and cannot be loaded.');
            }
        }

        return false;
    }

    public function getActionsRun()
    {
        return $this->actions_run;
    }

    public function isRPC()
    {
        return ($this->is_rpc === true);
    }

    protected function getActionClassName($action)
    {
        if (!is_array($action)) {
            $action = [
                $action
            ];
        }

        return (new \ReflectionClass($this))->getNamespaceName() . '\\Actions\\' . implode('\\', $action);
    }
}
