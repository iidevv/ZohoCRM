<?php

namespace Iidev\ZohoCRM\Core\Dispatcher\Orders;

use Iidev\ZohoCRM\Core\Factory\Commands\Orders\PushOrdersCommandFactory;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use XCart\Container;
use XLite\Core\Database;
use XLite\Model\Order;
use Iidev\ZohoCRM\Core\Dispatcher\Dispatcher;

class CreateOrdersDispatcher extends Dispatcher
{
    protected ExportMessage $message;

    protected array $orders = [];

    public function __construct($entityIds = [])
    {
        if (empty($entityIds)) {
            $entityIds = Database::getRepo(Order::class)->findOrderIdsToCreateInZoho();
        }

        $this->orders = Database::getRepo(Order::class)->findByIds($entityIds);
        $this->createProfilesAndProducts();

        /** @var PushOrdersCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get(PushOrdersCommandFactory::class) : null;
        $command = $commandFactory->createCommand($entityIds);
        $this->message = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }
}
