<?php

namespace Iidev\ZohoCRM\Controller\Customer;

use XLite\Core\Request;
use \XLite\Core\Config;

class ZohoWebhook extends \XLite\Controller\Customer\ACustomer
{
    public function handleRequest()
    {
        if (Request::getInstance()->isPost()) {
            $this->handlePostRequest();
        } else {
            $this->sendResponse(['error' => 'Invalid request method']);
        }

        exit;
    }

    private function handlePostRequest()
    {
        $headers = getallheaders();
        $token = isset($headers['Token']) ? $headers['Token'] : '';
        $expectedToken = Config::getInstance()->Iidev->ZohoCRM->webhook_token;

        if ($token !== $expectedToken) {
            http_response_code(401);
            exit;
        }

        $data = $_POST;

        if ($data) {
            $service = new \Iidev\ZohoCRM\Core\Service\ZohoWebhookService();
            $service->processZohoRequest($data);

            $this->sendResponse(['success' => true]);
        } else {
            $this->sendResponse(['error' => 'Invalid payload']);
        }
    }

    private function sendResponse($response)
    {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    protected function doNoAction()
    {
        $this->handleRequest();
    }

}
