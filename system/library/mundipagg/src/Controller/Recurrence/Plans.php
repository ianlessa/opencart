<?php

namespace Mundipagg\Controller\Recurrence;

class Plans
{
    private $openCart;

    public function __construct($openCart)
    {
        $this->openCart = $openCart;
    }

    public function __call($name, array $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        }

        return $this->index();
    }

    public function index()
    {
    }

    protected function edit()
    {
    }

    protected function delete()
    {
    }

    protected function create()
    {
    }
}