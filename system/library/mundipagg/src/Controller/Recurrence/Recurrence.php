<?php

namespace Mundipagg\Controller\Recurrence;

use Mundipagg\Settings\Recurrence as RecurrenceSettings;

class Recurrence
{
    public $data;
    public $openCart;
    public $language;
    public $templateDir = 'extension/payment/mundipagg/recurrence/';
    protected $recurrenceSettings;

    public function __construct($openCart)
    {
        $this->openCart = $openCart;
        $lang = $this->openCart->load->language('extension/payment/mundipagg');
        $this->language = $lang['recurrence'];

        $this->chargeRecurrenceSettings();
        $this->setLayoutComponents();
    }

    /**
     * Sets opencart dashboard layout components
     *
     * It puts opencart header, left column and footer
     *
     * @return void
     */
    protected function setLayoutComponents()
    {
        $this->data['header'] =
            $this->openCart->load->controller('common/header');
        $this->data['column_left'] =
            $this->openCart->load->controller('common/column_left');
        $this->data['footer'] = $this->openCart->load->controller('common/footer');
    }

    public function render($path)
    {
        $this->openCart->response->setOutput(
            $this->openCart->load->view(
                $this->templateDir . $path,
                $this->data
            )
        );
    }

    protected function chargeRecurrenceSettings()
    {
        $this->recurrenceSettings = new RecurrenceSettings($this->openCart);
        $this->data['recurrenceSettings'] = $this->recurrenceSettings->getAllSettings();
    }
}
