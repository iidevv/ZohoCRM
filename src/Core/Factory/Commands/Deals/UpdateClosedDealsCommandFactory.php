<?php

namespace Iidev\ZohoCRM\Core\Factory\Commands\Deals;

use Iidev\ZohoCRM\Core\Command\Push\Deals\UpdateClosedDealsCommand;

class UpdateClosedDealsCommandFactory
{
    public function __construct()
    {
    }

    public function createCommand(array $entityIds)
    {
        return new UpdateClosedDealsCommand($entityIds);
    }
}
