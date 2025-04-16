<?php

namespace Iidev\ZohoCRM\Core\Task\Orders;

use Iidev\ZohoCRM\Core\Dispatcher\Orders\CreateOrderMessagesDispatcher;
use Symfony\Component\Messenger\MessageBusInterface;
use XCart\Container;
use Iidev\ZohoCRM\Core\Task\Base\Periodic;

class CreateOrderMessages extends Periodic
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
        return "ZohoCRM:CreateOrderMessages";
    }

    /**
     * @inheritDoc
     */
    protected function runStep()
    {
        $dispatcher = new CreateOrderMessagesDispatcher();
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
