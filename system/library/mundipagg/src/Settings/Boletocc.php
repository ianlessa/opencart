<?php

namespace Mundipagg\Settings;

class Boletocc
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
        return $this->openCart->config->get('payment_mundipagg_boletocc_status');
    }

    public function getPaymentTitle()
    {
        return $this->openCart->config->get('payment_mundipagg_boletocc_title');
    }

    public function getTitle()
    {
        return $this->openCart->config->get('payment_mundipagg_boletocc_title');
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

    public function getBoletoccPageInfo()
    {
        $this->openCart->load->language('extension/payment/mundipagg');

        return array(
            'boletoccStatus' => $this->getStatus(),
            'boletoccText' => $this->openCart->language->get('boletocc')
        );
    }

    public function getAllSettings()
    {
        return array(
            'boletocc_enabled' => $this->getStatus(),
            'boletocc_title' => $this->getTitle(),
            'boletocc_name' => $this->getName(),
            'boletocc_bank' => $this->getBank(),
            'boletocc_instructions' => $this->getInstructions(),
            'boletocc_due_date' => $this->getDueAt()
        );
    }
}