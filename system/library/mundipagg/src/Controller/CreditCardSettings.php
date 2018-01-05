<?php

namespace Mundipagg\Controller;

class CreditCardSettings
{
    private $openCart;

    public function __construct($openCart)
    {
        $this->openCart = $openCart;
    }

    public function getStatus()
    {
        return $this->openCart->config->get('payment_mundipagg_credit_card_status');
    }

    public function isCreditCardEnabled()
    {
        return $this->getStatus() == '1';
    }

    public function getPaymentTitle()
    {
        return $this->openCart->config->get('payment_mundipagg_credit_card_payment_title');
    }

    public function getInvoiceName()
    {
        return $this->openCart->config->get('payment_mundipagg_credit_card_invoice_name');
    }

    public function getOperation()
    {
        return $this->openCart->config->get('payment_mundipagg_credit_card_operation');
    }

    public function getAllSettings()
    {
        return array(
            'credit_card_status' => $this->getStatus(),
            'credit_card_payment_title' => $this->getPaymentTitle(),
            'credit_card_invoice_name' => $this->getInvoiceName(),
            'credit_card_operation' => $this->getOperation(),
            'credit_card_is_saved_enabled' => $this->isSavedCreditcardEnabled()
        );
    }

    public function getPaymentInformation()
    {
        $sql = "SELECT * from `". DB_PREFIX ."mundipagg_payments`";
        $query = $this->openCart->db->query($sql);

        return $query->rows;
    }

    public function isSavedCreditcardEnabled() {
        return $this->openCart->config->get('payment_mundipagg_credit_card_is_saved_enabled');
    }
}
