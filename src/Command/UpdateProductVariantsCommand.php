<?php

namespace Iidev\ZohoCRM\Command;


use Iidev\ZohoCRM\Core\Dispatcher\UpdateProductVariantsDispatcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class UpdateProductVariantsCommand extends Command
{
    use LockAwareTrait;

    protected static $defaultName = 'ZohoCRM:UpdateProductVariants';

    protected MessageBusInterface    $bus;
    protected UpdateProductVariantsDispatcher $dispatcher;

    public function __construct(MessageBusInterface $bus, UpdateProductVariantsDispatcher $dispatcher)
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

            $message = $this->dispatcher->getMessage();
            $this->bus->dispatch($message);

            $output->writeln('Done: ' . \XLite\Core\Converter::formatTime());
            $output->writeln('----------------------');

            $this->releaseLock();

        } else {
            $output->writeln('Already running: ' . \XLite\Core\Converter::formatTime());
        }

        return Command::SUCCESS;
    }
}
