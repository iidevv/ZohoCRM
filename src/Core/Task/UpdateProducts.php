<?php

namespace Iidev\ZohoCRM\Core\Task;

use Iidev\ZohoCRM\Core\Dispatcher\UpdateProductsDispatcher;
use Symfony\Component\Messenger\MessageBusInterface;
use XCart\Container;
use XLite\Core\Task\Base\Periodic;

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
        return static::t('Update products [ZohoCRM]');
    }

    /**
     * @inheritDoc
     */
    protected function runStep()
    {
        $dispatcher = new UpdateProductsDispatcher();
        $message    = $dispatcher->getMessage();

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

    public function isReady()
    {
        return false;
    }

    protected function isValid()
    {
        return false;
    }
}
