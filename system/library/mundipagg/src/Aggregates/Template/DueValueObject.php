<?php

namespace Mundipagg\Aggregates\Template;


class DueValueObject
{
    const TYPE_EXACT = 'E';
    const TYPE_WORKDAY = 'U';

    /** @var string */
    protected $type;
    /** @var int */
    protected $value;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return DueValueObject
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $value
     * @return DueValueObject
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
}