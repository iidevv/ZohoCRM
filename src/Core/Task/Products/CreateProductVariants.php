<?php

namespace Iidev\ZohoCRM\Core\Task\Products;

use Iidev\ZohoCRM\Core\Dispatcher\Products\CreateProductVariantsDispatcher;
use Symfony\Component\Messenger\MessageBusInterface;
use XCart\Container;
use Iidev\ZohoCRM\Core\Task\Base\Periodic;

class CreateProductVariants extends Periodic
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
        return "ZohoCRM:CreateProductVariants";
    }

    /**
     * @inheritDoc
     */
    protected function runStep()
    {
        $this->bus = Container::getContainer() ? Container::getContainer()->get('messenger.default_bus') : null;

        $dispatcherVariants = new CreateProductVariantsDispatcher();
        $message = $dispatcherVariants->getMessage();
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
