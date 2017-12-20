<?php

namespace Mundipagg;

use MundiAPILib\MundiAPIClient;
use MundiAPILib\Models\CreateCustomerRequest;

class Customer
{
    private $details;
    private $openCart;

    public function __construct($details, $openCart)
    {
        $this->details = $details;
        $this->openCart = $openCart;
    }

    private function getSecretKey()
    {
        $mundipaggSettings = $this->openCart->setting->getSetting('payment_mundipagg');
        
        if ($mundipaggSettings['payment_mundipagg_test_mode']) {
            return $mundipaggSettings['payment_mundipagg_test_secret_key'];
        }
        
        return $mundipaggSettings['payment_mundipagg_prod_secret_key'];
    }

    private function getApiPassword()
    {
        return '';
    }
    
    private function getOrderData()
    {
        $this->openCart->load->model('checkout/order');
        
        return $this->openCart->model_checkout_order->getOrder(
            $this->openCart->session->data['order_id']
        );
    }
    
    private function getCreateAddressRequest()
    {
        $orderData = $this->getOrderData();
        
        return new CreateAddressRequest(
            //Street
            $orderData['payment_address_1'],
            //Number
            $orderData['payment_custom_field'][1],
            //Zipcode
            preg_replace('/\D/', '', $orderData['payment_postcode']),
            //Neighborhood
            $orderData['payment_address_2'],
            //City
            $orderData['payment_city'],
            //State
            $orderData['payment_zone_code'],
            //Country
            $orderData['shipping_iso_code_2'],
            //Complement
            $orderData['payment_custom_field'][2],
            //Metadata
            null
        );
    }
    
    
    private function getCreateCustomerRequest()
    {
        $orderData = $this->getOrderData();
        
        return array(
            'name'     => $orderData['payment_firstname']." ".$orderData['payment_lastname'],
            'email'    => $orderData['email'],
            'phone'    => $orderData['telephone'],
            'document' => null,
            'type'     => "",
            'address'   => $this->getCreateAddressRequest(),
            'metadata' => null
        );
    }
    
    private function isMPCustomer()
    {
        $this->openCart->load->model('extension/payment/mundipagg_customer');
        
        return $this->openCart->model_extension_payment_mundipagg_customer->exists(
            $this->openCart->customer->getId()
        );
    }
    
    public function createMPCustomer()
    {
        if ($this->isMPCustomer()) {
            return true;
        }

        \Unirest\Request::verifyPeer(false);
        
        $client = new MundiAPIClient(
            $this->getSecretKey(),
            $this->getApiPassword()
        );
        
        $response = $client->getCustomers($this->getCreateCustomerRequest());
        $customer = json_decode($response);
    }
}
