<?php
/**
 * ControllerExtensionPaymentMundipagg is the payment module controller
 *
 * @package Mundipagg
 */
require_once DIR_SYSTEM.'library/mundipagg/vendor/autoload.php';

use Mundipagg\Controller\Api;
use Mundipagg\Controller\SavedCreditCard;
use Mundipagg\Controller\TwoCreditCards;
use Mundipagg\Log;
use Mundipagg\LogMessages;
use Mundipagg\Order;
use Mundipagg\Settings\Boleto as BoletoSettings;
use Mundipagg\Settings\CreditCard as CreditCardSettings;
use Mundipagg\Settings\General as GeneralSettings;

class ControllerExtensionPaymentMundipagg extends Controller
{
    /**
     * @var array $data
     */
    private $data;

    /**
     * @var array $setting
     */
    private $setting;

    /**
     * @var object $creditCardModel
     */
    private $creditCardModel;

    /**
     * @var object $mundipaggModel
     */
    private $mundipaggModel;

    /**
     * @var object $mundipaggOrderUpdateModel
     */
    private $mundipaggOrderUpdateModel;

    /**
     * It loads opencart/mundipagg models
     *
     * From time to time it is necessary to load a ton of models. This method just
     * group this statements together in order to make a cleaner code
     *
     * @return void
     */
    private function load()
    {
        $this->load->model('checkout/order');
        $this->load->model('setting/setting');
        $this->load->model('extension/payment/mundipagg_customer');
        $this->load->model('extension/payment/mundipagg');
        $this->load->model('extension/payment/mundipagg_credit_card');
        $this->load->model('extension/payment/mundipagg_orderdata_update');
        $this->load->language('extension/payment/mundipagg');

        $this->data['misc'] = $this->language->get('misc');
        $this->setting = $this->model_setting_setting;
        $this->mundipaggModel = $this->model_extension_payment_mundipagg;
        $this->creditCardModel = $this->model_extension_payment_mundipagg_credit_card;
        $this->mundipaggOrderUpdateModel = $this->model_extension_payment_mundipagg_orderdata_update;
    }

    /**
     * This method sets the customized css file path
     *
     * The user can provide a custom css file to be used instead of modules default.
     * It must be inside theme stylesheet directory and be named mundipagg_theme.css.
     * (not implemented yet)
     *
     * @return Void
     */
    private function getDirectories()
    {
        $this->load();

        $theme = $this->config->get('config_theme');
        $themeDirectory = $this->config->get('theme_' . $theme . '_directory');
        $this->data['themeDirectory'] = 'catalog/view/theme/' . $themeDirectory;

        $customizedFile = $this->data['themeDirectory'] . '/stylesheet/mundipagg/mundipagg_customized.css';

        if (file_exists($customizedFile)) {
            $this->data['customizedFile'] = $customizedFile;
        }
    }

    /**
     * This method is called when user has to choose between the installed payment methods.
     *
     * @return mixed
     */
    public function index()
    {
        $boletoSettings = new BoletoSettings($this);
        $generalSettings = new GeneralSettings($this);
        $creditCardSettings = new CreditCardSettings($this);
        $savedCreditcard = new SavedCreditCard($this);

        $this->data['publicKey'] = $generalSettings->getPublicKey();

        $this->getDirectories();
        $this->loadUrls();

        if ($creditCardSettings->isEnabled()) {
            $this->data = array_merge($this->data, $creditCardSettings->getCreditCardPageInfo());
        }

        // check if payment with two credit cards is enabled
        if ($creditCardSettings->isTwoCreditCardsEnabled()) {
            $this->data = array_merge($this->data, $creditCardSettings->getTwoCreditCardsPageInfo());
        }

        if ($boletoSettings->isEnabled()) {
            $this->data = array_merge($this->data, $boletoSettings->getBoletoPageInfo());
        }

        $isSavedCreditCardEnabled = $creditCardSettings->isSavedCreditcardEnabled();
        $this->data['isSavedCreditcardEnabled'] = $isSavedCreditCardEnabled;

        if ($isSavedCreditCardEnabled) {
            $this->data['savedCreditcards'] =
                $savedCreditcard->getSavedCreditcardList($this->customer->getId());
        }

        $this->loadPaymentTemplates();

        return $this->load->view('extension/payment/mundipagg', $this->data);
    }

