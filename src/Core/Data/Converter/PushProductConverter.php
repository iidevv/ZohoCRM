<?php

namespace Iidev\ZohoCRM\Core\Data\Converter;

use Iidev\ZohoCRM\Core\Command\Push\Hydrator\BaseConverter;
use Iidev\ZohoCRM\Main;
use XCart\Domain\ModuleManagerDomain;
use XLite\Core\Config;
use XLite\Model\AEntity;
use XLite\Model\Base\Image;
use XLite\Model\Product;

class PushProductConverter extends BaseConverter
{
    /**
     * @param Product $entity
     * @return AEntity[]
     */
    public function convert(AEntity $entity): array
    {
        $shortDescription = Main::getFormattedDescription($entity->getBriefDescription());
        $description = Main::getFormattedDescription($entity->getDescription());

        return [
            'Sku'              => $entity->getSku(),
            'Description'      => $entity->getName(),
            'ShortDescription' => mb_strimwidth($shortDescription, 0, 997, '...'),
            'LongDescription'  => $description,
            'Classification'   => Config::getInstance()->Qualiteam->SkinActSkuVault->skuvault_classification,
            'Supplier'         => Config::getInstance()->Qualiteam->SkinActSkuVault->skuvault_supplier,
            'Brand'            => Config::getInstance()->Qualiteam->SkinActSkuVault->skuvault_brand,
            //'Cost'             => $entity->getPrice(),
            'SalePrice'        => $entity->getAbsoluteSalePriceValue(),
            'RetailPrice'      => $entity->getPrice(),
            'Weight'           => $entity->getWeight(),
            'WeightUnit'       => Config::getInstance()->Units->weight_unit,
            'Pictures'         => array_map(function (Image $image) {
                return $image->getURL();
            }, $entity->getImages()->toArray()),
        ];
    }
}
