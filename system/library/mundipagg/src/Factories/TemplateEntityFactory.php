<?php
namespace Mundipagg\Factories;

use Mundipagg\Aggregates\Template\TemplateEntity;

class TemplateEntityFactory
{

    /**
     * @param $postData
     * @return TemplateEntity
     */
    public function createFromPostData($postData)
    {
        $templateEntity = new TemplateEntity();
        $templateEntity
            ->setName($postData['name'])
            ->setDescription($postData['description'])
        ;

        if (isset($postData['single'])) {
            $templateEntity->setIsSingle($postData['single']);
        }

        if (isset($postData['trial'])) {
            $templateEntity->setTrial(intval($postData['trial']));
        }
        
        $paymentMethods =
            isset($postData['payment_method']) ? $postData['payment_method'] : [];
        foreach( $paymentMethods as $paymentMethod)
        {
            switch($paymentMethod)
            {
                case 'credit_card':
                    $templateEntity
                        ->setAcceptCreditCard(true)
                        ->setAllowInstallments($postData['allow_installment']);
                    break;
                case 'boleto':
                    $templateEntity->setAcceptBoleto(true);
                    break;
            }
        }

        return $templateEntity;
    }

    public function createFromDBData($dbData)
    {
        $templateEntity = new TemplateEntity();
        $templateEntity
            ->setId($dbData['id'])
            ->setName($dbData['name'])
            ->setDescription($dbData['description'])
            ->setIsSingle($dbData['is_single'])
            ->setAcceptBoleto($dbData['accept_boleto'])
            ->setAcceptCreditCard($dbData['accept_credit_card'])
            ->setAllowInstallments($dbData['allow_installments'])
            ->setTrial($dbData['trial'])
        ;
        return $templateEntity;
    }
}