    private function loadUrls()
    {
        $generateUrl = 'extension/payment/mundipagg/generateBoleto';

        $this->data['generate_boleto_url'] = $this->url->link($generateUrl);
        $this->data['checkout_success_url'] = $this->url->link('checkout/success');
        $this->data['payment_failure_url'] = $this->url->link('checkout/failure');
    }

    private function loadPaymentTemplates()
    {
        $viewPath = 'extension/payment/mundipagg_';

        $this->data['savedCreditcardTemplate'] = $this->load->view($viewPath . 'saved_credit_card', $this->data);
        $this->data['newCreditcardTemplate'] = $this->load->view($viewPath . 'new_credit_card', $this->data);
        $this->data['creditcardTemplate'] = $this->load->view($viewPath . 'credit_card', $this->data);
        $this->data['boletoTemplate'] = $this->load->view($viewPath . 'boleto', $this->data);
        $this->data['twoCreditCardsTemplate'] = $this->load->view($viewPath . 'two_credit_cards', $this->data);
    }

    /**
     * Generate boleto
     * @return void
     */
    public function generateBoleto()
    {
        if (!$this->customer->isLogged()) {
            $this->response->redirect($this->url->link('checkout/failure', '', true));
        }

        $this->load();

        if (isset($this->session->data['order_id'])) {
            $orderData = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            if ($this->validate($orderData)) {
                if ($orderData['payment_code'] === 'mundipagg') {
                    $cart = $this->cart;
                    $response = $this->getOrder()->create($orderData, $cart, 'boleto');

                    if (isset($response->charges[0]->lastTransaction->success)) {

                        $this->load->model('extension/payment/mundipagg_order_processing');
                        $model = $this->model_extension_payment_mundipagg_order_processing;
                        $this->saveBoletoInfoInOrderHistory($response);
                        $this->printBoletoUrl($response);
                        $model->setOrderStatus($orderData['order_id'], 1);
                        return;

                    } else{
                        Log::create()
                            ->error(LogMessages::API_REQUEST_FAIL, __METHOD__)
                            ->withOrderId($this->session->data['order_id'])
                            ->withRequest(json_encode($response, JSON_PRETTY_PRINT));

                        Log::create()
                            ->error(LogMessages::UNKNOWN_API_RESPONSE, __METHOD__)
                            ->withOrderId($this->session->data['order_id']);
                        $this->response->redirect($this->url->link('checkout/failure', '', true));
                    }
                }
            }
        }

        Log::create()->error(LogMessages::ORDER_ID_NOT_FOUND, __METHOD__);
        $this->response->redirect($this->url->link('checkout/cart'));
    }

    /**
     * Save payment info and print boleto url
     * @param string $response Api's response
     * @return void
     */
    private function saveBoletoInfoInOrderHistory($response)
    {
        $orderComment =
                $this->language->get('boleto')['pending_order_status'] . " <br>" .
                "<a href='" .
                $response->charges[0]->lastTransaction->url.
                "' target='_blank'>" .
                $this->language->get('boleto')['click_to_generate'] .
                "</a>";

        $model = $this->model_extension_payment_mundipagg_order_processing;

        $model->addOrderHistory(
            $this->session->data['order_id'],
            1,
            $orderComment,
            1
        );
    }

    private function printBoletoUrl($response)
    {
        echo $response->charges[0]->lastTransaction->url;
    }

    /**
     * Validate order data
     *
     * @return boolean
     */
    private function validate($orderData)
    {
        if (isset($orderData['order_id']) &&
            $orderData['order_id'] !== null) {
            $pattern = array(
               "customer_id",
               "email",
               "payment_firstname",
               "payment_lastname",
               "payment_address_1",
               "payment_address_2",
               "payment_postcode",
               "payment_city",
               "payment_zone_id",
               "payment_zone",
               "payment_zone_code",
               "payment_country_id",
               "payment_country",
               "payment_custom_field",
               "payment_method",
               "payment_code",
               "currency_id",
               "currency_code",
               "ip"
            );

            // kind of trick, but it counts how much indexes are in the difference between the intersection
            // between pattern and indexes in orderData
            if (count(array_diff($pattern, array_intersect(array_keys($orderData), $pattern))) > 0) {
                Log::create()->error(LogMessages::MALFORMED_REQUEST, __METHOD__);

                return false;
            }

            return true;
        }

        Log::create()
            ->error(LogMessages::ORDER_ID_NOT_FOUND, __METHOD__)
            ->withOrderId($orderData['order_id']);

        return false;
    }

