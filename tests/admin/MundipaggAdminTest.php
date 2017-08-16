<?php

namespace Tests;

class MundipaggAdminTest extends OpenCartTest
{
    public function testLoggedInCall()
    {
        $this->login('admin', 'admin');
        $response = $this->dispatchAction(
            'extension/extension/payment/install',
            'GET',
            ['extension'=>'mundipagg']
        );
        $this->assertRegExp('/uninstall.*extension=mundipagg/', $response->getOutput());
        $response = $this->dispatchAction(
            'extension/payment/mundipagg',
            'POST',
            [
                'payment_mundipagg_status' => 1,
                'payment_mundipagg_title' => 'MundiPagg Title',
                'payment_mundipagg_prod_secret_key' => getenv('PROD_SECRET_KEY'),
                'payment_mundipagg_test_secret_key' => getenv('TEST_SECRET_KEY'),
                'payment_mundipagg_test_mode' => 1,
                'payment_mundipagg_prod_public_key' => getenv('PROD_PUBLIC_KEY'),
                'payment_mundipagg_test_public_key' => getenv('TEST_PUBLIC_KEY'),
                'payment_mundipagg_log_enabled' => 1,
                'payment_mundipagg_credit_card_status' => 1,
                'payment_mundipagg_credit_card_payment_title' => 'Cartão de crédito',
                'payment_mundipagg_credit_card_invoice_name' => 'Borracharia',
                'payment_mundipagg_credit_card_operation' => 'Auth',
                'creditCard' => [
                    'Visa' => [
                        'is_enabled' => 1,
                        'installments_up_to' => 12,
                        'installments_without_interest' => 3,
                        'interest' => '2.99'
                    ],
                    'Mastercard' => [
                        'is_enabled' => 1,
                        'installments_up_to' => 12,
                        'installments_without_interest' => 3,
                        'interest' => '2.99'
                    ],
                    'Amex' => [
                        'is_enabled' => 1,
                        'installments_up_to' => 12,
                        'installments_without_interest' => 3,
                        'interest' => '2.99'
                    ],
                    'Diners' => [
                        'is_enabled' => 1,
                        'installments_up_to' => 12,
                        'installments_without_interest' => 3,
                        'interest' => '2.99'
                    ],
                    'Elo' => [
                        'is_enabled' => 1,
                        'installments_up_to' => 12,
                        'installments_without_interest' => 3,
                        'interest' => '2.99'
                    ],
                    'Hipercard' => [
                        'is_enabled' => 1,
                        'installments_up_to' => 12,
                        'installments_without_interest' => 3,
                        'interest' => '2.99'
                    ]
                ],
                'payment_mundipagg_boleto_status' => 1,
                'payment_mundipagg_boleto_title' => 'Boleto',
                'payment_mundipagg_boleto_name' => 'Borracharia',
                'payment_mundipagg_boleto_bank' => 341,
                'payment_mundipagg_boleto_due_date' => '',
                'payment_mundipagg_boleto_instructions' => str_repeat('bla ', 30)
            ]
        );
        $response = $this->dispatchAction('extension/extension/payment');
        $actual = $this->db->query('SELECT value FROM `' . DB_PREFIX . 'setting` where `key` = \'payment_mundipagg_status\' AND value = 1');
        $this->assertInstanceOf('stdClass', $actual);
    }

    public function tearDown()
    {
        $response = $this->dispatchAction(
            'extension/extension/payment/uninstall',
            'GET',
            ['extension'=>'mundipagg']
        );
    }
}