<?php

namespace Iidev\ZohoCRM\Core\Dispatcher\Quotes;

use Iidev\ZohoCRM\Core\Factory\Commands\Quotes\PushQuotesCommandFactory;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use XCart\Container;
use XLite\Core\Database;
use XLite\Model\Order;
use Iidev\ZohoCRM\Core\Dispatcher\Dispatcher;

class CreateQuotesDispatcher extends Dispatcher
{
    protected ExportMessage $message;

    protected array $orders = [];

    public function __construct()
    {
        $entityIds = Database::getRepo(Order::class)->findQuoteIdsToCreateInZoho();

        $this->orders = Database::getRepo(Order::class)->findByIds($entityIds);
        $this->createProfilesAndProducts();

        /** @var PushQuotesCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get(PushQuotesCommandFactory::class) : null;
        $command = $commandFactory->createCommand($entityIds);
        $this->message = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }
}
