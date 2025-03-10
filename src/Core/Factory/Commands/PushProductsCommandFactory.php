<?php

namespace Iidev\ZohoCRM\Core\Factory\Commands;

use Iidev\ZohoCRM\Core\Command\Push\PushProductsCommand;

class PushProductsCommandFactory
{
    public function __construct()
    {
    }

    public function createCommand(array $productsIds)
    {
        return new PushProductsCommand($productsIds);
    }
}
