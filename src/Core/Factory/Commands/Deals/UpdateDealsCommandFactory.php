<?php

namespace Iidev\ZohoCRM\Core\Factory\Commands\Deals;

use Iidev\ZohoCRM\Core\Command\Push\Deals\UpdateDealsCommand;

class UpdateDealsCommandFactory
{
    public function __construct()
    {
    }

    public function createCommand(array $entityIds)
    {
        return new UpdateDealsCommand($entityIds);
    }
}
