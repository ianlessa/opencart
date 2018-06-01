<?php
namespace Mundipagg\Controller;

use Mundipagg\Model\Order;

require_once DIR_SYSTEM . 'library/mundipagg/vendor/autoload.php';

class Events
{
    private $openCart;
    private $template;

    public function __construct($openCart, $template)
    {
        $this->openCart = $openCart;
        $this->template = $template;
    }

    public function __call($name, array $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        }

        return false;
    }

    /**
     * Show the Mundipagg's button in order list
     * @param array $data
     * @return mixed
     */
    public function orderListEntry($data)
    {
        $cancel = [];
        $cancelCapture = [];

        $ids = array_map(function ($row) {
            return (int) $row['order_id'];
        }, $data['orders']);

        $Order = new Order($this->openCart);
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

        $footer  = $this->openCart->load->view('extension/payment/mundipagg/order_actions', $templateData);

        $data['footer'] = $footer . $data['footer'];

        if (isset($this->openCart->session->data['error_warning'])) {
            $data['error_warning'] = $this->openCart->session->data['error_warning'];
            unset($this->openCart->session->data['error_warning']);
        }

        foreach ($data as $key => $value) {
            $this->template->set($key, $value);
        }

        return $this->template;
    }

    public function columnLeftEntry($data)
    {
        $mundipaggMenu = $this->getMundipaggMenu();

        array_unshift($data['menus'], $mundipaggMenu);

        foreach ($data as $key => $value) {
            $this->template->set($key, $value);
        }

        return $this->template;
    }

    private function getMundipaggMenu()
    {
        $htmlLogo =
            $this
                ->openCart
                ->load
                ->view('extension/payment/mundipagg/menu/mundipagg');

        $children[] = $this->getMenuChildren('Settings');
        $children[] = $this->getMenuChildren('Subscriptions');
        $children[] = $this->getMenuChildren('Plans');

        $mundipaggMenu = [
            'id'       => 'menu-mundipagg',
            'name'	   => $htmlLogo,
            'children' => $children
        ];

        return $mundipaggMenu;
    }

    private function getMenuChildren($name)
    {
        $path = 'extension/payment/mundipagg/' . strtolower($name);

        return [
            'name'  => $name,
            'href'  => $this->getLink($path)
        ];
    }

    private function getLink($path)
    {
        return $this->openCart->url->link(
            $path,
            'user_token=' . $this->openCart->session->data['user_token'],
            true
        );
    }
}