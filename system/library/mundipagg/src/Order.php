<?php
namespace Mundipagg;

require_once DIR_SYSTEM . 'library/mundipagg/vendor/autoload.php';

use MundiAPILib\MundiAPIClient;
use MundiAPILib\Models\CreateShippingRequest;
use MundiAPILib\Models\CreateOrderRequest;
use MundiAPILib\Models\CreateAddressRequest;
use MundiAPILib\Models\CreateCustomerRequest;
use Mundipagg\Controller\Settings;
use Mundipagg\Controller\Boleto;
use MundiAPILib\Exceptions\ErrorException;

/**
 * @method \MundiAPILib\Controllers\OrdersController getOrders()
 * @method \MundiAPILib\Controllers\CustomersController getCustomers()
 */
class Order
{
    private $orderInterest;
    private $orderInstallments;
    
    /**
     * @var MundiAPIClient
     */
    private $apiClient;
    private $openCart;
    private $settings;

    private $mundipaggCustomerModel;

    /**
     * @param array $openCart
     */
    public function __construct($openCart)
    {
        $this->settings = new Settings($openCart);

        $this->openCart = $openCart;
        $this->orderInterest = 0;

        $this->apiClient = new MundiAPIClient($this->settings->getSecretKey(), $this->settings->getPassword());
    }
    
    public function __call(string $name, array $arguments)
    {
        if (method_exists($this->apiClient, $name)) {
            return call_user_func_array([$this->apiClient, $name], $arguments);
        }
    }
    
    public function setInterest($interest)
    {
        $this->orderInterest = $interest;
    }
    
    public function setInstallments($installments)
    {
        $this->orderInstallments = $installments;
    }

    /**
     * Create a MundiPagg order
     * @param array $orderData
     * @param array $cart
     * @param string $paymentMethod
     * @param string $cardToken
     * @return object
     */
    public function create($orderData, $cart, $paymentMethod, $cardToken = null)
    {
        $items = $this->prepareItems($cart->getProducts());
        $createAddressRequest = $this->createAddressRequest($orderData);
        $createCustomerRequest = $this->createCustomerRequest($orderData, $createAddressRequest);
        $createShippingRequest = $this->createShippingRequest($orderData, $createAddressRequest, $cart);
        $payments = $this->preparePayments($paymentMethod, $cardToken);

        $CreateOrderRequest = $this->createOrderRequest(
            $items,
            $createCustomerRequest,
            $payments,
            $orderData['order_id'],
            $this->getMundipaggCustomerId($orderData['customer_id']),
            $createShippingRequest,
            $this->settings->getModuleMetaData()
        );

        \Mundipagg\Log::create()
            ->info(\Mundipagg\LogMessages::CREATE_ORDER_MUNDIPAGG_REQUEST, __METHOD__)
            ->withOrderId($orderData['order_id'])
            ->withRequest(json_encode($CreateOrderRequest, JSON_PRETTY_PRINT));

        $order = $this->getOrders()->createOrder($CreateOrderRequest);
        $this->createOrUpdateCharge($orderData, $order);

        \Mundipagg\Log::create()
            ->info(\Mundipagg\LogMessages::CREATE_ORDER_MUNDIPAGG_RESPONSE, __METHOD__)
            ->withOrderId($orderData['order_id'])
            ->withResponse(json_encode($order, JSON_PRETTY_PRINT));

        return $order;
    }

    public function cancelCharge($chargeId)
    {
        try {
            return $this->getCharges()->cancelCharge($chargeId);
        } catch (ErrorException $e) {
            \Mundipagg\Log::create()
                ->error($e->getMessage(), __METHOD__)
                ->withLineNumber(__LINE__);
            throw new \Exception($e->getMessage());
        } catch (\Exception $e) {
            \Mundipagg\Log::create()
                ->error(\Mundipagg\LogMessages::UNABLE_TO_CANCEL_MUNDI_CHARGE, __METHOD__)
                ->withLineNumber(__LINE__);
            throw new \Exception(\Mundipagg\LogMessages::UNABLE_TO_CANCEL_MUNDI_CHARGE);
        }
    }

