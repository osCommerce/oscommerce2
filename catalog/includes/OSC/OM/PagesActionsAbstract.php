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

    public function __construct(\OSC\OM\PagesInterface $page)
    {
        $this->page = $page;
    }
}
