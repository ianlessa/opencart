<?php

namespace Mundipagg\Controller;

class Order
{
    private $openCart;

    public function __construct($openCart)
    {
        $this->openCart = $openCart;
    }
}
