<?php

namespace Iidev\ZohoCRM\Core\Dispatcher;

use Iidev\ZohoCRM\Core\Factory\Commands\PushOrdersCommandFactory;
use Iidev\ZohoCRM\Core\Factory\Commands\PushProfilesCommandFactory;
use Iidev\ZohoCRM\Core\Factory\Commands\PushProductsCommandFactory;
use Iidev\ZohoCRM\Core\Factory\Commands\PushProductVariantsCommandFactory;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use XCart\Container;
use XLite\Core\Database;
use XLite\Model\Order;

class CreateOrdersDispatcher
{
    protected ExportMessage $message;

    private array $orders = [];

    public function __construct()
    {
        $entityIds = Database::getRepo(Order::class)->findOrderIdsToCreateInZoho();

        $this->orders = Database::getRepo(Order::class)->findByIds($entityIds);
        $this->createProfilesAndProductsBeforePushOrders();

        /** @var PushOrdersCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get('Iidev\ZohoCRM\Core\Factory\Commands\PushOrdersCommandFactory') : null;
        $command = $commandFactory->createCommand($entityIds);
        $this->message = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }

    protected function createProfilesAndProductsBeforePushOrders()
    {
        $profileIds = [];
        $productIds = [];
        $productVariantIds = [];
        foreach ($this->orders as $order) {
            $profile = $order->getOrigProfile();
            if ($profile && !$profile->getZohoId()) {
                $profileIds[] = $profile->getProfileId();
            }

            foreach ($order->getItems() as $orderItem) {
                if ($orderItem->getVariant() && !$orderItem->getVariant()->getZohoId()) {
                    $productVariantIds[] = $orderItem->getVariant()->getId();
                }
                if ($orderItem->getProduct() && !$orderItem->getProduct()->getZohoId()) {
                    $productIds[] = $orderItem->getProduct()->getProductId();
                }
            }
        }

        $profileIds = array_unique($profileIds);
        $productIds = array_unique($productIds);
        $productVariantIds = array_unique($productVariantIds);

        if (!empty($profileIds)) {
            /** @var PushProfilesCommandFactory $profilesFactory */
            $profilesFactory = Container::getContainer()->get('Iidev\ZohoCRM\Core\Factory\Commands\PushProfilesCommandFactory');
            $profilesCommand = $profilesFactory->createCommand($profileIds);
            $profilesCommand->execute();
        }

        if (!empty($productIds)) {
            /** @var PushProductsCommandFactory $productsFactory */
            $productsFactory = Container::getContainer()->get('Iidev\ZohoCRM\Core\Factory\Commands\PushProductsCommandFactory');
            $productsCommand = $productsFactory->createCommand($productIds);
            $productsCommand->execute();
        }

        if (!empty($productVariantIds)) {
            /** @var PushProductVariantsCommandFactory $productsFactory */
            $productsFactory = Container::getContainer()->get('Iidev\ZohoCRM\Core\Factory\Commands\PushProductVariantsCommandFactory');
            $productsCommand = $productsFactory->createCommand($productVariantIds);
            $productsCommand->execute();
        }
    }
}
