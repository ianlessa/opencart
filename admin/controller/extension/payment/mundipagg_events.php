<?php

use Mundipagg\Model\Order;

require_once DIR_SYSTEM . 'library/mundipagg/vendor/autoload.php';

/**
 * ControllerExtensionPaymentMundipaggEvents deal with module events
 *
 * The purpose of this class is to centralize methods related to important
 * events to the module
 *
 * @package Mundipagg
 *
 */
class ControllerExtensionPaymentMundipaggEvents extends Controller
{
    public function onOrderList(string $route, $data = array(), $template = null)
    {
        $cancel = [];
        $cancelCapture = [];

        $ids = array_map(function ($row) {
            return (int) $row['order_id'];
        }, $data['orders']);

        $Order = new Order($this);
        $orders = $Order->getOrders(
            [
                'order_id' => $ids,
                'order_status_id' => [1,15,2]
            ],
            [
                'order_status_id',
                'order_id'
            ]
        );

        foreach ($orders->rows as $order) {
            switch ($order['order_status_id']) {
                case 1: // I can capture, cancel
                    $cancelCapture[] = '#form-order table tbody tr ' .
                        'input[name="selected[]"][value=' . $order['order_id'] . ']';
                    break;
                case 15: // I can cancel
                case 2:
                    $cancel[] = '#form-order table tbody tr ' .
                        'input[name="selected[]"][value=' . $order['order_id'] . ']';
            }
        }

        $templateData['cancelCapture'] = implode(',', $cancelCapture);
        $templateData['cancel'] = implode(',', $cancel);
        $templateData['httpServer'] = HTTPS_SERVER;

        $footer  = $this->load->view('extension/payment/mundipagg/order_actions', $templateData);

        $data['footer'] = $footer . $data['footer'];

        if (isset($this->session->data['error_warning'])) {
            $data['error_warning'] = $this->session->data['error_warning'];
            unset($this->session->data['error_warning']);
        }

        $template = new Template($this->registry->get('config')->get('template_engine'));

        foreach ($data as $key => $value) {
            $template->set($key, $value);
        }

        return $template->render($this->registry->get('config')->get('template_directory') . $route, $this->registry->get('config')->get('template_cache'));
    }

    public function addModuleLink() {
        /**
         * @todo
         */
        return;
    }
}
