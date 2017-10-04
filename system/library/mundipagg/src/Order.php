<?php
namespace Mundipagg;

require_once DIR_SYSTEM . 'library/mundipagg/vendor/autoload.php';

use Mundipagg\Controller\CreditCard;
use Mundipagg\Log;
use Mundipagg\LogMessages;
use MundiAPILib\MundiAPIClient;
use MundiAPILib\Models\CreateShippingRequest;
use MundiAPILib\Models\CreateOrderRequest;
use MundiAPILib\Models\CreateAddressRequest;
use MundiAPILib\Models\CreateCustomerRequest;
use Mundipagg\Controller\Settings;
use Mundipagg\Controller\Boleto;
use Unirest\Exception;

class Order
{
    private $orderInterest;
    private $orderInstallments;
    
    private $apiClient;
    private $orderInstance;
    private $customerInstance;
    private $openCart;
    private $settings;

    private $mundipaggCustomerModel;

    /**
     * @param Object $mundipaggCustomerModel
     * @param array $openCart
     */
    public function __construct($mundipaggCustomerModel, $openCart)
    {
        $this->settings = new Settings($openCart);

        $this->openCart = $openCart;
        $this->orderInterest = 0;
        $this->mundipaggCustomerModel = $mundipaggCustomerModel;

        $this->apiClient = new MundiAPIClient($this->settings->getSecretKey(), $this->settings->getPassword());

        $this->orderInstance = $this->apiClient->getOrders();
        $this->customerInstance = $this->apiClient->getCustomers();
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

        try {
            Log::create()
                ->info(LogMessages::CREATE_ORDER_MUNDIPAGG_REQUEST, __METHOD__)
                ->withOrderId($orderData['order_id'])
                ->withRequest(json_encode($CreateOrderRequest, JSON_PRETTY_PRINT));

            $order = $this->orderInstance->createOrder($CreateOrderRequest);

            Log::create()
                ->info(LogMessages::CREATE_ORDER_MUNDIPAGG_RESPONSE, __METHOD__)
                ->withOrderId($orderData['order_id'])
                ->withResponse(json_encode($order, JSON_PRETTY_PRINT));

            return $order;
        } catch (\Exception $exc) {
            Log::create()
                ->error(LogMessages::API_REQUEST_FAIL, __METHOD__)
                ->withOrderId($orderData['order_id'])
                ->withException($exc->getMessage())
                ->withRequest(json_encode($CreateOrderRequest))
                ->withLineNumber(__LINE__);
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
        $priceWithInterest = $price + ($price * $interest);
        
        return (int) $priceWithInterest * 100;
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
}