    public function createOrUpdateCharge(array $opencartOrder, $mundipaggOrder)
    {
        try {
            if(!is_object($mundipaggOrder)) {
                throw new \Exception();
            }
            if (property_exists($mundipaggOrder, 'charges')) {
                foreach ($mundipaggOrder->charges as $charge) {
                    $rows = $this->openCart->db->query(
                        'SELECT 1 FROM `' . DB_PREFIX . 'mundipagg_charge`'.
                        ' WHERE opencart_id = ' . $opencartOrder['order_id'] .
                        '   AND charge_id = "' . $charge->id . '"'
                    );
                    if ($this->openCart->db->countAffected()) {
                        $query = 'UPDATE `' . DB_PREFIX . 'mundipagg_charge` ' .
                            'SET status = "' . $mundipaggOrder->status . '",' .
                            '    canceled_amount = "' . $charge->payment_method . '"' .
                            ' WHERE opencart_id = ' . $opencartOrder['order_id'] .
                            '   AND charge_id = "' . $charge->id . '"';
                    } else {
                        $query = 'INSERT INTO `' . DB_PREFIX . 'mundipagg_charge` ' .
                            '(opencart_id, charge_id, payment_method, status, paid_amount, amount) ' .
                            'VALUES (' .
                            $opencartOrder['order_id'] . ', ' .
                            '"' . $charge->id . '",' .
                            '"' . $charge->payment_method . '", ' .
                            '"' . $mundipaggOrder->status . '",' .
                            $charge->paid_amount . ',' .
                            $charge->amount .
                            ');';
                    }
                }
            } else if (property_exists($mundipaggOrder, 'canceledAt')) {
                $rows = $this->openCart->db->query(
                    'SELECT 1 FROM `' . DB_PREFIX . 'mundipagg_charge`'.
                    ' WHERE opencart_id = ' . $mundipaggOrder->code .
                    '   AND charge_id = "' . $mundipaggOrder->id . '"'
                );
                if ($this->openCart->db->countAffected()) {
                    $query = 'UPDATE `' . DB_PREFIX . 'mundipagg_charge` ' .
                        'SET status = "' . $mundipaggOrder->status . '",' .
                        '    canceled_amount = "' . $mundipaggOrder->amount . '"' .
                        ' WHERE opencart_id = ' . $mundipaggOrder->code .
                        '   AND charge_id = "' . $mundipaggOrder->id . '"';
                } else {
                    $query = 'INSERT INTO `' . DB_PREFIX . 'mundipagg_charge` ' .
                        '(opencart_id, charge_id, payment_method, status, amount) ' .
                        'VALUES (' .
                        $mundipaggOrder->code . ', ' .
                        '"' . $mundipaggOrder->id . '",' .
                        '"' . $mundipaggOrder->paymentMethod . '", ' .
                        '"' . $mundipaggOrder->status . '",' .
                        $mundipaggOrder->amount .
                        ');';
                }
                $orderStatus = $this->translateStatusFromMP($mundipaggOrder);
                $this->openCart->db->query(
                    "UPDATE `" . DB_PREFIX . "order`
                        SET order_status_id = " . $orderStatus . "
                        WHERE order_id = ". $mundipaggOrder->code
                );
            }
            $this->openCart->db->query($query);
        } catch (Exception $e) {
            \Mundipagg\Log::create()
            ->error(\Mundipagg\LogMessages::UNABLE_TO_CREATE_MUNDI_CHARGE, __METHOD__)
            ->withLineNumber(__LINE__)
            ->withQuery($query);
        }
    }

    /**
     * @param array                 $items
     * @param CreateCustomerRequest $customer
     * @param array                 $payments
     * @param string                $code
     * @param string                $customerId
     * @param CreateShippingRequest $shipping
     * @param array                 $metadata
     * @return CreateOrderRequest
     */
    private function createOrderRequest(
        $items,
        $customer,
        $payments,
        $code,
        $customerId,
        $shipping,
        $metadata = null
    ) {
        return new CreateOrderRequest(
            $items,
            $customer,
            $payments,
            $code,
            $customerId,
            $shipping,
            $metadata
        );
    }
    
    /**
     * This function was created only because the current sdk has no support for
     * global amount. The initial value for orderInterest is 0, the neutral
     * element for multiplication, thus, apply this function to every item
     * without set a value other than zero to interest has no effect to
     * orders without interest.
     * @param double $price item price
     * @return integer item price plus interest in cents
     */
    private function getPriceWithInterest($price)
    {
        $interest = number_format($this->orderInterest/100, 2, '.', ',');
        $priceWithInterest = $price + ($price * $interest * 100);
        
        return $priceWithInterest;
    }

    /**
     * Prepare items to API's format
     * @param array $products
     * @return array
     */
    private function prepareItems($products)
    {
        $items = array();
        foreach ($products as $product) {
            $items[] = array(
                'amount'      => $this->getPriceWithInterest($product['price']),
                'description' => $product['name'],
                'quantity'    => $product['quantity']
            );
        }
        return $items;
    }

    /**
     * @param array $orderData
     * @return \MundiAPILib\Models\CreateAddressRequest
     */
    private function createAddressRequest($orderData)
    {
        return new CreateAddressRequest(
            //Street
            $orderData['payment_address_1'],
            //Number
            $orderData['payment_custom_field'][
                $this->openCart->config->get('payment_mundipagg_mapping_number')
            ],
            //Zipcode
            preg_replace('/\D/', '', $orderData['payment_postcode']),
            //Neighborhood
            $orderData['payment_address_2'],
            //City
            $orderData['payment_city'],
            //State
            $orderData['payment_zone_code'],
            //Country
            $orderData['shipping_iso_code_2'],
            //Complement
            $orderData['payment_custom_field'][
                $this->openCart->config->get('payment_mundipagg_mapping_complement')
            ],
            //Metadata
            null
        );
    }

