<?php

namespace Iidev\ZohoCRM\Core\Task\Products;

use Iidev\ZohoCRM\Core\Dispatcher\Products\CreateProductsDispatcher;
use Symfony\Component\Messenger\MessageBusInterface;
use XCart\Container;
use Iidev\ZohoCRM\Core\Task\Base\Periodic;

class CreateProducts extends Periodic
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
        return "ZohoCRM:CreateProducts";
    }

    /**
     * @inheritDoc
     */
    protected function runStep()
    {
        $this->bus = Container::getContainer() ? Container::getContainer()->get('messenger.default_bus') : null;

        $dispatcherProducts = new CreateProductsDispatcher();
        $message = $dispatcherProducts->getMessage();
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
