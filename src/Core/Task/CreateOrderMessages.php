<?php

namespace Iidev\ZohoCRM\Core\Task;

use Iidev\ZohoCRM\Core\Dispatcher\CreateOrderMessagesDispatcher;
use Symfony\Component\Messenger\MessageBusInterface;
use XCart\Container;
use XLite\Core\Task\Base\Periodic;

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
        return static::t('Create order messages [ZohoCRM]');
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

    public function isReady()
    {
        return false;
    }

    protected function isValid()
    {
        return false;
    }
}