    /**
     * @param array $orderData
     * @param CreateOrderRequest $createAddressRequest
     * @return array
     */
    private function createCustomerRequest($orderData, $createAddressRequest)
    {
        return array(
            'name'     => $orderData['payment_firstname']." ".$orderData['payment_lastname'],
            'email'    => $orderData['email'],
            'phone'    => $orderData['telephone'],
            'document' => null,
            'type'     => "",
            'address'   => $createAddressRequest,
            'metadata' => null
        );
    }

    /**
     * @param string $paymentType
     * @param string $cardToken
     * @throws \Exception Unsupported payment type
     * @return array
     */
    private function preparePayments($paymentType, $cardToken)
    {
        switch ($paymentType) {
            case 'boleto':
                return $this->getBoletoPaymentDetails();
            case 'creditCard':
                return $this->getCreditCardPaymentDetails(
                    $cardToken,
                    $this->orderInstallments
                );
            default:
                /** TODO: log it */
                throw new \Exception('Unsupported payment type');
        }
    }

    private function getBoletoPaymentDetails()
    {
        $boleto = new Boleto($this->openCart);

        return array(
            array(
                'payment_method' => 'boleto',
                'boleto' => array(
                    'bank'         => $boleto->getBank(),
                    'instructions' => $boleto->getInstructions(),
                    'due_at'       => $boleto->getDueDate()
                )
            )
        );
    }

    private function getCreditCardPaymentDetails($token, $installments)
    {
        return array(
            array(
                'payment_method' => 'credit_card',
                'credit_card' => array(
                    'installments' => $installments,
                    'card_token' => $token
                )
            )
        );
    }

     /**
     * @param array $orderData
     * @param CreateAddressRequest $createAddressRequest
     * @param array $cart
     * @return CreateShippingRequest
     */
    private function createShippingRequest($orderData, $createAddressRequest, $cart)
    {
        if ($cart->hasShipping()) {
            $shipping = array(
                'amountInCents' => $cart->session->data['shipping_method']['cost'] * 100,
                'description' => $cart->session->data['shipping_method']['title'],
                'recipientName' => $orderData['shipping_firstname'] . " " . $orderData['shipping_lastname'],
                'recipientPhone' => $orderData['telephone'],
                'addressId' => null
            );

            return new CreateShippingRequest(
                $shipping['amountInCents'],
                $shipping['description'],
                $shipping['recipientName'],
                $shipping['recipientPhone'],
                $shipping['addressId'],
                $createAddressRequest
            );
        }
    }

    /**
     * @param int $customerId
     * @return String
     */
    private function getMundipaggCustomerId($customerId)
    {
        $customer = $this->mundipaggCustomerModel->get($customerId);
        if ($customer['mundipagg_customer_id']) {
            return $customer['mundipagg_customer_id'];
        }
        return null;
    }
    
    public function setCustomerModel($mundipaggCustomerModel)
    {
        $this->mundipaggCustomerModel = $mundipaggCustomerModel;
    }

    /**
     * Update opencart order status with the mundipagg translated status
     *
     * @param mixed $orderStatus
     * @return void
     */
    public function updateOrderStatus($orderStatus)
    {
        $this->openCart->load->model('checkout/order');
        $this->openCart->load->model('extension/payment/mundipagg_order_processing');

        $this->openCart->model_checkout_order->addOrderHistory(
            $this->openCart->session->data['order_id'],
            $orderStatus,
            '',
            true
        );

        $this->openCart->load->model('extension/payment/mundipagg_order_processing');

        $this->openCart->model_extension_payment_mundipagg_order_processing->setOrderStatus(
            $this->openCart->session->data['order_id'],
            $orderStatus
        );
    }

    /**
     * It maps the statuses from mundipagg and those used in opencart
     *
     * @param mixed $response
     * @return string
     */
    public function translateStatusFromMP($response)
    {
        $statusFromMP = strtolower($response->status);

        $this->openCart->load->model('localisation/order_status');
        $statusModel = $this->openCart->model_localisation_order_status;

        switch ($statusFromMP) {
            case 'paid':
                $status = $statusModel->getOrderStatus(2)['order_status_id'];
                break;
            case 'pending':
                $status = $statusModel->getOrderStatus(1)['order_status_id'];
                break;
            case 'canceled':
                $status = $statusModel->getOrderStatus(7)['order_status_id'];
                break;
            case 'failed':
                $status = $statusModel->getOrderStatus(10)['order_status_id'];
                break;
            default:
                $status = false;
        }

        return $status;
    }
}
