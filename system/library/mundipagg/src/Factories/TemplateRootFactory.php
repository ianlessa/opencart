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

        $dueAt = new DueValueObject();
        $dueAt
            ->setType($postData['expiry_type'])
            ->setValue($postData['expiry_date'])
        ;

        $repetitions = [];
        foreach ($postData['intervals'] as $interval) {
            $repetition = new RepetitionValueObject();
            $repetition
                ->setFrequency($interval['frequency'])
                ->setIntervalType($interval['type']);
            if (isset($interval['discountValue'])) {
                $repetition
                    ->setDiscountValue($interval['discountValue'])
                    ->setDiscountType($interval['discountType']);
            }
            $repetitions[] = $repetition;
        }

        $templateRoot = new TemplateRoot();
        $templateRoot
            ->setTemplate($templateEntityFactory->createFromPostData($postData))
            ->setDueAt($dueAt)
            ->setRepetitions($repetitions)
        ;
        return $templateRoot;
    }
}