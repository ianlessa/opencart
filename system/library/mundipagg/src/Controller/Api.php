<?php

namespace Mundipagg\Controller;

require_once DIR_SYSTEM . 'library/mundipagg/vendor/autoload.php';

use Mundipagg\Enum\WebHookEnum;
use Mundipagg\Log;
use Mundipagg\LogMessages;
use Mundipagg\Model\WebHook as WebHookModel;
use Mundipagg\Model\Installments;
use Mundipagg\Enum\OrderstatusEnum;


class Api
{
    private $data;
    private $verb;
    private $model;

    public function __construct($data, $verb, $openCart)
    {
        $this->data = $data;
        $this->verb = $verb;
        $this->model = new Installments($openCart->db);
    }

    /**
     * I've made it this way so we can have all the combinations of
     * $verb with $endpoint, things like getInstallments and postInstallments
     * can then exist.
     *
     * @param $endpoint
     * @param $arguments
     * @return array
     */
    public function __call($endpoint, $arguments)
    {
        $method = $this->verb . ucfirst($endpoint);

        if (method_exists($this, $method)) {
            return $this->{$method}($this->data[$this->verb]);
        }

        return [
            'status_code' => 404,
            'payload' => ['error' => 'endpoint not found']
        ];
    }

    private function getInstallments($arguments)
    {
        $brand = $arguments['brand'];
        $total = $arguments['total'];

        if (!isset($brand, $total)) {
            return $this->notFoundResponse('missing parameters');
        }

        $installments = $this->model->getInstallmentsFor($brand, $total);

        if (!$installments) {
            return $this->notFoundResponse('wrong request');
        }

        return [
            'status_code' => 200,
            'payload' => $installments
        ];
    }

    private function notFoundResponse($message)
    {
        return [
            'status_code' => 404,
            'payload' => ['error' => $message]
        ];
    }
}
