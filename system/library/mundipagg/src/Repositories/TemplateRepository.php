<?php

namespace Mundipagg\Repositories;

use Mundipagg\Aggregates\Template\RepetitionValueObject;
use Mundipagg\Aggregates\Template\TemplateRoot;
use Mundipagg\Factories\TemplateRootFactory;

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
                `allow_installments`,
                `trial`,
                `due_type`,
                `due_value`
            ) VALUES (
                " . ($templateRoot->getTemplate()->isSingle()?1:0) . ",
                '" . $templateRoot->getTemplate()->getName() . "',
                '" . $templateRoot->getTemplate()->getDescription() . "',
                " . ($templateRoot->getTemplate()->isAcceptCreditCard()?1:0) . ",
                " . ($templateRoot->getTemplate()->isAcceptBoleto()?1:0) . ",
                " . ($templateRoot->getTemplate()->isAllowInstallments()?1:0) . ",                
                " . $templateRoot->getTemplate()->getTrial() . ",
                '" . $templateRoot->getDueAt()->getType() . "',
                " . $templateRoot->getDueAt()->getValue() . "
            )
        ");

        $db->query($query);
        $templateRoot->getTemplate()->setId($db->getLastId());

        $this->createTemplateRepetitions($templateRoot);

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
        $query = "
            SELECT 
              t.*,
              GROUP_CONCAT(r.frequency) AS frequency, 
              GROUP_CONCAT(r.interval_type) AS interval_type,
              GROUP_CONCAT(r.discount_type) AS discount_type, 
              GROUP_CONCAT(r.discount_value) AS discount_value,      
              GROUP_CONCAT(r.cycles) AS cycles      
            FROM `" . DB_PREFIX . "mundipagg_template` AS t 
            INNER JOIN `" . DB_PREFIX . "mundipagg_template_repetition` AS r
              ON t.id = r.template_id
            GROUP BY t.id  
        ";

        if ($limit !== 0) {
            $limit = intval($limit);
            $query .= " LIMIT $limit";
        }

        $result = $this->openCart->db->query($query . ";");

        $templateRootFactory = new TemplateRootFactory();
        $templateRoots = [];

        foreach ($result->rows as $row) {
            $templateRoot = $templateRootFactory->createFromDBData($row);
            $templateRoots[] = $templateRoot;
        }

        return $templateRoots;
    }

    protected function createTemplateRepetitions($templateRoot)
    {
        $query = "
            INSERT INTO `" . DB_PREFIX . "mundipagg_template_repetition` (
                `template_id`,
                `cycles`,
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
                ". $repetition->getCycles() . ",
                ". intval($repetition->getFrequency()) .",
                '". $repetition->getIntervalType() ."',
                '". $repetition->getDiscountType() ."',
                ". floatval($repetition->getDiscountValue()) ."
            ),";
        }
        $query = rtrim($query,',') . ';';

        $this->openCart->db->query($query);
    }
}