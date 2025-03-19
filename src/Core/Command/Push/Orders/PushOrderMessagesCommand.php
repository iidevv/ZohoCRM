<?php

namespace Iidev\ZohoCRM\Core\Command\Push\Orders;

use Exception;
use Iidev\ZohoCRM\Core\Command\Command;
use XLite\Core\Database;
use \XC\VendorMessages\Model\Message;
use XLite\Core\Config;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\users\MinifiedUser;
use com\zoho\crm\api\record\Field;

class PushOrderMessagesCommand extends Command
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
            $recordOperations = new RecordOperations('Order_Messages');
            $bodyWrapper = new BodyWrapper();
            $records = $this->getOrderMessages();

            if (empty($records)) {
                return;
            }

            $bodyWrapper->setData($records);
            $headerInstance = new HeaderMap();
            $response = $recordOperations->createRecords($bodyWrapper, $headerInstance);

            $this->processCreateResult(\Iidev\ZohoCRM\Model\ZohoOrderMessage::class, $response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('PushOrderMessagesCommand Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
        }
    }

    protected function getOrderMessages()
    {
        $records = [];
        $messages = Database::getRepo(Message::class)->findByIds($this->entityIds);

        foreach ($messages as $message) {
            $records[] = $this->getOrderMessage($message);
            $this->entities[] = $message;
        }

        return $records;
    }

    protected function getOrderMessage(Message $message)
    {
        $record = new Record();

        $name = "";

        if ($message->getConversation()?->getOrder()) {
            $name = (string) $message->getConversation()?->getName();
        } else {
            $name = "Conversation #{$message->getConversation()->getId()}";
        }

        $record->addFieldValue(new Field('Name'), $name);

        $record->addFieldValue(new Field('message'), $message->getBody() ?? '');

        $imageBody = '';

        $images = $message->getImages();
        if ($images) {
            foreach ($images as $image) {
                $url = $image->getURL();
                if ($url) {
                    $imageBody .= "\n" . $url;
                }
            }
        }

        $record->addFieldValue(new Field('images'), $imageBody);

        $profileId = $message->getAuthor()?->getZohoModel()?->getZohoId();

        if ($profileId) {
            $profile = new Record();
            $profile->setId($profileId);
            $record->addFieldValue(new Field('contactName'), $profile);
        }

        $orderId = $message->getConversation()?->getOrder()?->getZohoModel()?->getZohoId();

        if ($orderId) {
            $order = new Record();
            $order->setId($orderId);
            $record->addFieldValue(new Field('orderNumber'), $order);
        }

        $date = new \DateTime('@' . $message->getDate());
        $record->addFieldValue(new Field('date'), $date);

        $owner = new MinifiedUser();
        $owner->setId(Config::getInstance()->Iidev->ZohoCRM->owner_id);

        $record->addFieldValue(new Field('Order Message Owner'), $owner);

        return $record;
    }
}
