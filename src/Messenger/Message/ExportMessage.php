<?php

namespace Iidev\ZohoCRM\Messenger\Message;

use Iidev\ZohoCRM\Core\Command\ICommand;

class ExportMessage
{
    protected ICommand $command;

    public function __construct(ICommand $command)
    {
        $this->command = $command;
    }

    /**
     * @return ICommand
     */
    public function getCommand(): ICommand
    {
        return $this->command;
    }
}
