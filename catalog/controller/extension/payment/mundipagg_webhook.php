<?php

require_once DIR_SYSTEM."library/mundipagg/vendor/autoload.php";

use Mundipagg\Controller\WebHook;

/**
 * ControllerMundipaggWebhook deal with webhook requests
 *
 * The purpose of this class is to centralize
 *
 * @package Mundipagg
 *
 */
class ControllerExtensionPaymentMundipaggWebhook extends Controller
{
    public function index()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $webhook = new WebHook($this, file_get_contents('php://input'));
            $webhook->updateStatus();
        }
    }
}
