<?php

namespace Iidev\ZohoCRM\Core\Factory\Commands\Products;

use Iidev\ZohoCRM\Core\Command\Push\Products\PushProductsCommand;

class PushProductsCommandFactory
{
    public function __construct()
    {
    }

    public function createCommand(array $entityIds)
    {
        return new PushProductsCommand($entityIds);
    }
}
