<?php

namespace Iidev\ZohoCRM\Core\Dispatcher\Profiles;

use Iidev\ZohoCRM\Core\Factory\Commands\Profiles\UpdateProfilesCommandFactory;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use XCart\Container;
use XLite\Core\Database;
use XLite\Model\Profile;

class UpdateProfilesDispatcher
{
    protected ExportMessage $message;
    
    public function __construct()
    {
        $entityIds = Database::getRepo(Profile::class)->findProfileIdsToUpdateInZoho();
        
        /** @var UpdateProfilesCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get(UpdateProfilesCommandFactory::class) : null;
        $command        = $commandFactory->createCommand($entityIds);
        $this->message  = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }
}
