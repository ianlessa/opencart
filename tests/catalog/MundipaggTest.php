<?php

namespace Tests;

class MundipaggCatalogTest extends OpenCartTest
{
    public function setUp()
    {
        parent::setUp();
        $this->loadModel('account/customer');
        $customer_id = $this->model_account_customer->addCustomer([
            'customer_group_id' => 1,
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'email' => 'customer@localhost',
            'telephone' => 'telephone',
            'password' => 'password',
            'custom_field' => array(),
        ]);
        $this->model_account_customer->editPassword('customer@localhost', 'password');
        $this->login('customer@localhost', 'password');
    }

    public function testAddCart()
    {
        $response = $this->dispatchAction('checkout/cart/add', 'POST', [
            'quantity'   => 1,
            'product_id' => 40
        ]);
        $this->assertRegExp('/Success: You have added/', $response->getOutput());
    }
    public function testBillingDetails()
    {
        $response = $this->dispatchAction('checkout/payment_address/save', 'POST', [
            'firstname' => 'José',
            'lastname' => 'Das Couves',
            'company' => '',
            'address_1' => 'Rua dos Bobos',
            'address_2' => '',
            'city' => 'Neverland',
            'postcode' => '171171171',
            'country_id' => '30',
            'zone_id' => '446'
        ]);
        $this->assertEquals('[]', $response->getOutput());
    }

    public function testDeliveryMethod()
    {
        $response = $this->dispatchAction( 'checkout/shipping_method/save', 'POST', [
            'shipping_method' => 'flat.flat',
            'comment' => '',
            'agree' => 1
        ]);
        $this->assertRegExp('/route=checkout/', $response->getOutput());
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