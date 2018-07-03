<?php

namespace Mundipagg\Aggregates\Template;

use Exception;

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
     * @throws Exception
     */
    public function setFrequency($frequency)
    {
        $intValue = intval($frequency);
        if ($intValue <= 0) {
            throw new Exception(
                "Interval frequency should be greater than 0: $frequency!"
            );
        }
        $this->frequency = $intValue;
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
     * @throws Exception
     */
    public function setIntervalType($intervalType)
    {
        if (!in_array($intervalType, self::getValidIntervalTypes())) {
            throw new Exception("Invalid Interval Type: $intervalType! ");
        }

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
     * @throws Exception
     */
    public function setDiscountType($discountType)
    {
        if (!in_array($discountType, self::getValidDiscountTypes())) {
            throw new Exception("Invalid Interval Discount Type: $discountType! ");
        }

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
            ['code'=>self::DISCOUNT_TYPE_PERCENT, 'name' => '%'],
            ['code'=>self::DISCOUNT_TYPE_FIXED, 'name' => "R$"]
        ];
    }

    public static function getIntervalTypesArray()
    {
        return [
            ['code'=>self::INTERVAL_TYPE_MONTHLY, 'name'=> "Mensal"],
            ['code'=>self::INTERVAL_TYPE_SEMESTER, 'name'=> "Semestral"]
        ];
    }

    public static function getValidIntervalTypes()
    {
        return [
            self::INTERVAL_TYPE_MONTHLY,
            self::INTERVAL_TYPE_SEMESTER
        ];
    }

    public static function getValidDiscountTypes()
    {
        return [
            self::DISCOUNT_TYPE_PERCENT,
            self::DISCOUNT_TYPE_FIXED
        ];
    }
}