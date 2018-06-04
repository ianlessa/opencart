<?php

namespace Mundipagg\Controller;

class Charges
{
    private $openCart;

    public function __construct($openCart)
    {
        $this->openCart = $openCart;
    }

    public function preview()
    {
        if (empty($this->openCart->request->get['order_id'])) {
            return $this->openCart->redirect('sale/order');
        }

        $this->openCart->load->model('sale/order');
        $order_id = $this->openCart->request->get['order_id'];
        $order_info = $this->openCart->model_sale_order->getOrder($order_id);

        $status = '';
        if (isset($this->openCart->request->get['status'])) {
            $status = $this->openCart->request->get['status'];
        }

        $data['cancel'] = $this->openCart->url->link(
            'sale/order',
            'user_token=' . $this->openCart->session->data['user_token'] .
            '&route=sale/order',
            true);

        $data['text_order'] = sprintf('Order (#%s)', $order_id);
        $data['column_product'] = 'Product';
        $data['column_model'] = 'Model';
        $data['column_quantity'] = 'Quantity';
        $data['column_price'] = 'Unit Price';
        $data['column_total'] = 'Total';
        $data['charges'] = $this->openCart->getChargesData($order_info, $status);
        $data['products'] = $this->openCart->getDataProducts($order_info);
        $data['vouchers'] = $this->openCart->getVoucherData($order_info);
        $data['totals'] = $this->openCart->getTotalsData($order_info);
        $data['header'] = $this->openCart->load->controller('common/header');
        $data['column_left'] = $this->openCart->load->controller('common/column_left');
        $data['footer'] = $this->openCart->load->controller('common/footer');
        $data['heading_title'] = "Preview $status charge";

        $this->openCart->response->setOutput($this->openCart->load->view(
            'extension/payment/mundipagg_previewChangeCharge',
            $data
        ));
    }
}