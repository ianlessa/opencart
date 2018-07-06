<?php

namespace Mundipagg\Aggregates;

interface IAGGRoot
{
    public function isDisabled();
    public function setDisabled($disabled);
    public function getId();
}