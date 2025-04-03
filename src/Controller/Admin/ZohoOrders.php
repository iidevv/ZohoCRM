<?php

namespace Iidev\ZohoCRM\Controller\Admin;

use Iidev\ZohoCRM\Core\Dispatcher\Orders\CreateOrdersDispatcher;
use \XLite\Core\TopMessage;
use Iidev\ZohoCRM\Command\LockAwareTrait;

class ZohoOrders extends Zoho
{
    use LockAwareTrait;
    protected static $defaultName = 'ZohoCRM:CreateOrders';

    protected function doActionCreateZohoOrder()
    {
        $id = \XLite\Core\Request::getInstance()->id;

        if (!$this->isRunning()) {
            $this->setRunning();

            $dispatcher = new CreateOrdersDispatcher([$id]);
            $message = $dispatcher->getMessage();
            $this->bus->dispatch($message);

            $this->getResult($id);

            $this->releaseLock();
        } else {
            TopMessage::addWarning("Please try again later.");
        }

        $this->setReturnURL($this->getOrderUrl($id));
    }

    protected function getResult($id)
    {
        $order = \XLite\Core\Database::getRepo(\Iidev\ZohoCRM\Model\ZohoOrder::class)->find($id);

        if ($order?->getZohoId()) {
            TopMessage::addInfo("Order was successfully added");
        } else {
            TopMessage::addError("Something went wrong, check Zoho order errors");
        }
    }

    protected function getOrderUrl($id)
    {
        return \XLite\Core\Converter::buildFullURL(
            'order',
            '',
            ['order_id' => $id],
            \XLite::getAdminScript()
        );
    }
}
