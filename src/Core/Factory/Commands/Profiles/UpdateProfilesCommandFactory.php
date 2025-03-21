<?php

namespace Iidev\ZohoCRM\Core\Factory\Commands\Profiles;

use Iidev\ZohoCRM\Core\Command\Push\Profiles\UpdateProfilesCommand;

class UpdateProfilesCommandFactory
{
    public function __construct()
    {
    }

    public function createCommand(array $entityIds)
    {
        return new UpdateProfilesCommand($entityIds);
    }
}
