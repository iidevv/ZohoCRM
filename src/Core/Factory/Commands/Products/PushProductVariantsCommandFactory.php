<?php

namespace Iidev\ZohoCRM\Core\Factory\Commands\Products;

use Iidev\ZohoCRM\Core\Command\Push\Products\PushProductVariantsCommand;

class PushProductVariantsCommandFactory
{
    public function __construct()
    {
    }

    public function createCommand(array $entityIds)
    {
        return new PushProductVariantsCommand($entityIds);
    }
}
