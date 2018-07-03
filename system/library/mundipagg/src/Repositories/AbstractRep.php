<?php
/**
 * Created by PhpStorm.
 * User: ian
 * Date: 29/06/18
 * Time: 16:22
 */

namespace Mundipagg\Repositories;


abstract class AbstractRep
{
    protected $openCart;

    public function __construct($openCart)
    {
        $this->openCart = $openCart;
    }

    public function save(&$object){
        $objectId = null;
        if (
            is_object($object) &&
            method_exists($object,'getId')
        ) {
            $object = $object->getId();
        }
        if ($objectId === null) {
            $this->create($object);
        }

        return $this->update($object);
    }

    abstract protected function create(&$object);
    abstract protected function update(&$object);
    abstract public function delete($object);
    abstract public function find($objectId);
    abstract public function listEntities($limit);
}