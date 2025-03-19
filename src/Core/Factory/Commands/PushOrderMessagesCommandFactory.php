<?php

namespace Iidev\ZohoCRM\Core\Factory\Commands;

use Iidev\ZohoCRM\Core\Command\Push\Orders\PushOrderMessagesCommand;

class PushOrderMessagesCommandFactory
{
    public function __construct()
    {
    }

    public function createCommand(array $entityIds)
    {
        return new PushOrderMessagesCommand($entityIds);
    }
}
