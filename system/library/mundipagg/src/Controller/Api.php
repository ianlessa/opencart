<?php

namespace Mundipagg\Controller;

require_once DIR_SYSTEM . 'library/mundipagg/vendor/autoload.php';

use Mundipagg\Enum\WebHookEnum;
use Mundipagg\Log;
use Mundipagg\LogMessages;
use Mundipagg\Model\WebHook as WebHookModel;
use Mundipagg\Enum\OrderstatusEnum;


class Api
{
    private $data;
    private $verb;

    public function __construct($data, $verb)
    {
        $this->data = $data;
        $this->verb = $verb;
    }

    // I'm changing $name to $endpoint, if something weird happen, it could be here
    public function __call($endpoint, $arguments)
    {
        $method = $this->verb . ucfirst($endpoint);

        if (method_exists($this, $method)) {
            return $this->{$method}($this->data[$this->verb]);
        }

        return [
            'status_code' => 404,
            'payload' => json_encode(['error' => 'endpoint not found'])
        ];
    }

    private function getInstallments($arguments)
    {
        $brand = $arguments['brand'];
        $total = $arguments['total'];

        if (!isset($brand, $total)) {
            return $this->sendNotFoundRequest('missing parameters');
        }

        return $this->getInstallmentsFor($brand, $total);
    }

    private function getInstallmentsFor($brand, $total)
    {

    }

    private function sendNotFoundRequest($message)
    {
        return [
            'status_code' => 404,
            'payload' => json_encode(['error' => $message])
        ];
    }
}
