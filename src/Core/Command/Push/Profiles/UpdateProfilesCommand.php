<?php

namespace Iidev\ZohoCRM\Core\Command\Push\Profiles;

use Exception;
use Iidev\ZohoCRM\Core\Command\Command;
use XLite\Core\Database;
use XLite\Model\Profile;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\Contacts;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\record\Field;
use com\zoho\crm\api\util\Choice;


class UpdateProfilesCommand extends Command
{
    public function __construct(
        array $entityIds
    ) {
        parent::__construct();
        $this->entityIds = $entityIds;
    }

    public function execute(): void
    {
        try {
            $recordOperations = new RecordOperations('Contacts');
            $bodyWrapper = new BodyWrapper();
            $records = $this->getProfiles();

            if (empty($records)) {
                return;
            }

            $bodyWrapper->setData($records);
            $headerInstance = new HeaderMap();
            $response = $recordOperations->updateRecords($bodyWrapper, $headerInstance);

            $this->processUpdateResult(\Iidev\ZohoCRM\Model\ZohoProfile::class, $response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('UpdateProfilesCommand Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
        }
    }

    protected function getProfiles()
    {
        $records = [];
        $profiles = Database::getRepo(Profile::class)->findByIds($this->entityIds);

        foreach ($profiles as $profile) {
            $records[] = $this->getProfile($profile);
            $this->entities[] = $profile;
        }

        return $records;
    }

    protected function getProfile(Profile $profile)
    {
        $record = new Record();

        $record->addFieldValue(Contacts::id(), $profile->getZohoModel()->getZohoId());
        $record->addFieldValue(Contacts::Email(), $profile->getLogin());

        $membership = $profile->getMembershipId() ? "Professional (Pro)" : "-None-";

        $record->addFieldValue(new Field('membership'), new Choice($membership));

        return $record;
    }
}
