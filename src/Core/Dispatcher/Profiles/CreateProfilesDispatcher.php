<?php

namespace Iidev\ZohoCRM\Core\Dispatcher\Profiles;

use Iidev\ZohoCRM\Core\Factory\Commands\Profiles\PushProfilesCommandFactory;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use XCart\Container;
use XLite\Core\Database;
use XLite\Model\Profile;

class CreateProfilesDispatcher
{
    protected ExportMessage $message;

    public function __construct($entityIds = [])
    {
        if (empty($entityIds)) {
            $entityIds = Database::getRepo(Profile::class)->findProfileIdsToCreateInZoho();
        }

        /** @var PushProfilesCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get(PushProfilesCommandFactory::class) : null;
        $command = $commandFactory->createCommand($entityIds);
        $this->message = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }
}
