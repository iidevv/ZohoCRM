<?php

namespace Iidev\ZohoCRM\Core\Dispatcher\Orders;

use Iidev\ZohoCRM\Core\Factory\Commands\Orders\PushOrderMessagesCommandFactory;
use Iidev\ZohoCRM\Core\Factory\Commands\Profiles\PushProfilesCommandFactory;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use XCart\Container;
use XLite\Core\Database;
use \XC\VendorMessages\Model\Message;

class CreateOrderMessagesDispatcher
{
    protected ExportMessage $message;

    protected array $messages = [];

    public function __construct()
    {
        $entityIds = Database::getRepo(Message::class)->findOrderMessageIdsToCreateInZoho();

        $this->messages = Database::getRepo(Message::class)->findByIds($entityIds);
        $this->createProfiles();
        
        /** @var PushOrderMessagesCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get(PushOrderMessagesCommandFactory::class) : null;
        $command = $commandFactory->createCommand($entityIds);
        $this->message = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }

    protected function createProfiles()
    {
        $profileIds = [];
        foreach ($this->messages as $message) {
            $profile = $message->getAuthor()?->getOrigProfile();
            
            if ($profile && !$profile->getZohoModel()?->getZohoId()) {
                $profileIds[] = $profile->getProfileId();
            }
        }

        $profileIds = array_unique($profileIds);

        if (!empty($profileIds)) {
            /** @var PushProfilesCommandFactory $profilesFactory */
            $profilesFactory = Container::getContainer()->get('Iidev\ZohoCRM\Core\Factory\Commands\PushProfilesCommandFactory');
            $profilesCommand = $profilesFactory->createCommand($profileIds);
            $profilesCommand->execute();
        }
    }
}
