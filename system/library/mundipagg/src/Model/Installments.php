<?php
namespace Mundipagg\Model;

use Mundipagg\Log;
use Mundipagg\LogMessages;
use Mundipagg\Settings\CreditCard;

class Installments
{
    private $openCartModel;

    public function __construct($openCartModel)
    {
        $this->openCartModel = $openCartModel;
    }

    public function getInstallmentsFor($brand, $total)
    {
        if (!$this->doesBrandExist($brand) || !$this->isBrandEnabled($brand)) {
            return [];
        }

        $installmentsRules = $this->getInstallmentsRulesPerBrand($brand)[0];

        $maxInstallments = $installmentsRules['installments_up_to'];
        $maxInstallmentsWithoutInterest = $installmentsRules['installments_without_interest'];
        $interest = $installmentsRules['interest'];
        $incrementalInterest = $installmentsRules['incremental_interest'];

        $installmentsWithoutInterest = $this->getInstallmentsWithoutInterest(
            $total,
            $maxInstallmentsWithoutInterest
        );

        $installmentsWithInterest = $this->getInstallmentsWithInterest(
            $total,
            $maxInstallmentsWithoutInterest,
            $maxInstallments,
            $interest,
            $incrementalInterest
        );

        return array_merge($installmentsWithoutInterest, $installmentsWithInterest);
    }

    private function getInstallmentsRulesPerBrand($brandName)
    {
        $fields = '
            brand_name,
            is_enabled, 
            installments_up_to,
            installments_without_interest,
            interest,
            incremental_interest
        ';
        $selectStatement = 'SELECT ' . $fields . ' FROM';
        $tableName = DB_PREFIX .'mundipagg_payments';
        $condition = "WHERE brand_name = '" . $brandName . "'";

        $sql = $selectStatement . ' `' . $tableName . '` ' . $condition;
        $query = $this->openCartModel->query($sql);

        return $query->rows;
    }

    private function doesBrandExist($brandName)
    {
        $selectStatement = 'SELECT brand_name FROM';
        $tableName = DB_PREFIX .'mundipagg_payments';
        $condition = "WHERE brand_name = '" . $brandName . "'";

        $sql = $selectStatement . ' `' . $tableName . '` ' . $condition;
        $query = $this->openCartModel->query($sql);

        return $query->num_rows === 1;
    }

    private function isBrandEnabled($brandName)
    {
        $selectStatement = 'SELECT is_enabled FROM';
        $tableName = DB_PREFIX .'mundipagg_payments';
        $condition = "WHERE brand_name = '" . $brandName . "'";

        $sql = $selectStatement . ' `' . $tableName . '` ' . $condition;
        $query = $this->openCartModel->query($sql);

        return $query->row['is_enabled'] !== '0';
    }

    private function getInstallmentsWithoutInterest($total, $max)
    {
        $installments = array();
        for ($i = 0; $i < $max; $i++) {
            $installments[] = array(
                'amount' => $total / ($i + 1),
                'times' => $i + 1,
                'interest' => 0
            );
        }
        return $installments;
    }
    private function getInstallmentsWithInterest($total, $maxWithout, $max, $interest, $increment = 0)
    {
        $installments = array();
        for ($i = $maxWithout + 1; $i < $max; $i++) {
            $installments[] = array(
                'amount' => $total / ($i + 1),
                'times' => $i + 1,
                'interest' => $interest
            );
            $interest += $increment;
        }
        return $installments;
    }
}
