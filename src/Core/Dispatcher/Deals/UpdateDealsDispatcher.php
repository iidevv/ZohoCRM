<?php

namespace Iidev\ZohoCRM\Core\Dispatcher\Deals;

use Iidev\ZohoCRM\Core\Factory\Commands\Deals\UpdateDealsCommandFactory;
use Iidev\ZohoCRM\Core\Factory\Commands\Deals\UpdateClosedWonDealsCommandFactory;
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


        $closedWonEntityIds = Database::getRepo(ZohoDeal::class)->findClosedWonDealIdsToUpdateInZoho();

        /** @var UpdateClosedWonDealsCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get(UpdateClosedWonDealsCommandFactory::class) : null;
        $command = $commandFactory->createCommand($closedWonEntityIds);
        $this->message = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }
}
