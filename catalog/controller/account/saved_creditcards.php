<?php

class ControllerAccountSavedCreditcards extends Controller {

    private $languageMundiPagg;

    public function index() {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/account', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->loadLanguage();
        $data = $this->loadTemplateControllers();

        $data['text'] = $this->languageMundiPagg;
        $data['breadcrumbs'] = $this->setBreadcrumbs($data);
        $this->document->setTitle($data['text']['title']);

        $this->response->setOutput($this->load->view('account/saved_creditcards', $data));
    }

    private function loadLanguage()
    {
        $this->load->language('extension/payment/mundipagg');
        $this->languageMundiPagg = $this->language->get("saved_creditcard");
    }

    /**
     * @param $data
     * @return mixed
     */
    private function setBreadcrumbs()
    {
        $breadcrumbs[] = [
            'text' => $this->languageMundiPagg['account'],
            'href' => $this->url->link('common/home')
        ];

        $breadcrumbs[] = [
            'text' => $this->languageMundiPagg['title'],
            'href' => $this->url->link('account/account', '', true)
        ];
        return $breadcrumbs;
    }

    private function loadTemplateControllers()
    {
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        return $data;
    }

}