<?php

class ModelExtensionPaymentMundipaggCustomer extends Model
{
    
    private $mundiPaggCustomerId;
    private $customerId;

    function getMundiPaggCustomerId()
    {
        return $this->mundiPaggCustomerId;
    }

    function getCustomerId()
    {
        return $this->customerId;
    }

    function setMundiPaggCustomerId($mundiPaggCustomerId)
    {
        $this->mundiPaggCustomerId = $mundiPaggCustomerId;
    }

    function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }
    
    /**
     * @return boolean
     */
    public function exists($customerId)
    {
        $this->setCustomerId($customerId);
        $sql = "SELECT mundipagg_customer_id FROM " . DB_PREFIX . "mundipagg_customer WHERE customer_id = '" . $this->getCustomerId() . "' ";
        $query = $this->db->query($sql);
        if ($query->num_rows) {
            return true;
        } else {
            return false;
        }
    }
    
    public function get($customerId)
    {
        $this->setCustomerId($customerId);
        $sql = "SELECT mundipagg_customer_id, customer_id FROM " . DB_PREFIX . "mundipagg_customer WHERE customer_id = '" . $this->getCustomerId() . "' ";
        $query = $this->db->query($sql);
        if ($query->num_rows) {
            return $query->row;
        } else {
            return false;
        }
    }
    
    public function create($customerId)
    {
        $this->setCustomerId($customerId);
        $sql = "SELECT mundipagg_customer_id, customer_id FROM " . DB_PREFIX . "mundipagg_customer WHERE customer_id = '" . $this->getCustomerId() . "' ";
        $query = $this->db->query($sql);
        if ($query) {
            return $query;
        } else {
            return false;
        }
    }
}
