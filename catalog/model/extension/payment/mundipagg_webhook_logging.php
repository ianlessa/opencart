<?php
use Mundipagg\Log;

class ModelExtensionPaymentMundipaggWebhookLogging extends Model
{

    /**
     * Log data from API
     * @param Json $requestData
     * @return void
     */
    public function logRequestData($jsonRequest, $methodName = '')
    {
        $request = json_decode($jsonRequest);
        $orderId = "";
        if (isset($request->data->code)) {
            $orderId = $request->data->code;
        }

        Log::create()
            ->info(LogMessages::REQUEST_INFO, $methodName)
            ->withOrderId($orderId)
            ->withRequest($jsonRequest);
    }

    /**
     * @param String $orderData
     */
    public function orderAlredyUpdated($orderData, $methodName = "")
    {
        Log::create()
            ->info(LogMessages::ORDER_ALREADY_UPDATED, $methodName)
            ->withOrderId($orderData->code);
    }

    /**
     * @param String $orderData
     */
    public function orderStatusChanged($orderId, $status, $methodName = "")
    {
        Log::create()
            ->info(LogMessages::ORDER_STATUS_CHANGED, $methodName)
            ->withOrderStatus($orderData->status);
    }

    /**
     * @param $mundipaggOrderId
     * @param string $methodName
     * @internal param String $orderData
     */
    public function orderNotFound($mundipaggOrderId, $methodName = "")
    {
        Log::create()
            ->debug(LogMessages::ORDER_NOT_FOUND_IN_ORDER_TABLE, $methodName)
            ->withMundiOrderId($mundipaggOrderId);
    }

    /**
     * @param Exception $exception
     * @param int $orderId
     */
    public function errorDurringOrderUpdate($exception, $orderId = null, $methodName = "")
    {
        Log::create()
            ->info(Log::ERROR_DURING_STATUS_UPDATE, $methodName)
            ->withOrderId($orderId)
            ->withException($exception->getMessage());
    }
}
