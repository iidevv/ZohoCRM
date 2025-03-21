<?php

namespace Iidev\ZohoCRM\Core\Factory\Commands\Orders;

use Iidev\ZohoCRM\Core\Command\Push\Orders\UpdateOrdersCommand;

class UpdateOrdersCommandFactory
{
    public function __construct()
    {
    }

    public function createCommand(array $entityIds)
    {
        return new UpdateOrdersCommand($entityIds);
    }
}
