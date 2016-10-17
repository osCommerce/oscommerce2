<?php
/**
  * osCommerce Online Merchant
  *
  * @copyright Copyright (c) 2015 osCommerce; http://www.oscommerce.com
  * @license GPL; http://www.oscommerce.com/gpllicense.txt
  */

namespace OSC\OM;

abstract class PagesActionsAbstract implements \OSC\OM\PagesActionsInterface
{
    protected $page;
    protected $file;
    protected $is_rpc = false;

    public function __construct(\OSC\OM\PagesInterface $page)
    {
        $this->page = $page;

        if (isset($this->file)) {
            $this->page->setFile($this->file);
        }

        if (isset($this->page->app) && is_subclass_of($this->page->app, 'OSC\OM\AppAbstract')) {
            if ($this->page->app->hasDefinitionFile('Sites/Admin/Pages/' . $this->page->getCode() . '/Actions/' . implode('/', $this->page->getActionsRun()) . '.txt')) {
                $this->page->app->loadDefinitionFile('Sites/Admin/Pages/' . $this->page->getCode() . '/Actions/' . implode('/', $this->page->getActionsRun()) . '.txt');
            }
        }
    }

    public function isRPC()
    {
        return ($this->is_rpc === true);
    }
}
