<?php

require_once DIR_SYSTEM . 'library/mundipagg/vendor/autoload.php';

use Mundipagg\Controller\CreditCardSettings;
use Mundipagg\Model\Creditcard;
use Mundipagg\Model\Customer;

class ControllerAccountSavedCreditcards extends Controller {

    private $languageMundiPagg;

    public function index() {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/account', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        } else {
            $creditCardSettings = new CreditCardSettings($this);
            if ($creditCardSettings->isSavedCreditcardEnabled() !== 'true') {
                $this->response->redirect($this->url->link('account/account', '', true));
                return;
            }
        }

        $this->loadLanguage();
        $data = $this->loadTemplateControllers();

        $data['text'] = $this->languageMundiPagg;
        $data['breadcrumbs'] = $this->setBreadcrumbs($data);
        $data['creditcards'] = $this->getCreditcards();

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
            'href' => $this->url->link('account/account')
        ];

        $breadcrumbs[] = [
            'text' => $this->languageMundiPagg['title'],
            'href' => $this->url->link('account/saved_creditcards', '', true)
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


    private function getCreditcards()
    {
        $savedCreditcard = new Creditcard($this);
        $mundiPaggCustomer = new Customer($this);

        $customerId = $this->customer->getId();
        $mundiPaggCustomerId = $mundiPaggCustomer->getByOpencartId($customerId);

        return
            $savedCreditcard->
            getCreditcardsByCustomerId(
                $mundiPaggCustomerId['mundipagg_customer_id']
            );
    }
}