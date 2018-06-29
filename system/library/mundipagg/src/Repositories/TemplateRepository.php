<?php

namespace Mundipagg\Repositories;

use Mundipagg\Aggregates\Template\RepetitionValueObject;
use Mundipagg\Aggregates\Template\TemplateRoot;

class TemplateRepository extends AbstractRep
{

    /**
     * @param TemplateRoot $templateRoot
     */
    protected function create(&$templateRoot)
    {
        /** @var \DB $db */
        $db = $this->openCart->db;

        $query = ("
            INSERT INTO `" . DB_PREFIX . "mundipagg_template` (
                `is_single`,
                `name`,
                `description`,
                `accept_credit_card`,
                `accept_boleto`,
                `allow_installments`
            ) VALUES (
                " . ($templateRoot->getTemplate()->isSingle()?1:0) . ",
                '" . $templateRoot->getTemplate()->getName() . "',
                '" . $templateRoot->getTemplate()->getDescription() . "',
                " . ($templateRoot->getTemplate()->isAcceptCreditCard()?1:0) . ",
                " . ($templateRoot->getTemplate()->isAcceptBoleto()?1:0) . ",
                " . ($templateRoot->getTemplate()->isAllowInstallments()?1:0) . "
            )
        ");

        $db->query($query);
        $templateRoot->getTemplate()->setId($db->getLastId());

        $this->createTemplateRepetitions($templateRoot);
        $this->createTemplateDue($templateRoot);

        return true;
    }

    /**
     * @param TemplateRoot $templateRoot
     */
    protected function update(&$templateRoot)
    {
        // TODO: Implement update() method.
    }
    public function delete($template)
    {

    }

    public function find($templateId)
    {

    }

    public function listEntities($limit = 0)
    {

    }

    protected function createTemplateRepetitions($templateRoot)
    {
        $query = "
            INSERT INTO `" . DB_PREFIX . "mundipagg_template_repetition` (
                `template_id`,
                `frequency`,
                `interval_type`,
                `discount_type`,
                `discount_value`
            ) VALUES 
        ";

        /** @var RepetitionValueObject $repetition */
        foreach ($templateRoot->getRepetitions() as $repetition) {
            $query .= "(
                ". $templateRoot->getTemplate()->getId() .",
                ". intval($repetition->getFrequency()) .",
                '". $repetition->getIntervalType() ."',
                '". $repetition->getDiscountType() ."',
                ". floatval($repetition->getDiscountValue()) ."
            ),";
        }
        $query = rtrim($query,',') . ';';

        $this->openCart->db->query($query);
    }

    protected function createTemplateDue($templateRoot)
    {

        $query = "
            INSERT INTO `" . DB_PREFIX . "mundipagg_template_due` (
                `template_id`,
                `type`,
                `value`                
            ) VALUES (
                ". $templateRoot->getTemplate()->getId() .",
                '" . $templateRoot->getDueAt()->getType(). "',
                " . floatval($templateRoot->getDueAt()->getValue()) . "
            )
        ";

        $this->openCart->db->query($query);
    }


}