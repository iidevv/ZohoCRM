<?php

namespace Iidev\ZohoCRM\Controller\Admin;

use XLite\Controller\Admin\AAdmin;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use XCart\Container;

class Zoho extends AAdmin
{
    protected ?MessageBusInterface $bus;
    protected ExportMessage        $message;

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->bus = Container::getContainer() ? Container::getContainer()->get('messenger.default_bus') : null;
    }

    public function getTitle()
    {
        return '"Zoho CRM Integration" Addon Settings';
    }
}
