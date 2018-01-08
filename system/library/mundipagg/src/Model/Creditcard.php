<?php
namespace Mundipagg\Model;

use Mundipagg\Log;
use Mundipagg\LogMessages;

class Creditcard
{
    private $openCart;
    private $tableName = "mundipagg_creditcard";

    public function __construct($openCart)
    {
        $this->openCart = $openCart;
    }

    /**
     * @param string $mundipaggCustomerId
     * @param object $cardData
     * @param int $opencartOrderId
     */
    public function saveCreditcard($mundipaggCustomerId, $cardData, $opencartOrderId)
    {
        $sql =
            "INSERT INTO".
            "`" . DB_PREFIX . $this->tableName . "` " .
            "(
                mundipagg_creditcard_id,
                mundipagg_customer_id,
                first_six_digits,
                last_four_digits,
                brand,
                holder_name,
                exp_month,
                exp_year
            ) VALUES (".
            "'" . $cardData->id .  "'," .
            "'" . $mundipaggCustomerId . "', " .
            "'', " . // @todo save first six digits
            "'" . $cardData->lastFourDigits . "', " .
            "'" . $cardData->brand . "', " .
            "'" . $cardData->holderName . "', " .
            "'" . $cardData->expMonth . "', " .
            "'" . $cardData->expYear . "'" .
            ")"
        ;

        try {
            $this->openCart->db->query($sql);
        } catch (\Exception $exc) {
            Log::create()
                ->error(LogMessages::CANNOT_SAVE_CREDIT_CARD_DATA, __METHOD__)
                ->withOrderId($opencartOrderId);
        }
    }

    /**
     * Verify if credit card exists
     * @param int $creditCardId
     * @return bool
     */
    public function creditCardExists($mundipaggCreditcardId)
    {
        $sql =
            "SELECT mundipagg_creditcard_id FROM " .
            "`" . DB_PREFIX . $this->tableName . "` " .
            "WHERE mundipagg_creditcard_id = '" . $mundipaggCreditcardId . "'"
        ;

        try {
            $query = $this->openCart->db->query($sql);

            if ($query->num_rows === 1) {
                return true;
            }
            return false;

        } catch (\Exception $exc) {
            Log::create()
                ->error(LogMessages::CANNOT_SAVE_CREDIT_CARD_DATA, __METHOD__);
        }
    }

    /**
     * Get saved credit card
     * @param int $id Table primary key
     * @return bool
     */
    public function getCreditcardById($id)
    {
        $sql =
            "SELECT " .
            "id, " .
            "mundipagg_creditcard_id, " .
            "mundipagg_customer_id, " .
            "first_six_digits, " .
            "last_four_digits, " .
            "brand, " .
            "holder_name, " .
            "exp_month, " .
            "exp_year " .
            "FROM " .
            "`" . DB_PREFIX . $this->tableName . "` " .
            "WHERE id = '" . $id . "'"
        ;

        try {
            $query = $this->openCart->db->query($sql);

            if ($query->num_rows === 1) {
                return $query->row;
            }
            return false;

        } catch (\Exception $exc) {
            Log::create()
                ->error(LogMessages::CANNOT_GET_CREDIT_CARD_DATA, __METHOD__);
        }
    }

    /**
     * Return an array with all user credit cards
     * @param string $customerId MundiPagg customer id
     * @return array
     */
    public function getCreditcardsByCustomerId($customerId)
    {
        $sql = "
            SELECT " .
            "id, " .
            "mundipagg_creditcard_id," .
            "mundipagg_customer_id, " .
            "first_six_digits, " .
            "last_four_digits, " .
            "brand, " .
            "holder_name, " .
            "exp_month, " .
            "exp_year " .
            "FROM " .
            "`". DB_PREFIX . "mundipagg_creditcard` " .
            "WHERE mundipagg_customer_id = '" . $customerId . "'" .
            " ORDER BY brand, last_four_digits, id
        ";

        $query =  $this->openCart->db->query($sql);

        return $query->rows;
    }

    public function deleteCreditcard($cardId)
    {
        $sql = "DELETE FROM " . DB_PREFIX . $this->tableName . " WHERE id = $cardId";
        try {
            $this->openCart->db->query($sql);
        } catch (\Exception $exc) {
            Log::create()
                ->error(LogMessages::CANNOT_DELETE_CREDIT_CARD_DATA, __METHOD__);
        }
    }
}