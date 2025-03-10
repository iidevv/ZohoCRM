<?php

namespace Iidev\ZohoCRM\Core\Dispatcher;

use Iidev\ZohoCRM\Core\Factory\Commands\PushProductsCommandFactory;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use XCart\Container;
use XLite\Core\Database;
use XLite\Model\Product;

class CreateProductsDispatcher
{
    protected ExportMessage $message;
    
    public function __construct()
    {
        $productsIds = Database::getRepo(Product::class)->findProductIdsToCreateInZoho();

        /** @var PushProductsCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get('Iidev\ZohoCRM\Core\Factory\Commands\PushProductsCommandFactory') : null;
        $command        = $commandFactory->createCommand($productsIds);
        $this->message  = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }
}
