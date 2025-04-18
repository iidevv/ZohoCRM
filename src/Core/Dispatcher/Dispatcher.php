<?php

namespace Iidev\ZohoCRM\Core\Dispatcher;

use Iidev\ZohoCRM\Core\Factory\Commands\Profiles\PushProfilesCommandFactory;
use Iidev\ZohoCRM\Core\Factory\Commands\Products\PushProductsCommandFactory;
use Iidev\ZohoCRM\Core\Factory\Commands\Products\PushProductVariantsCommandFactory;
use XCart\Container;

class Dispatcher
{
    protected array $orders = [];

    public function __construct()
    {
    }

    protected function createProfilesAndProducts()
    {
        $profileIds = [];
        $productIds = [];
        $productVariantIds = [];
        foreach ($this->orders as $order) {
            $profile = $order->getOrigProfile();
            if ($profile && !$profile->getZohoModel()?->getZohoId()) {
                $profileIds[] = $profile->getProfileId();
            }

            foreach ($order->getItems() as $orderItem) {
                if ($orderItem->getVariant() && !$orderItem->getVariant()->getZohoModel()?->getZohoId()) {
                    $productVariantIds[] = $orderItem->getVariant()->getId();
                    continue;
                }
                if ($orderItem->getProduct()->getProductId() && !$orderItem->getProduct()->getZohoModel()?->getZohoId()) {
                    $productIds[] = $orderItem->getProduct()->getProductId();
                    continue;
                }

                $main = new \Iidev\ZohoCRM\Main();
                $product = $main->getDeletedProductPlaceholder();

                if ($product && !$product->getZohoModel()?->getZohoId()) {
                    $productIds[] = $product->getProductId();
                }
            }
        }

        $profileIds = array_unique($profileIds);
        $productIds = array_unique($productIds);
        $productVariantIds = array_unique($productVariantIds);

        if (!empty($profileIds)) {
            /** @var PushProfilesCommandFactory $profilesFactory */
            $profilesFactory = Container::getContainer()->get(PushProfilesCommandFactory::class);
            $profilesCommand = $profilesFactory->createCommand($profileIds);
            $profilesCommand->execute();
        }

        if (!empty($productIds)) {
            /** @var PushProductsCommandFactory $productsFactory */
            $productsFactory = Container::getContainer()->get(PushProductsCommandFactory::class);
            $productsCommand = $productsFactory->createCommand($productIds);
            $productsCommand->execute();
        }

        if (!empty($productVariantIds)) {
            /** @var PushProductVariantsCommandFactory $productsFactory */
            $productsFactory = Container::getContainer()->get(PushProductVariantsCommandFactory::class);
            $productsCommand = $productsFactory->createCommand($productVariantIds);
            $productsCommand->execute();
        }
    }
}
