<?php

namespace Iidev\ZohoCRM\Core\Factory\Commands;

use Iidev\ZohoCRM\Core\Command\Push\UpdateProductVariantsCommand;

class UpdateProductVariantsCommandFactory
{
    public function __construct()
    {
    }

    public function createCommand(array $entityIds)
    {
        return new UpdateProductVariantsCommand($entityIds);
    }
}
