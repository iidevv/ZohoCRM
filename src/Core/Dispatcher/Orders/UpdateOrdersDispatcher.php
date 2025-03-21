<?php

namespace Iidev\ZohoCRM\Core\Dispatcher\Orders;

use Iidev\ZohoCRM\Core\Factory\Commands\Orders\UpdateOrdersCommandFactory;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use XCart\Container;
use XLite\Core\Database;
use XLite\Model\Order;

class UpdateOrdersDispatcher
{
    protected ExportMessage $message;

    protected array $orders = [];

    public function __construct()
    {
        $entityIds = Database::getRepo(Order::class)->findOrderIdsToUpdateInZoho();

        /** @var UpdateOrdersCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get(UpdateOrdersCommandFactory::class) : null;
        $command = $commandFactory->createCommand($entityIds);
        $this->message = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }
}
