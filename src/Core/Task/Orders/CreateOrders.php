<?php

namespace Iidev\ZohoCRM\Core\Task\Orders;

use Iidev\ZohoCRM\Core\Dispatcher\Orders\CreateOrdersDispatcher;
use Symfony\Component\Messenger\MessageBusInterface;
use XCart\Container;
use Iidev\ZohoCRM\Core\Task\Base\Periodic;

class CreateOrders extends Periodic
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
        return static::t('Create orders [ZohoCRM]');
    }

    /**
     * @inheritDoc
     */
    protected function runStep()
    {
        if ((int) \XLite\Core\Config::getInstance()->Iidev->ZohoCRM->orders_enable_sync !== 1)
            return;

        $dispatcher = new CreateOrdersDispatcher();
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
