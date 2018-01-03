<?php
/**
 * ControllerExtensionPaymentMundipaggCreditCard
 *
 * @package Mundipagg
 */
class ControllerExtensionPaymentMundipaggCreditCard extends Controller
{
    /**
     * Method to load credit card view
     *
     * @return mixed
     */
    public function index()
    {
        $data = array();


        return $this->load->view('extension/payment/mundipagg', $data);
    }
}
