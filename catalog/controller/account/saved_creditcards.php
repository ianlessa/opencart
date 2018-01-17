<?php

require_once DIR_SYSTEM . 'library/mundipagg/vendor/autoload.php';

use Mundipagg\Settings\CreditCard as CreditCardSettings;

use Mundipagg\Model\Creditcard;
use Mundipagg\Model\Customer;

class ControllerAccountSavedCreditcards extends Controller {

    private $languageMundiPagg;

    public function index()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/account', '', true);
            $this->response->redirect($this->url->link('account/login', '', true));
        } else {
            $creditCardSettings = new CreditCardSettings($this);
            if (!$creditCardSettings->isSavedCreditcardEnabled()) {
                $this->response->redirect($this->url->link('account/account', '', true));
                return;
            }
        }

        $this->loadLanguage();
        $data = $this->loadTemplateControllers();

        $data['text'] = $this->languageMundiPagg;
        $data['breadcrumbs'] = $this->setBreadcrumbs($data);
        $data['creditcards'] = $this->getCreditcards();
        $data['deleteUrl'] = $this->url->link('account/saved_creditcards/delete', '', true);

        $this->document->setTitle($data['text']['title']);
        $this->response->setOutput($this->load->view('account/saved_creditcards', $data));
    }

    public function delete()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/account', '', true);
            $this->response->redirect($this->url->link('account/login', '', true));
        } else {
            $creditCardSettings = new CreditCardSettings($this);
            if (!$creditCardSettings->isSavedCreditcardEnabled()) {
                return;
            }
        }

        $cardId = isset($this->request->post['cardId']) ? $this->request->post['cardId'] : null;
        if ($cardId) {
            //get all customer's cards
            $savedCreditCards = $this->getCreditcards();

            foreach ($savedCreditCards as $creditCard) {
                if (intval($creditCard['id']) == intval($cardId)) {
                    //do deletion
                    $savedCreditcard = new Creditcard($this);
                    $savedCreditcard->deleteCreditcard($creditCard['id']);
                    break;
                }
            }
        }
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