<?php

namespace Iidev\ZohoCRM\Core\Dispatcher\Products;

use Iidev\ZohoCRM\Core\Factory\Commands\Products\UpdateProductsCommandFactory;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use XCart\Container;
use XLite\Core\Database;
use XLite\Model\Product;

class UpdateProductsDispatcher
{
    protected ExportMessage $message;
    
    public function __construct()
    {
        $entityIds = Database::getRepo(Product::class)->findProductIdsToUpdateInZoho();

        /** @var UpdateProductsCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get(UpdateProductsCommandFactory::class) : null;
        $command        = $commandFactory->createCommand($entityIds);
        $this->message  = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }
}
