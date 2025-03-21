<?php

namespace Iidev\ZohoCRM\Core\Factory\Commands\Quotes;

use Iidev\ZohoCRM\Core\Command\Push\Quotes\UpdateQuotesCommand;

class UpdateQuotesCommandFactory
{
    public function __construct()
    {
    }

    public function createCommand(array $entityIds)
    {
        return new UpdateQuotesCommand($entityIds);
    }
}
