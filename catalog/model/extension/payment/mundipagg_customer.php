<?php

class ModelExtensionPaymentMundipaggCustomer extends Model
{
    
    private $mundiPaggCustomerId;
    private $customerId;

    public function getMundiPaggCustomerId()
    {
        return $this->mundiPaggCustomerId;
    }

    public function getCustomerId()
    {
        return $this->customerId;
    }

    public function setMundiPaggCustomerId($mundiPaggCustomerId)
    {
        $this->mundiPaggCustomerId = $mundiPaggCustomerId;
    }

    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }
    
    /**
     * Verify if an $opencartCustomerId have a
     * $mundipaggCustomerId
     * @return boolean
     */
    public function exists($opencartCustomerId)
    {
        $sql =
            "SELECT mundipagg_customer_id FROM " .
            "`" . DB_PREFIX . "mundipagg_customer`" .
            " WHERE customer_id = '" . $opencartCustomerId . "' ";

        $query = $this->db->query($sql);

        if ($query->num_rows) {
            return true;
        }

        return false;
    }
    
    public function get($opencartCustomerId)
    {
        $sql =
            "SELECT mundipagg_customer_id, customer_id FROM " .
            "`" . DB_PREFIX . "mundipagg_customer` " .
            " WHERE customer_id = '" . $opencartCustomerId . "' ";

        $query = $this->db->query($sql);

        if ($query->num_rows) {
            return $query->row;
        }

        return false;
    }
    
    public function create($opencartCustomerId, $mundipaggCustomerId)
    {
        $sql =
            "INSERT INTO `" . DB_PREFIX . "mundipagg_customer` " .
            "(customer_id, mundipagg_customer_id) " .
            "values('" .
                $opencartCustomerId .
            "', '"
                . $mundipaggCustomerId .
            "')";

        $query = $this->db->query($sql);

        if ($query) {
            return $query;
        }

        return false;
    }
}
