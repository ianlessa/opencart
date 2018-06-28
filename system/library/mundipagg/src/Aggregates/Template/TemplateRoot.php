<?php

namespace Mundipagg\Aggregates\Template;


class TemplateRoot
{
    /** @var TemplateEntity */
    protected $template;
    /** @var DueValueObject */
    protected $dueAt;
    /** @var array */
    protected $repetitions;

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
     * @param array $repetitions
     * @return TemplateRoot
     */
    public function setRepetitions($repetitions)
    {
        $this->repetitions = $repetitions;
        return $this;
    }

}