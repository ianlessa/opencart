<?php

use Mundipagg\Log;

class ModelExtensionPaymentMundipaggOrderdataUpdate extends Model
{
    private function updateOrderAmount($orderId, $sql)
    {
        try {
            $this->db->query($sql);

            return true;
        } catch (Exception $e) {
            Log::create()
                ->error(LogMessages::UNABLE_TO_UPDATE_ORDER_AMOUNT, __METHOD__)
                ->withOrderId($orderId)
                ->withLineNumber(__LINE__)
                ->withQuery($sql);
        }
    }

    public function updateOrderAmountInOrder($orderId, $orderAmount)
    {
        $sql = "UPDATE `" . DB_PREFIX . "order` " .
            "set `total` = '" . $orderAmount . "' " .
            "WHERE `order_id` = '" . $orderId . "'";
        $this->updateOrderAmount($orderId, $sql);
    }


    public function updateOrderAmountInOrderTotals($orderId, $orderAmount)
    {
        $sql = "UPDATE `" . DB_PREFIX . "order_total` " .
            "set `value` = '" . $orderAmount . "' " .
            "WHERE `order_id` = '" . $orderId . "' " .
            "AND code = 'total' ";
        $this->updateOrderAmount($orderId, $sql);
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

        $this->updateOrderAmount($orderId, $sql);
    }



}