    /**
     * Group credit card request validations in one method
     *
     * @return bool
     */
    private function isValidateCreditCardRequest()
    {
        if (!isset($this->session->data['order_id'])) {
            return false;
        }
        
        if ($this->session->data['payment_method']['code'] !== 'mundipagg') {
            return false;
        }
        
        if (!$this->customer->isLogged()) {
            return false;
        }
        
        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            return false;
        }
        
        return true;
    }

    /**
     * Create credit card order using mundipagg SDK
     *
     * @param $interest
     * @param $installments
     * @param $orderData
     * @param $cardToken
     * @return mixed
     */
    private function createCreditCardOrder($interest, $installments, $orderData, $cardToken, $cardId)
    {
        $this->load();
        
        $order = $this->getOrder();
        
        $order->setInterest($interest);
        $order->setInstallments($installments);

        $orderData['amountWithInterest'] =
            $this->setInterestToOrder($orderData, $interest);
        
        return $order->create($orderData, $this->cart, 'creditCard', $cardToken, $cardId);
    }
    
    private function getOrder()
    {
        if (!is_object($this->order)) {
            $this->order = new Order($this);
            $this->order->setCustomerModel($this->model_extension_payment_mundipagg_customer);
        }

        return $this->order;
    }

    /**
     * This method stores the received order id from mundipagg with the opencart order id
     *
     * @param string $mundiOrderId
     * @param string $openCartOrderId
     * @return void
     */
    public function saveMPOrderId($mundiOrderId, $openCartOrderId)
    {
        $this->load->model('extension/payment/mundipagg_credit_card');
        $this->model_extension_payment_mundipagg_credit_card->saveMundiOrder(
            $mundiOrderId,
            $openCartOrderId
        );
    }

    /**
     * This method process the credit card transaction
     *
     * @return void
     */
    public function processCreditCard()
    {
        $this->load();
        
        if (!$this->isValidateCreditCardRequest()) {
            Log::create()
                ->error(LogMessages::INVALID_CREDIT_CARD_REQUEST, __METHOD__)
                ->withOrderId($this->session->data['order_id']);
            $this->response->redirect($this->url->link('checkout/failure'));
        }

        // we only have munditoken-1 when in two credit cards payment
        if (isset($this->request->post['munditoken-1'])) {
            $this->processTwoCreditCards();
        } else {
            $this->processSingleCreditCard();
        }
    }

    private function processSingleCreditCard()
    {
        $orderData = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        // payment with saved credit card
        if (isset($this->request->post['mundipaggSavedCreditCard'])) {
            $cardId = $this->request->post['mundipaggSavedCreditCard'];
            $cardToken = null;
        } else {
            $cardToken = $this->request->post['munditoken-0'];
            $saveCreditCard = false;
            if (isset($this->request->post['cardSaveCreditcard'])) {
                $saveCreditCard = $this->request->post['cardSaveCreditcard'];
            }

            $orderData['saveCreditcard'] = $saveCreditCard === 'on';
            $cardId = null;
        }

        $paymentDetails = explode('|', $this->request->post['payment-details-0']);

        try {
            $response = $this->createCreditCardOrder(
                (double)$paymentDetails[1],
                $paymentDetails[0],
                $orderData,
                $cardToken,
                $cardId
            );
        } catch (Exception $e) {
            Log::create()
                ->error(LogMessages::UNABLE_TO_CREATE_ORDER, __METHOD__)
                ->withOrderId($this->session->data['order_id'])
                ->withException($e);

            $this->response->redirect($this->url->link('checkout/failure'));
        }

        $orderStatus = $this->getOrder()->translateStatusFromMP($response);
        if (!$orderStatus) {
            Log::create()
                ->error(LogMessages::UNKNOWN_ORDER_STATUS, __METHOD__)
                ->withResponseStatus($response->status)
                ->withOrderId($this->session->data['order_id']);

            $this->response->redirect($this->url->link('checkout/failure'));
        }

        $novaOrderMundial = $this->getOrder();
        $novaOrderMundial->updateOrderStatus($orderStatus);

        $this->saveMPOrderId($response->id, $this->session->data['order_id']);
        $this->response->redirect($this->url->link('checkout/success', '', true));
    }

    private function processTwoCreditCards()
    {
        if (!$this->isValidTwoCreditCardsRequest($this->request->post)) {
            Log::create()
                ->error(LogMessages::UNKNOWN_ORDER_STATUS, __METHOD__)
                ->withOrderId($this->session->data['order_id']);

            $this->response->redirect($this->url->link('checkout/failure'));
        }

        try {
            $twoCreditCards = new TwoCreditCards($this, $this->request->post, $this->cart);
            $response = $twoCreditCards->processPayment();
        } catch (\Exception $e) {
            Log::create()
                ->error(LogMessages::CANNOT_CREATE_TWO_CREDIT_CARDS_ORDER, __METHOD__)
                ->withOrderId($this->session->data['order_id']);

            $this->response->redirect($this->url->link('checkout/failure'));
        }

        $orderStatus = $this->getOrder()->translateStatusFromMP($response);
        if (!$orderStatus) {
            Log::create()
                ->error(LogMessages::UNKNOWN_ORDER_STATUS, __METHOD__)
                ->withResponseStatus($response->status)
                ->withOrderId($this->session->data['order_id']);

            $this->response->redirect($this->url->link('checkout/failure'));
        }

        $this->getOrder()->updateOrderStatus($orderStatus);
        $this->saveMPOrderId($response->id, $this->session->data['order_id']);
        $this->response->redirect($this->url->link('checkout/success', '', true));
    }

    private function isValidTwoCreditCardsRequest($requestData)
    {
        $paymentDetailsFirstCard = explode('|', $requestData['payment-details-0']);
        $paymentDetailsSecondCard = explode('|', $requestData['payment-details-1']);

        if (count($paymentDetailsFirstCard) !== 2 || count($paymentDetailsSecondCard) !== 2) {
            return false;
        }

        if (!isset($requestData['total-0'], $requestData['total-1'])) {
            return false;
        }

        if (!isset($requestData['munditoken-0'], $requestData['munditoken-1'])) {
            return false;
        }

        return true;
    }

    /**
     * Update order data in database
     * @param array $orderData
     * @param float $interest
     * @return mixed (bool, float)
     */
    private function setInterestToOrder($orderData, $interest)
    {
        if ($interest > 0) {
            $amountWithInterest = $this->setInterestToAmount($orderData['total'], $interest);
            $interestAmount = $amountWithInterest - $orderData['total'];

            $this->mundipaggOrderUpdateModel->
                updateOrderAmountInOrder(
                    $orderData['order_id'],
                    $amountWithInterest
                );

            $this->mundipaggOrderUpdateModel->
                updateOrderAmountInOrderTotals(
                    $orderData['order_id'],
                    $amountWithInterest
                );

            $this->mundipaggOrderUpdateModel->
                insertInterestInOrderTotals(
                    $orderData['order_id'],
                    $interestAmount
                );

            return $amountWithInterest;
        }
        return false;
    }

    private function setInterestToAmount($amount, $interest)
    {
        return round($amount + ($amount * ($interest * 0.01)), 2);
    }

    // index.php?route=extension/payment/mundipagg/api/installments&brand=visa&total=

    /**
     * The API responds to
     */
    public function api()
    {
        $postData = $this->request->post;
        $getData = $this->request->get;

        $action = explode('/', $getData['route']);
        $action = array_pop($action);

        $data = [
            'post' => $postData,
            'get' => $getData
        ];

        $verb = strtolower($_SERVER['REQUEST_METHOD']);

        $api = new Api($data, $verb, $this);
        $result = $api->{$action}();

        $this->sendResponse($result);
    }

    private function sendResponse($result)
    {
        $responseStatus = $result['status_code'];
        $responseData = $result['payload'];

        http_response_code($responseStatus);

        echo json_encode($responseData);
    }
}