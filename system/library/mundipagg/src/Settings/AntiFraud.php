<?php

namespace Mundipagg\Settings;

class AntiFraud
{
    private $openCart;

    public function __construct($openCart)
    {
        $this->openCart = $openCart;
    }

    public function isEnabled()
    {
        return $this->openCart->config->get('payment_mundipagg_antifraud_status') === '1';
    }

    public function getOrderMinVal()
    {
        return $this->openCart->config->get('payment_mundipagg_antifraud_minval');
    }

    public function getAllSettings()
    {
        return array(
            'antifraud_enabled' => $this->isEnabled(),
            'payment_mundipagg_antifraud_minval' => $this->getOrderMinVal(),
        );
    }
}