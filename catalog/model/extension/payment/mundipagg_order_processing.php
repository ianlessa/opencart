<?php

use Mundipagg\Enum\OrderstatusEnum;

class ModelExtensionPaymentMundipaggOrderProcessing extends Model
{
    private $webhookLogging;
    private $webhookLanguage;

    public function load()
    {
        $this->load->model('extension/payment/mundipagg_webhook_logging');
        $this->load->model('setting/setting');
        $this->load->language('extension/payment/mundipagg');
        $this->load->model('localisation/currency');
        $this->webhookLogging = $this->model_extension_payment_mundipagg_webhook_logging;
        $this->webhookLanguage = $this->language->get('order_history_update');
    }

    /**
     * - Partial paid value in order history
     * - Order status = Processing
     * @param Json $dataRequest
     */
    public function setChargePaid($dataRequest)
    {
        $this->load();
        $comment =
            $this->webhookLanguage['chargePaid'] .
            $this->moneyFormat($dataRequest->data->paid_amount) .
            $this->webhookLanguage['of'] .
            $this->moneyFormat($dataRequest->data->amount)
            ;
        $this->addOrderHistory(
            $dataRequest->data->code,
            OrderstatusEnum::ORDER_PROCESSING_ID,
            $comment,
            0
        );
    }
    
    /**
     * - Paid value in order history
     * - Order status = Processing
     * @param Json $dataRequest
     */
    public function setChargeOverPaid($dataRequest)
    {
        $this->load();
        $comment =
            $this->webhookLanguage['chargeOverPaid'] .
            $this->moneyFormat($dataRequest->data->paid_amount) .
            $this->webhookLanguage['of'] .
            $this->moneyFormat($dataRequest->data->amount)
            ;
        $this->addOrderHistory(
            $dataRequest->data->code,
            OrderstatusEnum::ORDER_PROCESSING_ID,
            $comment,
            0
        );
        $this->setOrderStatus(
            $dataRequest->data->code,
            OrderstatusEnum::ORDER_PROCESSING_ID
        );
    }

    /**
     * Refunded value in order history
     * Order status = voided
     * @param Json $dataRequest
     */
    public function setChargeRefunded($dataRequest)
    {
        $this->load();
        $comment = $this->webhookLanguage['chargeRefunded'];
        $this->addOrderHistory(
            $dataRequest->data->code,
            OrderstatusEnum::ORDER_CANCELED_ID,
            $comment,
            1
        );
        $this->setOrderStatus(
            $dataRequest->data->code,
            OrderstatusEnum::ORDER_CANCELED_ID
        );
    }

    /**
     * Order status = canceled
     * @param Json $dataRequest
     */
    public function setOrderCanceled($dataRequest)
    {
        $this->load();
        $comment =
            $this->webhookLanguage['orderCanceled']
            ;

        $this->addOrderHistory(
            $dataRequest->data->code,
            OrderstatusEnum::ORDER_CANCELED_ID,
            $comment,
            1
        );
        $this->setOrderStatus(
            $dataRequest->data->code,
            OrderstatusEnum::ORDER_CANCELED_ID
        );
    }

    /**
     * - Total paid value in order history
     * - Order status = Processing
     * @param Json $dataRequest
     */
    public function setOrderPaid($dataRequest)
    {
        $this->load();
        $comment =
            $this->webhookLanguage['orderPaid'] .
            $this->moneyFormat($dataRequest->data->amount)
            ;
        $this->addOrderHistory(
            $dataRequest->data->code,
            OrderstatusEnum::ORDER_PROCESSING_ID,
            $comment,
            1
        );
        $this->setOrderStatus(
            $dataRequest->data->code,
            OrderstatusEnum::ORDER_PROCESSING_ID
        );
    }

    private function getCurrency()
    {
        return
            $this->model_localisation_currency->getCurrencyByCode(
                $this->config->get('config_currency')
            );
    }

    /**
     * Returns money format by Opencart
     * @example $1000,00
     * @param int $number in cents
     * @return String
     */
    private function moneyFormat($numberInCents)
    {
        $currency = $this->getCurrency();
        return
            $currency['symbol_left'] .
            number_format($numberInCents * 0.01, 2) .
            $currency['symbol_right']
        ;
    }
    
    /**
     * Update all order statuses
     * @param int $orderId
     * @param int $statusId
     * @return boolean
     */
    public function setOrderStatus($orderId, $statusId)
    {
        try {
            $sql = $this->getUpdateSql($statusId, $orderId);
            $this->db->query($sql);
            return true;
        } catch (Exception $exc) {
            $this->webhookLogging->errorDurringOrderUpdate(
                $exc,
                $orderId,
                $statusId
            );
        }
    }

    /**
     * Update order history
     * @param int $orderId
     * @param int $orderStatus
     * @param String $comment
     * @param int $sendEmail
     */
    public function addOrderHistory($orderId, $orderStatus, $comment, $sendEmail)
    {
        $sql = "
            INSERT `" . DB_PREFIX . "order_history` 
            (
                order_id, 
                order_status_id, 
                notify, 
                comment,
                date_added
            )
            values(
                " . $orderId . ",
                " . $orderStatus . ",
                " . $sendEmail . ",
                '" . $comment . "',
                now()
            )
        ";

        try {
            $this->db->query($sql);
        } catch (\Exception $e) {

        }
    }

    /**
     * @param int $orderId
     * @return int
     */
    public function orderExists($orderId)
    {
        $sql = "SELECT opencart_id FROM `".DB_PREFIX."mundipagg_order`
                WHERE mundipagg_id = '".$orderId . "'
                LIMIT 1";
        $query = $this->db->query($sql);
        return $query->num_rows;
    }
    
    private function getUpdateSql($status, $orderId)
    {
        return
        "UPDATE `" . DB_PREFIX . "order`
        SET order_status_id = " . $status . "
        WHERE order_id = ". $orderId;
    }

    public function getCurrentOrderStatus($orderId)
    {
        $sql = "
            SELECT order_status_id FROM
            `" . DB_PREFIX . "order`
            WHERE order_id = " . $orderId;
        $query = $this->db->query($sql);
        if ($query->num_rows > 0) {
            return trim($query->row['order_status_id']);
        }
    }
}
