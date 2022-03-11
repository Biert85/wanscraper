<?php

namespace App\Lib\SimplePie;

class SimplePie extends \SimplePie
{
    public function __construct()
    {
        parent::__construct();

        $this->set_item_class(Item::class);
    }
}
