<?php

namespace Iidev\ZohoCRM\Core\Factory\Commands\Profiles;

use Iidev\ZohoCRM\Core\Command\Push\Profiles\PushProfilesCommand;

class PushProfilesCommandFactory
{
    public function __construct()
    {
    }

    public function createCommand(array $entityIds)
    {
        return new PushProfilesCommand($entityIds);
    }
}
