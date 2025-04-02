<?php

namespace Iidev\ZohoCRM\Controller\Admin;

use Iidev\ZohoCRM\Core\Dispatcher\Profiles\CreateProfilesDispatcher;
use \XLite\Core\TopMessage;
use Iidev\ZohoCRM\Command\LockAwareTrait;

class ZohoProfiles extends Zoho
{
    use LockAwareTrait;
    protected static $defaultName = 'ZohoCRM:CreateProfiles';

    protected function doActionCreateZohoProfile()
    {
        $id = \XLite\Core\Request::getInstance()->id;

        if (!$this->isRunning()) {
            $this->setRunning();

            $dispatcher = new CreateProfilesDispatcher([$id]);
            $message = $dispatcher->getMessage();
            $this->bus->dispatch($message);

            $this->getResult($id);

            $this->releaseLock();
        } else {
            TopMessage::addWarning("Please try again later.");
        }

        $this->setReturnURL($this->getProfileUrl($id));
    }

    protected function getResult($id)
    {
        $profile = \XLite\Core\Database::getRepo(\Iidev\ZohoCRM\Model\ZohoProfile::class)->find($id);

        if ($profile->getZohoId()) {
            TopMessage::addInfo("Profile was successfully added");
        } else {
            TopMessage::addError("Something went wrong, check Zoho profile errors");
        }
    }

    protected function getProfileUrl($id)
    {
        return \XLite\Core\Converter::buildFullURL(
            'profile',
            '',
            ['profile_id' => $id],
            \XLite::getAdminScript()
        );
    }
}
