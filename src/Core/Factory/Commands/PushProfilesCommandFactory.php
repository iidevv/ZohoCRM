<?php

namespace Iidev\ZohoCRM\Core\Factory\Commands;

use Iidev\ZohoCRM\Core\Command\Push\PushProfilesCommand;

class PushProfilesCommandFactory
{
    public function __construct()
    {
    }

    public function createCommand(array $productsIds)
    {
        return new PushProfilesCommand($productsIds);
    }
}
