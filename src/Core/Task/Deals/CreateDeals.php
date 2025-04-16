<?php

namespace Iidev\ZohoCRM\Core\Task\Deals;

use Iidev\ZohoCRM\Core\Dispatcher\Deals\CreateDealsDispatcher;
use Symfony\Component\Messenger\MessageBusInterface;
use XCart\Container;
use Iidev\ZohoCRM\Core\Task\Base\Periodic;

class CreateDeals extends Periodic
{
    /**
     * @var mixed|null
     */
    protected ?MessageBusInterface $bus;

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return "ZohoCRM:CreateDeals";
    }

    /**
     * @inheritDoc
     */
    protected function runStep()
    {
        $dispatcher = new CreateDealsDispatcher();
        $message = $dispatcher->getMessage();

        $this->bus = Container::getContainer() ? Container::getContainer()->get('messenger.default_bus') : null;
        $this->bus->dispatch($message);
    }

    /**
     * @inheritDoc
     */
    protected function getPeriod()
    {
        return static::INT_5_MIN;
    }
}
