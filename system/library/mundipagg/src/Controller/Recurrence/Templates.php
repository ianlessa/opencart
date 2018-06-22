<?php

namespace Mundipagg\Controller\Recurrence;

class Templates extends Recurrence
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
        $this->data['heading_title'] = $this->language['Templates'];
        $this->data['createLink'] =
            'index.php?route=extension/payment/mundipagg/templates&user_token=' .
            $this->openCart->request->get['user_token'] .
            '&action=create';
        $this->render('templates/base');
    }

    protected function edit()
    {
    }

    protected function delete()
    {
    }

    protected function create()
    {
        $this->data['heading_title'] = $this->language['Templates'];

        $this->data['formAction'] = 'index.php?route=extension/payment/mundipagg/templates&user_token=' .
            $this->openCart->request->get['user_token'] .                                                                                                                                               
            '&action=create';

        $this->data['formPlan'] = 'extension/payment/mundipagg/recurrence/templates/form_plan.twig';
        $this->data['panelPlanFrequency'] = 'extension/payment/mundipagg/recurrence/templates/panelPlanFrequency.twig';

        $this->data['formSingle'] = 'extension/payment/mundipagg/recurrence/templates/form_single.twig';
        $this->data['panelSingleFrequency'] = 'extension/payment/mundipagg/recurrence/templates/panelSingleFrequency.twig';

        $path = 'extension/payment/mundipagg/';
        $this->data['formBase'] = $path . 'recurrence/templates/form_base.twig';
        $this->render('templates/create');
    }

}
