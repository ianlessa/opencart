<?php

namespace Mundipagg\Aggregates\Template;

use Mundipagg\Aggregates\IAGGRoot;

class TemplateRoot implements IAGGRoot
{
    /** @var bool */
    protected $isDisabled;
    /** @var TemplateEntity */
    protected $template;
    /** @var DueValueObject */
    protected $dueAt;
    /** @var RepetitionValueObject[] */
    protected $repetitions;

    public function __construct()
    {
        $this->isDisabled = false;
    }

    /**
     * @return TemplateEntity
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param TemplateEntity $template
     * @return TemplateRoot
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return DueValueObject
     */
    public function getDueAt()
    {
        return $this->dueAt;
    }

    /**
     * @param DueValueObject $dueAt
     * @return TemplateRoot
     */
    public function setDueAt($dueAt)
    {
        $this->dueAt = $dueAt;
        return $this;
    }

    /**
     * @return array
     */
    public function getRepetitions()
    {
        return $this->repetitions;
    }

    /**
     * @param RepetitionValueObject $repetitions
     * @return TemplateRoot
     */
    public function addRepetition($repetition)
    {
        $this->repetitions[] = $repetition;
        return $this;
    }

    public function getId()
    {
        return $this->template->getId();
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->isDisabled;
    }

    /**
     * @param bool $isDisabled
     */
    public function setDisabled($isDisabled)
    {
        $this->isDisabled = boolval($isDisabled);
        return $this;
    }
}