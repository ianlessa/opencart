<?php
namespace Mundipagg\Model;

use Mundipagg\Log;

class Order
{
    private $openCart;
    public function __construct($openCart)
    {
        $this->openCart = $openCart;
    }

    public function saveCharge(array $data)
    {
        $charge = $this->getCharge($data['opencart_id'], $data['charge_id']);
        if ($charge->num_rows) {
            $this->updateCharge($data);
        } else {
            $this->insertCharge($data);
        }
    }

    public function getOrders($data, $fields)
    {
        $where = [];
        if (isset($data['ids'])) {
            $where[]= 'order_id IN(' . implode(',', $data['ids']) . ')';
        }
        if (isset($data['order_status_id'])) {
            $where[]= 'order_status_id IN(' . implode(',', $data['order_status_id']) . ')';
        }
        if ($where) {
            return $this->openCart->db->query(
                'SELECT ' . implode(",\n         ", $fields) .
                '  FROM `' . DB_PREFIX . "order`\n" .
                ' WHERE ' . implode(' AND ', $where)
            );
        }
    }

    public function getCharge($opencart_id, $charge_id = null)
    {
        $charge = $this->openCart->db->query(
            "SELECT charge_id, \n".
            "       payment_method,\n".
            "       status,\n".
            "       paid_amount,\n".
            "       amount,\n".
            "       opencart_id AS order_id,\n".
            '       CASE WHEN (status != "canceled" OR status = "paid")'."\n".
            "            THEN 1\n".
            "            ELSE 0\n".
            '             END AS can_cancel,'."\n".
            '       CASE WHEN status != "canceled"'."\n".
            '             AND status != "paid"'."\n".
            "            THEN 1\n".
            "            ELSE 0\n".
            '             END AS can_capture'."\n".
            '  FROM `' . DB_PREFIX . "mundipagg_charge`\n".
            ' WHERE opencart_id = ' . $opencart_id .
            ($charge_id ? ' AND charge_id = "' . $charge_id . '"' : '')
        );
        return $charge;
    }

    private function updateCharge(array $data)
    {
        $query = 'UPDATE `' . DB_PREFIX . 'mundipagg_charge` SET ';
        $fields = array();
        foreach ($data as $key => $value) {
            $fields[]= ' ' . $key . ' = "' . $value . '"';
        }
        $query.= implode(', ', $fields) .
            ' WHERE opencart_id = ' . $data['opencart_id'] .
            '   AND charge_id = "' . $data['charge_id'] . '"';
        $this->openCart->db->query($query);
    }

    private function insertCharge(array $data)
    {
        $this->openCart->db->query(
            'INSERT INTO `' . DB_PREFIX . 'mundipagg_charge` ' .
            '(' . implode(',', array_keys($data)) . ') ' .
            'VALUES ("' . implode('", "', $data) . '"'.
            ');'
        );
    }

    public function updateOrderStatus($order_id, $order_status_id)
    {
        $this->openCart->db->query(
            "UPDATE `" . DB_PREFIX . "order`
                        SET order_status_id = " . $order_status_id . "
                        WHERE order_id = ". $order_id
        );
    }

    public function updateAmount($amount, $orderId)
    {
        $sql = "UPDATE `" . DB_PREFIX . "order` " .
            "set `total` = '" . $amount . "' " .
            "WHERE `order_id` = '" . $orderId . "'";

        try {
            $this->openCart->db->query($sql);
        } catch (\Exception $e) {
            Log::create()
                ->error(LogMessages::UNABLE_TO_UPDATE_ORDER_AMOUNT, __METHOD__)
                ->withOrderId($orderId)
                ->withLineNumber(__LINE__)
                ->withQuery($sql);
        }
    }

    public function updateAmountInOrderTotals($orderId, $orderAmount)
    {
        $sql = "UPDATE `" . DB_PREFIX . "order_total` " .
            "set `value` = '" . $orderAmount . "' " .
            "WHERE `order_id` = '" . $orderId . "' " .
            "AND code = 'total' ";

        try {
            $this->openCart->db->query($sql);
        } catch (\Exception $e) {
            Log::create()
                ->error(LogMessages::UNABLE_TO_UPDATE_ORDER_AMOUNT, __METHOD__)
                ->withOrderId($orderId)
                ->withLineNumber(__LINE__)
                ->withQuery($sql);
        }
    }

    public function insertInterestInOrderTotals($orderId, $interestAmount)
    {
        $sql = "INSERT INTO `" . DB_PREFIX . "order_total` " .
            "(" .
            "`order_id`," .
            " `code`," .
            "`title`," .
            "`value`," .
            "`sort_order`" .
            ")".
            " VALUES (" .
            "'" . $orderId . "'," .
            "'mundipagg_interest'," .
            "'Juros'," .
            "'" . $interestAmount . "'," .
            "'3'" .
            ")";

        try {
            $this->openCart->db->query($sql);
        } catch (\Exception $e) {
            Log::create()
                ->error(LogMessages::UNABLE_TO_UPDATE_ORDER_AMOUNT, __METHOD__)
                ->withOrderId($orderId)
                ->withLineNumber(__LINE__)
                ->withQuery($sql);
        }
    }
}
