<?php

namespace Iidev\ZohoCRM\Core\Dispatcher\Products;

use Iidev\ZohoCRM\Core\Factory\Commands\Products\PushProductVariantsCommandFactory;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use XCart\Container;
use XLite\Core\Database;
use \XC\ProductVariants\Model\ProductVariant;

class CreateProductVariantsDispatcher
{
    protected ExportMessage $message;
    
    public function __construct()
    {
        $entityIds = Database::getRepo(ProductVariant::class)->findVariantIdsToCreateInZoho();

        /** @var PushProductVariantsCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get(PushProductVariantsCommandFactory::class) : null;
        $command        = $commandFactory->createCommand($entityIds);
        $this->message  = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }
}
