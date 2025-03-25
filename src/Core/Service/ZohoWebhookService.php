<?php

namespace Iidev\ZohoCRM\Core\Service;

use Iidev\ZohoCRM\Model\ZohoProfile;
use Iidev\ZohoCRM\Model\ZohoOrder;
use XLite\InjectLoggerTrait;
use XLite\Core\Database;
use XLite\Core\OrderHistory;
use Qualiteam\SkinActVerifiedCustomer\Model\VerificationInfo;

class ZohoWebhookService
{
    use InjectLoggerTrait;

    public function processZohoRequest(array $data)
    {
        if (isset($data['event_type'])) {
            switch ($data['event_type']) {
                case 'order_status_change':
                    $this->handleOrderStatusChange($data);
                    break;
                case 'profile_change':
                    $this->handleProfileChange($data);
                    break;
                default:
                    $this->getLogger('ZohoCRM')->error('Missing event_type in payload', []);
            }
        } else {
            $this->getLogger('ZohoCRM')->error('Missing event_type in payload', []);
        }
    }

    private function handleOrderStatusChange(array $data)
    {
        $zohoModel = Database::getRepo(ZohoOrder::class)->findOneBy([
            "zoho_id" => $data['zohoId']
        ]);

        if (!$zohoModel) {
            $this->getLogger('ZohoCRM')->error("Order {$data['zohoId']} not found", []);
            return;
        }

        $paymentStatus = $this->getPaymentStatus($data['paymentStatus']);
        $shippingStatus = $this->getShippingStatus($data['status']);

        if (!$paymentStatus || !$shippingStatus) {
            $this->getLogger('ZohoCRM')->error("Order status not found", [
                $data['paymentStatus'],
                $data['status']
            ]);
            return;
        }

        $order = $zohoModel->getId();
        $order->setPaymentStatus($paymentStatus);
        $order->setShippingStatus($shippingStatus);

        OrderHistory::getInstance()->registerEvent($order->getOrderId(), "zoho", "Zoho order status changed");

        $this->setOrderSynced($order);

        Database::getEM()->persist($order);
        Database::getEM()->flush();
    }

    private function handleProfileChange(array $data)
    {
        $zohoModel = Database::getRepo(ZohoProfile::class)->findOneBy([
            "zoho_id" => $data['zohoId']
        ]);

        if (!$zohoModel) {
            $this->getLogger('ZohoCRM')->error("Profile {$data['zohoId']} not found", []);
            return;
        }

        $verified = $data['verified'] && $data['verified'] === 'true' ? VerificationInfo::STATUS_VERIFIED : VerificationInfo::STATUS_NOT_VERIFIED;

        if ($verified === null) {
            $this->getLogger('ZohoCRM')->error("Invalid or missing 'verified' value in payload", $data);
            return;
        }
        $this->getLogger('ZohoCRM')->error($data['verified'], [$verified]);

        $profile = $zohoModel->getId();
        if ($profile->getVerificationInfo()) {
            $profile->getVerificationInfo()
                ->setStatus($verified);
        }

        $zohoModel->setSynced(true);

        Database::getEM()->persist($profile);
        Database::getEM()->flush();
    }

    private function getPaymentStatus($status)
    {
        return Database::getRepo(\XLite\Model\Order\Status\Payment::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.translations', 't')
            ->where('t.name = :name')
            ->setParameter('name', $status)
            ->getSingleResult();
    }

    private function getShippingStatus($status)
    {
        return Database::getRepo(\XLite\Model\Order\Status\Shipping::class)
            ->createQueryBuilder('p')
            ->leftJoin('p.translations', 't')
            ->where('t.name = :name')
            ->setParameter('name', $status)
            ->getSingleResult();
    }

    private function setOrderSynced($order)
    {
        $order->getZohoModel()?->setSynced(true);
        $order->getZohoQuote()?->setSynced(true);
    }
}