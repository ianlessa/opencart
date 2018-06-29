<?php

namespace Mundipagg\Aggregates\Template;


class RepetitionValueObject
{
    const DISCOUNT_TYPE_FIXED = 'F';
    const DISCOUNT_TYPE_PERCENT = 'P';

    const INTERVAL_TYPE_MONTHLY = 'M';
    const INTERVAL_TYPE_SEMESTER = 'S';

    /** @var int */
    protected $frequency;
    /** @var string */
    protected $intervalType;
    /** @var string */
    protected $discountType;
    /** @var float */
    protected $discountValue;

    /**
     * @return int
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @param int $frequency
     * @return RepetitionValueObject
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntervalType()
    {
        return $this->intervalType;
    }

    /**
     * @param string $intervalType
     * @return RepetitionValueObject
     */
    public function setIntervalType($intervalType)
    {
        $this->intervalType = $intervalType;
        return $this;
    }

    /**
     * @return string
     */
    public function getDiscountType()
    {
        return $this->discountType;
    }

    /**
     * @param string $discountType
     * @return RepetitionValueObject
     */
    public function setDiscountType($discountType)
    {
        $this->discountType = $discountType;
        return $this;
    }

    /**
     * @return float
     */
    public function getDiscountValue()
    {
        return $this->discountValue;
    }

    /**
     * @param float $discountValue
     * @return RepetitionValueObject
     */
    public function setDiscountValue($discountValue)
    {
        $this->discountValue = $discountValue;
        return $this;
    }

    public static function getDiscountTypesArray()
    {
        return [
            ['code'=>self::DISCOUNT_TYPE_PERCENT , 'name' => '%'],
            ['code'=>self::DISCOUNT_TYPE_FIXED , 'name' => "R$"]
        ];
    }

    public static function getIntervalTypesArray()
    {
        return [
            ['code'=>self::INTERVAL_TYPE_MONTHLY, 'name'=> "Mensal"],
            ['code'=>self::INTERVAL_TYPE_SEMESTER, 'name'=> "Semestral"]
        ];
    }
}