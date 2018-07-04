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

        if (isset($postData['cycles'])) {
            $templateEntity->setCycles(intval($postData['cycles']));
        }

        if (isset($postData['trial'])) {
            $templateEntity->setTrial(intval($postData['trial']));
        }

        foreach($postData['payment_method'] as $paymentMethod)
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
            ->setCycles($dbData['cycles'])
            ->setTrial($dbData['trial'])
        ;
        return $templateEntity;
    }
}