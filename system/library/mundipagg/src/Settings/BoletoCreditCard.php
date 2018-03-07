<?php

namespace Mundipagg\Settings;

class BoletoCreditCard
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
        return $this->openCart->config->get('payment_mundipagg_boletoCreditCard_status');
    }

    public function getPaymentTitle()
    {
        return $this->openCart->config->get('payment_mundipagg_boletoCreditCard_title');
    }

    public function getTitle()
    {
        return $this->openCart->config->get('payment_mundipagg_boletoCreditCard_title');
    }

    public function getName()
    {
        return $this->openCart->config->get('payment_mundipagg_boleto_name');
    }

    public function getBank()
    {
        return $this->openCart->config->get('payment_mundipagg_boleto_bank');
    }

    public function getDueAt()
    {
        return $this->openCart->config->get('payment_mundipagg_boleto_due_date');
    }

    public function getInstructions()
    {
        return $this->openCart->config->get('payment_mundipagg_boleto_instructions');
    }

    public function getDueDate()
    {
        return date(
            "Y-m-d",
            mktime(
                0,
                0,
                0,
                date("m"),
                date("d") + $this->getDueAt(),
                date("Y")
            )
        );
    }

    public function getBoletoCreditCardPageInfo()
    {
        $this->openCart->load->language('extension/payment/mundipagg');

        return array(
            'boletoCreditCardStatus' => $this->getStatus(),
            'boletoCreditCardText' => $this->openCart->language->get('boletoCreditCard')
        );
    }

    public function getAllSettings()
    {
        return [
            'boletoCreditCard_enabled' => $this->getStatus(),
            'boletoCreditCard_title' => $this->getTitle(),
            'boletoCreditCard_name' => $this->getName(),
            'boletoCreditCard_bank' => $this->getBank(),
            'boletoCreditCard_instructions' => $this->getInstructions(),
            'boletoCreditCard_due_date' => $this->getDueAt()
        ];
    }
}