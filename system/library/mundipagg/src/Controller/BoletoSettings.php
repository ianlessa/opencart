<?php

namespace Mundipagg\Controller;

class BoletoSettings
{
    private $openCart;

    public function __construct($openCart)
    {
        $this->openCart = $openCart;
    }

    public function isEnabled()
    {
        return $this->getStatus() === '1';
    }

    public function getStatus()
    {
        return $this->openCart->config->get('payment_mundipagg_boleto_status');
    }

    public function getTitle()
    {
        return $this->openCart->config->get('payment_mundipagg_boleto_title');
    }

    public function getName()
    {
        return $this->openCart->config->get('payment_mundipagg_boleto_name');
    }

    public function getBank()
    {
        return $this->openCart->config->get('payment_mundipagg_boleto_bank');
    }

    public function getInstructions()
    {
        return $this->openCart->config->get('payment_mundipagg_boleto_instructions');
    }

    public function getDueDate()
    {
        return $this->openCart->config->get('payment_mundipagg_boleto_due_date');
    }

    public function getAllSettings()
    {
        return array(
            'boleto_enabled' => $this->getStatus(),
            'boleto_title' => $this->getTitle(),
            'boleto_name' => $this->getName(),
            'boleto_bank' => $this->getBank(),
            'boleto_instructions' => $this->getInstructions(),
            'boleto_due_date' => $this->getDueDate()
        );
    }
}
