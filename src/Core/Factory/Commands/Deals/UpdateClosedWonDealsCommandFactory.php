<?php

namespace Iidev\ZohoCRM\Core\Factory\Commands\Deals;

use Iidev\ZohoCRM\Core\Command\Push\Deals\UpdateClosedWonDealsCommand;

class UpdateClosedWonDealsCommandFactory
{
    public function __construct()
    {
    }

    public function createCommand(array $entityIds)
    {
        return new UpdateClosedWonDealsCommand($entityIds);
    }
}
