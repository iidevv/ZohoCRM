<?php

namespace Iidev\ZohoCRM\Core\Dispatcher;

use Iidev\ZohoCRM\Core\Factory\Commands\PushQuotesCommandFactory;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use XCart\Container;
use XLite\Core\Database;
use XLite\Model\Order;

class CreateQuotesDispatcher extends Dispatcher
{
    protected ExportMessage $message;

    private array $orders = [];

    public function __construct()
    {
        $entityIds = Database::getRepo(Order::class)->findQuoteIdsToCreateInZoho();

        $this->orders = Database::getRepo(Order::class)->findByIds($entityIds);
        $this->createProfilesAndProducts();

        /** @var PushQuotesCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get('Iidev\ZohoCRM\Core\Factory\Commands\PushQuotesCommandFactory') : null;
        $command = $commandFactory->createCommand($entityIds);
        $this->message = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }
}
