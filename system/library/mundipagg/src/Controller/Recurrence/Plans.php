<?php

namespace Mundipagg\Controller\Recurrence;

class Plans extends Recurrence
{
    public function __call($name, array $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        }

        $this->loadTemplates();

        return $this->index();
    }

    public function index()
    {
        $this->data['heading_title'] = $this->language['Plans'];

        $this->data['actionsTemplate'] =
            $this->openCart->load->view($this->templateDir . 'actions');
        $this->data['breadCrumbTemplate'] =
            $this->openCart->load->view($this->templateDir . 'breadcrumb');
        $this->data['content'] =
            $this->openCart->load->view($this->templateDir . 'plans/grid') .
            $this->openCart->load->view($this->templateDir . 'plans/list');

        $this->render('plans/base');
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