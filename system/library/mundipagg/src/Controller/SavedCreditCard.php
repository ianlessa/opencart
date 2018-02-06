<?php

namespace Mundipagg\Controller;

use Mundipagg\Model\Creditcard;

class SavedCreditCard
{
    private $openCart;

    public function __construct($openCart)
    {
        $this->openCart = $openCart;
    }

    public function getSavedCreditcardList($opencartCustomerId)
    {
        $savedCreditCards = new Creditcard($this->openCart);
        $this->openCart->load->model('extension/payment/mundipagg_customer');

        $customer =$this->
            openCart->model_extension_payment_mundipagg_customer->
            get($opencartCustomerId);

        return $savedCreditCards
            ->getCreditcardsByCustomerId(
                $customer['mundipagg_customer_id']
            );
    }
}
