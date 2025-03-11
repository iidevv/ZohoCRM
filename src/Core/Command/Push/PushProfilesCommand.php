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
// use com\zoho\crm\api\util\Choice;
use com\zoho\crm\api\record\ActionWrapper;
use com\zoho\crm\api\record\SuccessResponse;
use com\zoho\crm\api\record\APIException;
use Iidev\ZohoCRM\Core\Data\Converter\Main;
use XLite\InjectLoggerTrait;

class PushProfilesCommand extends Command
{
    use InjectLoggerTrait;

    private array $profileIds;
    private array $entities = [];

    public function __construct(
        array $profileIds
    ) {
        parent::__construct();
        $this->profileIds = $profileIds;
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

            $this->processResult($response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('PushProfilesCommand Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    protected function processResult($response)
    {
        if ($response != null) {
            $actionHandler = $response->getObject();
            if ($actionHandler instanceof ActionWrapper) {
                $actionResponses = $actionHandler->getData();

                $index = 0;
                foreach ($actionResponses as $actionResponse) {

                    $profile = $this->entities[$index];

                    if ($actionResponse instanceof SuccessResponse) {
                        $details = $actionResponse->getDetails();
                        if (isset($details['id'])) {
                            $zohoId = $details['id'];

                            $profile->setZohoId($zohoId);
                        }
                    } else if ($actionResponse instanceof APIException) {
                        $profile = $this->entities[$index];
                        $this->getLogger('ZohoCRM')->error('APIException:', [
                            $profile->getLogin(),
                            $actionResponse->getDetails(),
                        ]);
                    }
                    $index++;
                }

                Database::getEM()->flush();
            }
        }
    }

    protected function getProfiles()
    {
        $records = [];
        $profiles = Database::getRepo(Profile::class)->findByIds($this->profileIds);

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
