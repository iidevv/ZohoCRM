<?php

namespace Iidev\ZohoCRM\Core\Dispatcher;

use Iidev\ZohoCRM\Core\Factory\Commands\PushOrdersCommandFactory;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use XCart\Container;
use XLite\Core\Database;
use XLite\Model\Order;

class CreateOrdersDispatcher extends Dispatcher
{
    protected ExportMessage $message;

    private array $orders = [];

    public function __construct()
    {
        $entityIds = Database::getRepo(Order::class)->findOrderIdsToCreateInZoho();

        $this->orders = Database::getRepo(Order::class)->findByIds($entityIds);
        $this->createProfilesAndProducts();

        /** @var PushOrdersCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get('Iidev\ZohoCRM\Core\Factory\Commands\PushOrdersCommandFactory') : null;
        $command = $commandFactory->createCommand($entityIds);
        $this->message = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }
}
