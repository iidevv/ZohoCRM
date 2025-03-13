<?php

namespace Iidev\ZohoCRM\Controller\Admin;

use XLite\Controller\Admin\AAdmin;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use XCart\Container;
use XLite\Core\Database;
use XLite\Core\Request;

class Zoho extends AAdmin
{
    const OPTIONS = [];

    protected ?MessageBusInterface $bus;
    protected ExportMessage $message;

    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->bus = Container::getContainer() ? Container::getContainer()->get('messenger.default_bus') : null;
    }

    public function getTitle()
    {
        return '"Zoho CRM Integration" Addon Settings';
    }

    public function getOptions()
    {
        $options = Database::getRepo('XLite\Model\Config')->findByCategoryAndVisible('Iidev\ZohoCRM');

        return array_filter($options, function ($option) {
            return in_array($option->getName(), static::OPTIONS);
        });
    }

    /**
     * Update model
     */
    public function doActionUpdate()
    {
        $data = array_filter(Request::getInstance()->getData(), function ($key) {
            return in_array($key, static::OPTIONS);
        }, ARRAY_FILTER_USE_KEY);

        foreach ($data as $k => $v) {
            Database::getRepo('XLite\Model\Config')->createOption(
                [
                    'category' => 'Iidev\ZohoCRM',
                    'name' => $k,
                    'value' => $v,
                ]
            );
        }
    }
}
