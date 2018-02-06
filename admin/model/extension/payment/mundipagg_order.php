<?php

class ModelExtensionPaymentMundipaggOrder extends Model
{
    public function addOrderHistory($order_id, $order_status_id, $comment = '', $notify = false, $override = false)
    {
        $sql = "INSERT INTO " . DB_PREFIX . "order_history SET " .
            "order_id = '" . (int)$order_id . "', " .
            "order_status_id = '" . (int)$order_status_id . "', " .
            "notify = '" . (int)$notify . "', " .
            "comment = '" . $this->db->escape($comment) . "', " .
            "date_added = NOW()";
        $this->db->query($sql);
    }
}