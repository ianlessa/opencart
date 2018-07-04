<?php

namespace Mundipagg\Factories;

use Mundipagg\Aggregates\Template\DueValueObject;
use Mundipagg\Aggregates\Template\RepetitionValueObject;
use Mundipagg\Aggregates\Template\TemplateRoot;

class TemplateRootFactory
{
    /**
     * @param $postData
     * @return TemplateRoot
     * @throws \Exception
     */
    public function createFromPostData($postData)
    {
        $templateEntityFactory = new TemplateEntityFactory();
        $templateRoot = new TemplateRoot();

        $dueAt = new DueValueObject();
        $dueAt
            ->setType($postData['expiry_type'])
            ->setValue($postData['expiry_date'])
        ;

        foreach ($postData['intervals'] as $interval) {
            $repetition = new RepetitionValueObject();
            $repetition
                ->setFrequency($interval['frequency'])
                ->setIntervalType($interval['type'])
                ->setCycles($interval['cycles']);;


            if (isset($interval['discountValue'])) {
                $repetition
                    ->setDiscountValue($interval['discountValue'])
                    ->setDiscountType($interval['discountType']);
            }
            $templateRoot->addRepetition($repetition);
        }

        $templateRoot
            ->setTemplate($templateEntityFactory->createFromPostData($postData))
            ->setDueAt($dueAt)
        ;
        return $templateRoot;
    }

    public function createFromDBData($dbData) {
        $templateEntityFactory = new TemplateEntityFactory();
        $templateRoot = new TemplateRoot();

        $dueAt = new DueValueObject();
        $dueAt
            ->setType($dbData['due_type'])
            ->setValue($dbData['due_value'])
        ;
        $discountTypes = explode(',',$dbData['discount_type']);
        $discountValues = explode(',',$dbData['discount_value']);
        $intervalTypes = explode(',',$dbData['interval_type']);
        $frequencies = explode(',',$dbData['frequency']);
        $cycles = explode(',',$dbData['cycles']);

        foreach ($discountValues as $index => $discountValue) {
            $repetition = new RepetitionValueObject();
            $repetition
                ->setIntervalType($intervalTypes[$index])
                ->setFrequency($frequencies[$index])
                ->setCycles($cycles[$index]);

            if ($discountValue > 0) {
                $repetition
                    ->setDiscountType($discountTypes[$index])
                    ->setDiscountValue($discountValues[$index]);
            }

            $templateRoot->addRepetition($repetition);
        }

        $templateRoot
            ->setTemplate($templateEntityFactory->createFromDBData($dbData))
            ->setDueAt($dueAt);


        return $templateRoot;
    }
}