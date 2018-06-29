<?php

namespace Mundipagg\Aggregates\Template;


class TemplateEntity
{
    /** @var int */
    protected $id;
    /** @var boolean */
    protected $isSingle;
    /** @var string */
    protected $name;
    /** @var string */
    protected $description;
    /** @var boolean */
    protected $acceptCreditCard;
    /** @var boolean */
    protected $acceptBoleto;
    /** @var boolean */
    protected $allowInstallments;

    public function __construct()
    {
        $this->isSingle =
        $this->acceptCreditCard =
        $this->acceptBoleto =
        $this->allowInstallments =
            false;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return TemplateEntity
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSingle()
    {
        return $this->isSingle;
    }

    /**
     * @param bool $isSingle
     * @return TemplateEntity
     */
    public function setIsSingle($isSingle)
    {
        $this->isSingle = $isSingle;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return TemplateEntity
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAcceptCreditCard()
    {
        return $this->acceptCreditCard;
    }

    /**
     * @param bool $acceptCreditCard
     * @return TemplateEntity
     */
    public function setAcceptCreditCard($acceptCreditCard)
    {
        $this->acceptCreditCard = $acceptCreditCard;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAcceptBoleto()
    {
        return $this->acceptBoleto;
    }

    /**
     * @param bool $acceptBoleto
     * @return TemplateEntity
     */
    public function setAcceptBoleto($acceptBoleto)
    {
        $this->acceptBoleto = $acceptBoleto;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowInstallments()
    {
        return $this->allowInstallments;
    }

    /**
     * @param bool $allowInstallments
     * @return TemplateEntity
     */
    public function setAllowInstallments($allowInstallments)
    {
        $this->allowInstallments = boolval(intval($allowInstallments));
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return TemplateEntity
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
}