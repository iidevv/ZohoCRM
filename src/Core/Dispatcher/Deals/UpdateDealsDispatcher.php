<?php

namespace Iidev\ZohoCRM\Core\Dispatcher\Deals;

use Iidev\ZohoCRM\Core\Factory\Commands\Deals\UpdateDealsCommandFactory;
use Iidev\ZohoCRM\Core\Factory\Commands\Deals\UpdateClosedDealsCommandFactory;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use XCart\Container;
use XLite\Core\Database;
use XLite\Model\Cart;
use Iidev\ZohoCRM\Model\ZohoDeal;
use Iidev\ZohoCRM\Core\Dispatcher\Dispatcher;

class UpdateDealsDispatcher extends Dispatcher
{
    protected ExportMessage $message;

    protected array $orders = [];

    public function __construct()
    {
        $entityIds = Database::getRepo(Cart::class)->findDealIdsToUpdateInZoho();

        /** @var UpdateDealsCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get(UpdateDealsCommandFactory::class) : null;
        $command = $commandFactory->createCommand($entityIds);
        $this->message = new ExportMessage($command);


        $closedEntityIds = Database::getRepo(ZohoDeal::class)->findClosedDealIdsToUpdateInZoho();

        /** @var UpdateClosedDealsCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get(UpdateClosedDealsCommandFactory::class) : null;
        $command = $commandFactory->createCommand($closedEntityIds);
        $this->message = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }
}
