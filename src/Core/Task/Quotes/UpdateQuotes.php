<?php

namespace Iidev\ZohoCRM\Core\Task\Quotes;

use Iidev\ZohoCRM\Core\Dispatcher\Quotes\UpdateQuotesDispatcher;
use Symfony\Component\Messenger\MessageBusInterface;
use XCart\Container;
use XLite\Core\Task\Base\Periodic;

class UpdateQuotes extends Periodic
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
        return static::t('Update quotes [ZohoCRM]');
    }

    /**
     * @inheritDoc
     */
    protected function runStep()
    {
        if ((int) \XLite\Core\Config::getInstance()->Iidev->ZohoCRM->orders_enable_sync !== 1)
            return;
        
        $dispatcher = new UpdateQuotesDispatcher();
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
