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
    protected $is_rpc = false;

    public function __construct(\OSC\OM\PagesInterface $page)
    {
        $this->page = $page;
    }

    public function isRPC()
    {
        return ($this->is_rpc === true);
    }
}
