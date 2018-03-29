<?php
namespace Mundipagg\Controller;

use Mundipagg\Model\Order;

require_once DIR_SYSTEM . 'library/mundipagg/vendor/autoload.php';
require_once DIR_SYSTEM . 'library/template.php';

class Events
{
    private $opencart;


    public function __construct($opencart)
    {
        $this->opencart = $opencart;
    }

    public function addActionsToOrderList(string $route, $data = array(), $template = null)
    {
        $cancel = [];
        $cancelCapture = [];

        $ids = array_map(function ($row) {
            return (int) $row['order_id'];
        }, $data['orders']);

        $Order = new Order($this->opencart);
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

        $footer =  $this->opencart->load->view('extension/payment/mundipagg/order_actions', $templateData);

        $data['footer'] = $footer . $data['footer'];

        if (isset($this->opencart->data['error_warning'])) {
            $data['error_warning'] = $this->opencart->session->data['error_warning'];
            unset($this->opencart->session->data['error_warning']);
        }


        $template = new \Template('‌twig');

        foreach ($data as $key => $value) {
            $template->set($key, $value);
        }

        return $template->render('' . $route, 0);
    }

}