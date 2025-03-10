<?php

namespace Iidev\ZohoCRM\Core\Command;

/**
 * Interface ICommand
 */
interface ICommand
{
    /**
     * Execute
     *
     * @return void
     * @throws CommandException
     */
    public function execute(): void;
}