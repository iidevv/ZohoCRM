<?php

namespace Iidev\ZohoCRM\Core\SDK;

use XLite\Core\Config;
use XLite\InjectLoggerTrait;
use Exception;
use com\zoho\api\authenticator\OAuthBuilder;
use com\zoho\api\authenticator\store\FileStore;
use com\zoho\crm\api\InitializeBuilder;
use com\zoho\crm\api\SDKConfigBuilder;
use com\zoho\crm\api\dc\USDataCenter;
use com\zoho\api\logger\LogBuilder;
use com\zoho\api\logger\Levels;

class SDK
{
    use InjectLoggerTrait;

    public function __construct()
    {
    }

    protected function resetTokenStore()
    {
        $storePath =Config::getInstance()->Iidev->ZohoCRM->store_path;
        $tokenFilePath = "{$storePath}/zoho_sdk_token.txt";

        if (file_exists($tokenFilePath)) {
            unlink($tokenFilePath);
        }
    }

    protected function getToken($data)
    {
        if (isset($data['client_id']) && isset($data['client_secret']) && isset($data['scope_code'])) {
            $this->resetTokenStore();

            $token = (new OAuthBuilder())
                ->clientId($data['client_id'])
                ->clientSecret($data['client_secret'])
                ->grantToken($data['scope_code'])
                ->build();
        } else {
            
            $storePath =Config::getInstance()->Iidev->ZohoCRM->store_path;
            $tokenstore = new FileStore("{$storePath}/zoho_sdk_token.txt");
            $token = $tokenstore->findTokenById(1);
        }

        if (!$token) {
            throw new Exception("Undefined token", 1);
        }

        return $token;
    }

    public function initialize($data)
    {
        $environment = USDataCenter::PRODUCTION();
        $token = $this->getToken($data);
        $storePath =Config::getInstance()->Iidev->ZohoCRM->store_path;
        $tokenstore = new FileStore("{$storePath}/zoho_sdk_token.txt");

        $level = Config::getInstance()->Iidev->ZohoCRM->debug_enabled ? Levels::DEBUG : Levels::ERROR;

        $logger = (new LogBuilder())
            ->level($level)
            ->filePath("{$storePath}/zoho_sdk_log.log")
            ->build();

        $autoRefreshFields = false;
        $pickListValidation = false;
        $connectionTimeout = 2; //The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
        $timeout = 2; //The maximum number of seconds to allow cURL functions to execute.
        $enableSSLVerification = false;
        $sdkConfig = (new SDKConfigBuilder())
            ->autoRefreshFields($autoRefreshFields)
            ->pickListValidation($pickListValidation)
            ->sslVerification($enableSSLVerification)
            ->connectionTimeout($connectionTimeout)
            ->timeout($timeout)
            ->build();

        (new InitializeBuilder())
            ->environment($environment)
            ->store($tokenstore)
            ->SDKConfig($sdkConfig)
            ->token($token)
            ->logger($logger)
            ->initialize();
    }
}
