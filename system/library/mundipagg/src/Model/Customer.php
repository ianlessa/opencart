<?php
namespace Mundipagg\Model;

use Mundipagg\Log;
use Mundipagg\LogMessages;

class Customer
{
    private $openCart;
    private $tableName = 'mundipagg_customer';

    public function __construct($openCart)
    {
        $this->openCart = $openCart;
    }

    public function getByOpencartId($opencartCustomerId)
    {
        $sql =
            "SELECT " .
            "customer_id, " .
            "mundipagg_customer_id " .
            "FROM " .
            "`" . DB_PREFIX . $this->tableName . "` " .
            "WHERE customer_id = '" . $opencartCustomerId . "'"
        ;

        try {
            $query = $this->openCart->db->query($sql);

            if ($query->num_rows === 1) {
                return $query->row;
            }
            return false;

        } catch (\Exception $exc) {
            Log::create()
                ->error(LogMessages::CANNOT_GET_CUSTOMER_DATA, __METHOD__);
        }
    }
}