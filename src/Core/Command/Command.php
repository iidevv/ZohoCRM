<?php

namespace Iidev\ZohoCRM\Core\Command;

use Iidev\ZohoCRM\Core\SDK\SDK;

class Command implements ICommand
{
    public function __construct()
    {
        $main = new \Iidev\ZohoCRM\Main();
        
        if (!$main->isInitialized()) {
            throw new \Exception("Zoho SDK is not initialized. Token store is missing or invalid.");
        }
        
        (new SDK())->initialize([]);
    }

    public function execute(): void
    {
    }
}