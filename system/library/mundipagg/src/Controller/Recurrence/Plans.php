<?php

namespace Mundipagg\Controller\Recurrence;

class Plans extends Recurrence
{
    public function __call($name, array $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        }

        return $this->index();
    }

    public function index()
    {
        $this->data['heading_title'] = $this->language['Plans'];
        $this->render();
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