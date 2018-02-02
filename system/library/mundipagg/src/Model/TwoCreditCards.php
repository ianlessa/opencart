<?php
namespace Mundipagg\Model;

use Mundipagg\Model\Creditcard;

class TwoCreditCards
{
    private $openCart;

    public function __construct($openCart)
    {
        $this->openCart = $openCart;
    }
}