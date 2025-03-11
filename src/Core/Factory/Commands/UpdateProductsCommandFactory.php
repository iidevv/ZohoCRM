<?php

namespace Iidev\ZohoCRM\Core\Factory\Commands;

use Iidev\ZohoCRM\Core\Command\Push\UpdateProductsCommand;

class UpdateProductsCommandFactory
{
    public function __construct()
    {
    }

    public function createCommand(array $productsIds)
    {
        return new UpdateProductsCommand($productsIds);
    }
}
