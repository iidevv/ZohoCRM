<?php

namespace Iidev\ZohoCRM\Core\Dispatcher\Quotes;

use Iidev\ZohoCRM\Core\Factory\Commands\Quotes\UpdateQuotesCommandFactory;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use XCart\Container;
use XLite\Core\Database;
use XLite\Model\Order;
use Iidev\ZohoCRM\Core\Dispatcher\Dispatcher;

class UpdateQuotesDispatcher extends Dispatcher
{
    protected ExportMessage $message;

    public function __construct()
    {
        $entityIds = Database::getRepo(Order::class)->findQuoteIdsToUpdateInZoho();

        /** @var UpdateQuotesCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get(UpdateQuotesCommandFactory::class) : null;
        $command = $commandFactory->createCommand($entityIds);
        $this->message = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }
}
