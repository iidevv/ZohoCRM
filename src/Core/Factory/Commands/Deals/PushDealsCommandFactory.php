<?php

namespace Iidev\ZohoCRM\Core\Factory\Commands\Deals;

use Iidev\ZohoCRM\Core\Command\Push\Deals\PushDealsCommand;

class PushDealsCommandFactory
{
    public function __construct()
    {
    }

    public function createCommand(array $entityIds)
    {
        return new PushDealsCommand($entityIds);
    }
}
