<?php

namespace Mundipagg\Aggregates;

interface IAggregateRoot
{
    public function isDisabled();
    public function setDisabled($disabled);
    public function getId();
}