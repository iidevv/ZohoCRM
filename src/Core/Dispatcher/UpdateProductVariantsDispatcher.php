<?php

namespace Iidev\ZohoCRM\Core\Dispatcher;

use Iidev\ZohoCRM\Core\Factory\Commands\UpdateProductVariantsCommandFactory;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use XCart\Container;
use XLite\Core\Database;
use \XC\ProductVariants\Model\ProductVariant;

class UpdateProductVariantsDispatcher
{
    protected ExportMessage $message;
    
    public function __construct()
    {
        $entityIds = Database::getRepo(ProductVariant::class)->findVariantIdsToSyncInZoho();

        if (empty($entityIds)) {
            return;
        }

        /** @var UpdateProductVariantsCommandFactory $commandFactory */
        $commandFactory = Container::getContainer() ? Container::getContainer()->get('Iidev\ZohoCRM\Core\Factory\Commands\UpdateProductVariantsCommandFactory') : null;
        $command        = $commandFactory->createCommand($entityIds);
        $this->message  = new ExportMessage($command);
    }

    public function getMessage()
    {
        return $this->message;
    }
}
