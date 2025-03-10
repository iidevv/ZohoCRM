<?php

namespace Iidev\ZohoCRM\Messenger\Handler;

use Iidev\ZohoCRM\Core\Command\CommandException;
use Iidev\ZohoCRM\Messenger\Message\ExportMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use XLite\InjectLoggerTrait;

class ExportHandler implements MessageHandlerInterface
{
    use InjectLoggerTrait;

    /**
     * @param ExportMessage $message
     * @return void
     */
    public function __invoke(ExportMessage $message): void
    {
        try {
            $message->getCommand()->execute();

            $this->getLogger()->debug(
                'Command complete',
                ['command' => $message->getCommand()]
            );
        } catch (CommandException $e) {
            $this->getLogger()->error(
                $e->getMessage(),
                ['exception' => $e]
            );
        }
    }
}
