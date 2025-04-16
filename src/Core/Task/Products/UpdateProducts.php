<?php

namespace Iidev\ZohoCRM\Core\Task\Products;

use Iidev\ZohoCRM\Core\Dispatcher\Products\UpdateProductsDispatcher;
use Symfony\Component\Messenger\MessageBusInterface;
use XCart\Container;
use Iidev\ZohoCRM\Core\Task\Base\Periodic;

class UpdateProducts extends Periodic
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
        return "ZohoCRM:UpdateProducts";
    }

    /**
     * @inheritDoc
     */
    protected function runStep()
    {
        $this->bus = Container::getContainer() ? Container::getContainer()->get('messenger.default_bus') : null;
        
        $dispatcherProducts = new UpdateProductsDispatcher();
        $message    = $dispatcherProducts->getMessage();
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
