<?php

namespace Iidev\ZohoCRM\Core\Dispatcher;

use Iidev\ZohoCRM\Core\Factory\Commands\PushProfilesCommandFactory;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use XCart\Container;
use XLite\Core\Database;
use XLite\Model\Profile;

class CreateProfilesDispatcher
{
    protected ExportMessage $message;
    
    public function __construct()
    {
        $entityIds = Database::getRepo(Profile::class)->findProfileIdsToCreateInZoho();
        
        if (empty($entityIds)) {
            return;
        }

        /** @var PushProfilesCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get('Iidev\ZohoCRM\Core\Factory\Commands\PushProfilesCommandFactory') : null;
        $command        = $commandFactory->createCommand($entityIds);
        $this->message  = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }
}
