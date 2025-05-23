<?php

namespace Iidev\ZohoCRM\Command\Orders;


use Iidev\ZohoCRM\Core\Dispatcher\Orders\UpdateOrdersDispatcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Iidev\ZohoCRM\Command\LockAwareTrait;

class UpdateOrdersCommand extends Command
{
    use LockAwareTrait;

    protected static $defaultName = 'ZohoCRM:UpdateOrders';

    protected MessageBusInterface $bus;
    protected UpdateOrdersDispatcher $dispatcher;

    public function __construct(MessageBusInterface $bus, UpdateOrdersDispatcher $dispatcher)
    {
        parent::__construct();
        $this->bus = $bus;
        $this->dispatcher = $dispatcher;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->isRunning()) {

            $this->setRunning();

            $output->writeln('Started: ' . \XLite\Core\Converter::formatTime());

            if ((int) \XLite\Core\Config::getInstance()->Iidev->ZohoCRM->orders_enable_sync === 1) {
                $message = $this->dispatcher->getMessage();
                $this->bus->dispatch($message);
            } else {
                $output->writeln("Order synchronization disabled");
            }

            $output->writeln('Done: ' . \XLite\Core\Converter::formatTime());
            $output->writeln('----------------------');

            $this->releaseLock();

        } else {
            $output->writeln('Already running: ' . \XLite\Core\Converter::formatTime());
        }

        return Command::SUCCESS;
    }
}
