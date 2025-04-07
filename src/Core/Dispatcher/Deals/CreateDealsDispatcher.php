<?php

namespace Iidev\ZohoCRM\Core\Dispatcher\Deals;

use Iidev\ZohoCRM\Core\Factory\Commands\Deals\PushDealsCommandFactory;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use XCart\Container;
use XLite\Core\Database;
use XLite\Model\Cart;
use Iidev\ZohoCRM\Core\Dispatcher\Dispatcher;

class CreateDealsDispatcher extends Dispatcher
{
    protected ExportMessage $message;

    protected array $orders = [];

    public function __construct()
    {
        $entityIds = Database::getRepo(Cart::class)->findDealIdsToCreateInZoho();

        $this->orders = Database::getRepo(Cart::class)->findByIds($entityIds);
        
        $this->createProfilesAndProducts();

        /** @var PushDealsCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get(PushDealsCommandFactory::class) : null;
        $command = $commandFactory->createCommand($entityIds);
        $this->message = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }
}
