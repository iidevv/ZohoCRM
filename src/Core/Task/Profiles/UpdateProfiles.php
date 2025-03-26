<?php

namespace Iidev\ZohoCRM\Core\Task\Profiles;

use Iidev\ZohoCRM\Core\Dispatcher\Profiles\UpdateProfilesDispatcher;
use Symfony\Component\Messenger\MessageBusInterface;
use XCart\Container;
use Iidev\ZohoCRM\Core\Task\Base\Periodic;

class UpdateProfiles extends Periodic
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
        return "ZohoCRM:UpdateProfiles";
    }

    /**
     * @inheritDoc
     */
    protected function runStep()
    {
        $dispatcher = new UpdateProfilesDispatcher();
        $message = $dispatcher->getMessage();

        $this->bus = Container::getContainer() ? Container::getContainer()->get('messenger.default_bus') : null;
        $this->bus->dispatch($message);
    }

    /**
     * @inheritDoc
     */
    protected function getPeriod()
    {
        return static::INT_15_MIN;
    }
}
