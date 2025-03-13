<?php

namespace Iidev\ZohoCRM\Core\Factory\Commands;

use Iidev\ZohoCRM\Core\Command\Push\PushOrdersCommand;

class PushOrdersCommandFactory
{
    public function __construct()
    {
    }

    public function createCommand(array $entityIds)
    {
        return new PushOrdersCommand($entityIds);
    }
}
