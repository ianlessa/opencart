<?php
namespace Mundipagg\Model;


class SavedCreditcard
{
    private $openCart;

    public function __construct($openCart)
    {
        $this->openCart = $openCart;
    }

    public function saveCreditcard($mundipaggCustomerId, $cardData)
    {
        $sql =
            "INSERT INTO".
            "`" . DB_PREFIX . "mundipagg_creditcard` " .
            "(
                id,
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
        $query = $this->openCart->db->query($sql);
    }
}