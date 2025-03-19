<?php

namespace Iidev\ZohoCRM\Core\Command\Push;

use Exception;
use Iidev\ZohoCRM\Core\Command\Command;
use XLite\Core\Database;
use XLite\Model\Profile;
use XLite\Core\Config;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\Contacts;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\users\MinifiedUser;
use com\zoho\crm\api\record\Field;
use com\zoho\crm\api\util\Choice;


class PushProfilesCommand extends Command
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
            $response = $recordOperations->createRecords($bodyWrapper, $headerInstance);

            $this->processCreateResult(\Iidev\ZohoCRM\Model\ZohoProfile::class, $response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('PushProfilesCommand Error:', [
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

        $record->addFieldValue(Contacts::Email(), $profile->getLogin());

        $membership = $profile->getMembershipId() ? "Professional (Pro)" : "-None-";

        $record->addFieldValue(new Field('membership'), new Choice($membership));

        if (!empty($profile->getBillingAddress())) {
            $record->addFieldValue(Contacts::FirstName(), $profile->getBillingAddress()->getFirstname());
            $record->addFieldValue(Contacts::LastName(), $profile->getBillingAddress()->getLastname());
            $record->addFieldValue(Contacts::Phone(), $profile->getBillingAddress()->getPhone());
        }

        if (!empty($profile->getShippingAddress())) {
            $record->addFieldValue(Contacts::MailingCity(), $profile->getShippingAddress()->getCity());
            $record->addFieldValue(Contacts::MailingCountry(), $profile->getShippingAddress()->getCountryName());
            $record->addFieldValue(Contacts::MailingState(), $profile->getShippingAddress()->getStateName());
            $record->addFieldValue(Contacts::MailingStreet(), $profile->getShippingAddress()->getStreet());
            $record->addFieldValue(Contacts::MailingZip(), $profile->getShippingAddress()->getCity());
        }

        $owner = new MinifiedUser();
        $owner->setId(Config::getInstance()->Iidev->ZohoCRM->owner_id);

        $record->addFieldValue(Contacts::Owner(), $owner);

        return $record;
    }
}
