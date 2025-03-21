<?php

namespace Iidev\ZohoCRM\Core\Dispatcher\Products;

use Iidev\ZohoCRM\Core\Factory\Commands\Products\UpdateProductVariantsCommandFactory;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use XCart\Container;
use XLite\Core\Database;
use \XC\ProductVariants\Model\ProductVariant;

class UpdateProductVariantsDispatcher
{
    protected ExportMessage $message;
    
    public function __construct()
    {
        $entityIds = Database::getRepo(ProductVariant::class)->findVariantIdsToUpdateInZoho();

        /** @var UpdateProductVariantsCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get(UpdateProductVariantsCommandFactory::class) : null;
        $command        = $commandFactory->createCommand($entityIds);
        $this->message  = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }
}
