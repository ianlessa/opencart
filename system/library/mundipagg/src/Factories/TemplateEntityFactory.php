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
}