<?php

use Mundipagg\Log;

class ModelExtensionPaymentMundipaggCreditCard extends Model
{
    /**
     * Get credit cards images from json
     *
     * @param Strin $brandName Credit card brand name
     * @return Object
     */
    public function getCreditCardBrands($brandName = null)
    {
        try {
            $json = json_decode(
                file_get_contents(
                    'http://embeddables.eastus2.cloudapp.azure.com/payment/bank_info.json'
                )
            );
            if ($brandName) {
                $brandName = ucfirst($brandName);
                return $json->brands->$brandName;
            }
            return $json->brands;
        } catch (Exception $exc) {
        }
    }

    public function getActiveCreditCards()
    {
        $sql = "SELECT * FROM
               `". DB_PREFIX ."mundipagg_payments`
               WHERE `is_enabled` = 1
                ";
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    private function getInstallmentsWithInterest($creditCardInfo, $amount)
    {
        $installments = [];
        $interestAmount = number_format($amount * ((double)$creditCardInfo['interest']/100), 2, '.', ',');
        $amountWithInterest = number_format($amount + $interestAmount, 2, '.', ',');
        
        for ($i = $creditCardInfo['installments_without_interest']; $i < $creditCardInfo['installments_up_to']; $i++) {
            $installments[$i] = [
                'installments' => $i + 1,
                'amount' => number_format($amountWithInterest/($i + 1), 2, '.', ','),
                'interest' => $creditCardInfo['interest'],
                'total' => $amountWithInterest
            ];
        }
        
        return $installments;
    }
    
    private function getInstallmentsWithoutInterest($creditCardInfo, $amount)
    {
        $installments = [];
        
        for ($i = 0; $i < $creditCardInfo['installments_without_interest']; $i++) {
            $installments[$i] = [
                'installments' => $i + 1,
                'amount' => number_format($amount/($i + 1), 2, '.', ','),
                'interest' => 0,
                'total' => $amount
            ];
        }
        
        return $installments;
    }
    
    private function getInstallmentsPerCreditCard($creditCardInfo, $amountInCents)
    {
        return array_merge(
            $this->getInstallmentsWithoutInterest($creditCardInfo, $amountInCents),
            $this->getInstallmentsWithInterest($creditCardInfo, $amountInCents)
        );
    }
    
    public function getInstallmentsInfo($order)
    {
        $installments = [];
        
        $amount = (double)$order['total'];
        $creditCards = $this->getActiveCreditCards();
        
        foreach ($creditCards as $creditCard) {
            $installments[$creditCard['brand_name']] = $this->getInstallmentsPerCreditCard(
                $creditCard,
                $amount
            );
        }
        
        return $installments;
    }

    public function saveMundiOrder($mundiOrderId, $openCartOrderId)
    {
        $insertOrder = 'INSERT INTO `' . DB_PREFIX . 'mundipagg_order` ' .
            '(opencart_id, mundipagg_id) ' .
            "VALUES ('" . $openCartOrderId . "', '" . $mundiOrderId . "');";

        try {
            $this->db->query($insertOrder);
        } catch (Exception $e) {
            Log::create()
                ->error(LogMessages::UNABLE_TO_CREATE_MUNDI_ORDER, __METHOD__)
                ->withOrderId($openCartOrderId)
                ->withMundiOrderId($mundiOrderId)
                ->withLineNumber(__LINE__)
                ->withBackTraceInfo()
                ->withQuery($insertOrder);
        }
    }
}
