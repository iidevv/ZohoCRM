<?php

namespace Iidev\ZohoCRM\Core\Task\Products;

use Iidev\ZohoCRM\Core\Dispatcher\Products\CreateProductsDispatcher;
use Iidev\ZohoCRM\Core\Dispatcher\Products\CreateProductVariantsDispatcher;
use Symfony\Component\Messenger\MessageBusInterface;
use XCart\Container;
use XLite\Core\Task\Base\Periodic;

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
        return static::t('Create products [ZohoCRM]');
    }

    /**
     * @inheritDoc
     */
    protected function runStep()
    {
        $this->bus = Container::getContainer() ? Container::getContainer()->get('messenger.default_bus') : null;

        $dispatcherProducts = new CreateProductsDispatcher();
        $message    = $dispatcherProducts->getMessage();
        $this->bus->dispatch($message);
        
        $dispatcherVariants = new CreateProductVariantsDispatcher();
        $message    = $dispatcherVariants->getMessage();
        $this->bus->dispatch($message);
    }

    /**
     * @inheritDoc
     */
    protected function getPeriod()
    {
        return static::INT_15_MIN;
    }

    public function isReady()
    {
        return false;
    }

    protected function isValid()
    {
        return false;
    }
}
