<?php

namespace Tests;

class MundipaggCatalogTest extends OpenCartTest
{
    public function setUp()
    {
        parent::setUp();
        $response = $this->dispatchAction(
            'account/register',
            'GET',
            [
                'customer_group_id' => 1,
                'firstname' => 'José',
                'lastname' => 'das Couves',
                'email' => 'jose.das.couves@mundipagg.com',
                'telephone' => '61 171171171',
                'password' => 'teste',
                'confirm' => 'teste',
                'newsletter' => 0,
                'agree' => 1
            ]
        );
    }

    public function testTrue()
    {
        $this->assertTrue(true);
    }

    public function _tearDown()
    {
        $response = $this->dispatchAction(
            'customer/customer/delete',
            'POST',
            ['selected'=>[1]]
        );
    }
}