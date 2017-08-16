<?php

namespace Mundipagg\Controller;

class Boleto
{
    private $openCart;

    public function __construct($openCart)
    {
        $this->openCart = $openCart;
    }

    public function isEnabled()
    {
        return $this->openCart->config->get('payment_mundipagg_boleto_status') === '1';
    }

    public function getStatus()
    {
        return $this->openCart->config->get('payment_mundipagg_boleto_status');
    }

    public function getPaymentTitle()
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

    public function getBoletoPageInfo()
    {
        $this->openCart->load->language('extension/payment/mundipagg');

        return array(
            'boletoStatus' => $this->getStatus(),
            'boletoText' => $this->openCart->language->get('boleto')

        );
    }
}
