<?php

namespace Iidev\ZohoCRM\Core\Command\Push\Deals;

use Exception;
use Iidev\ZohoCRM\Core\Command\Command;
use XLite;
use XLite\Model\Order;
use XLite\Core\Config;
use com\zoho\crm\api\HeaderMap;
use com\zoho\crm\api\record\RecordOperations;
use com\zoho\crm\api\record\BodyWrapper;
use com\zoho\crm\api\record\Deals;
use com\zoho\crm\api\record\Record;
use com\zoho\crm\api\users\MinifiedUser;
use com\zoho\crm\api\record\Field;
use com\zoho\crm\api\util\Choice;

class PushDealsCommand extends Command
{
    public function __construct(
        array $entityIds
    ) {
        $this->entityIds = $entityIds;
        parent::__construct();
    }

    public function execute(): void
    {
        if (empty($this->entityIds)) {
            return;
        }

        try {
            $recordOperations = new RecordOperations('Deals');
            $bodyWrapper = new BodyWrapper();
            $records = $this->getCarts();

            if (empty($records)) {
                return;
            }

            $bodyWrapper->setData($records);
            $headerInstance = new HeaderMap();
            $response = $recordOperations->createRecords($bodyWrapper, $headerInstance);

            $this->processCreateResult(\Iidev\ZohoCRM\Model\ZohoDeal::class, $response);
        } catch (Exception $e) {
            $this->getLogger('ZohoCRM')->error('PushDealsCommand Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
        }
    }

    protected function getCarts()
    {
        $records = [];
        $orders = XLite\Core\Database::getRepo(\XLite\Model\Cart::class)->findByIds($this->entityIds);

        foreach ($orders as $order) {
            $records[] = $this->getCart($order);
            $this->entities[] = $order;
        }

        return $records;
    }

    protected function getCart(Order $order)
    {
        $record = new Record();

        $date = new \DateTime('@' . $order->getDate());
        $record->addFieldValue(Deals::ClosingDate(), $date);

        $lastVisitDate = new \DateTime('@' . $order->getLastVisitDate());
        $record->addFieldValue(new Field('lastVisitDate'), $lastVisitDate);
        $record->addFieldValue(new Field('profileUrl'), $this->getProfileUrl($order->getOrigProfile()));

        $record->addFieldValue(Deals::Stage(), new Choice('Qualification'));

        $name = $order->getProfile()?->getLogin() ?? "#{$order->getOrderId()}";

        $record->addFieldValue(Deals::DealName(), $name);
        $record->addFieldValue(new Field('entityId'), (string) $order->getOrderId());

        $record->addFieldValue(Deals::Amount(), (double) abs($order->getTotal()));

        $record->addFieldValue(Deals::Description(), $this->getItemsDescription($order->getItems()));

        $profileId = $order->getOrigProfile()?->getZohoModel()?->getZohoId();

        if ($profileId) {
            $profile = new Record();
            $profile->setId($profileId);
            $record->addFieldValue(Deals::ContactName(), $profile);
        }

        $owner = new MinifiedUser();
        $owner->setId(Config::getInstance()->Iidev->ZohoCRM->owner_id);

        $record->addFieldValue(Deals::Owner(), $owner);

        return $record;
    }

    protected function getItemsDescription($items)
    {
        if (empty($items)) {
            return 'Empty abandoned cart';
        }

        $description = "Items:\n\n";

        foreach ($items as $index => $item) {
            $lines = [];

            $itemSku = $item->getSku();
            $itemName = $item->getName();

            $quantity = $item->getAmount();

            $deletedText = !$item->getProduct()->isPersistent()
                ? ' (deleted)'
                : '';

            $itemNumber = $index + 1;
            $lines[] = "{$itemNumber}. {$itemName} ({$itemSku}) x{$quantity}{$deletedText}";

            if ($item->hasAttributeValues()) {
                foreach ($item->getAttributeValues() as $av) {
                    $lines[] = "  {$av->getName()}: {$av->getValue()}";
                }
            }

            $lines[] = "\n";

            $description .= implode("\n", $lines) . "\n";
        }

        return rtrim($description, "\n");
    }

    protected function getProfileUrl($profile)
    {
        $profileId = $profile?->getProfileId();

        if (!$profileId) {
            return '';
        }

        return \XLite\Core\Converter::buildFullURL(
            'profile',
            '',
            ['profile_id' => $profileId],
            \XLite::getAdminScript()
        );
    }
}
