<?php

/**
 * ModelExtensionPaymentMundipaggEvents
 *
 */
class ModelExtensionPaymentMundipaggEvents extends Model
{
    /**
     * Save new costumer
     */
    public function saveNewCustomer($mPCustomerId, $oCCustomerId)
    {
        $this->db->query(
            "INSERT INTO `" . DB_PREFIX . "mundipagg_customer`" .
            "(customer_id, mundipagg_customer_id) VALUES (" .
                "'${oCCustomerId}'" . ', ' .
                "'${mPCustomerId}'" .
            ");"
        );
    }

    public function getMPCustomerIdFromOC($oCCustomerId)
    {
        $sql = "SELECT * FROM " .
            DB_PREFIX . "mundipagg_customer WHERE customer_id = ${oCCustomerId};";

        $query = $this->db->query($sql);
        
        if ($query-> num_rows > 0) {
            return $query->row['mundipagg_customer_id'];
        }

        return false;
    }
}
